<?php

namespace App\Traits\Gateways;

use App\Models\AffiliateHistory;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\BlackPearlPayPayment;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Notifications\NewDepositNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Core as Helper;

trait BlackPearlPayTrait
{
    protected static string $blackpearlpayApiToken;

    private static function generateCredentials()
    {
        $setting = Gateway::first();
        if (!empty($setting)) {
            self::$blackpearlpayApiToken = $setting->getAttributes()['blackpearlpay_api_token'] ?? '';
        }
    }

    private static function getBaseUrl(): string
    {
        return 'http://api.blackpearlpay.com/api/public/cash';
    }

    private static function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . self::$blackpearlpayApiToken,
        ];
    }

    public static function requestQrcode($request)
    {
        $setting = \Helper::getSetting();
        $rules = [
            'amount' => ['required', 'max:' . $setting->min_deposit, 'max:' . $setting->max_deposit],
            'cpf'    => ['required', 'max:255'],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        self::generateCredentials();

        if (empty(self::$blackpearlpayApiToken)) {
            return [
                'status' => false,
                'message' => 'Gateway não configurado.'
            ];
        }

        $user = auth('api')->user();
        $amount = floatval(\Helper::amountPrepare($request->amount));
        $externalId = uniqid('dep_') . '_' . $user->id . '_' . time();

        $doc = $request->cpf ? preg_replace('/\D/', '', $request->cpf) : '00000000000';

        $body = [
            'amount' => $amount,
            'externalId' => $externalId,
            'payer' => [
                'name' => $user->name,
                'email' => $user->email,
                'document' => $doc,
            ],
        ];

        Log::info('BlackPearlPay Deposit Payload: ', $body);

        $response = Http::withoutVerifying()->withHeaders(self::getHeaders())
            ->post(self::getBaseUrl() . '/deposits/pix', $body);

        Log::info('BlackPearlPay Deposit Response: ' . $response->status() . ' - ' . $response->body());

        if ($response->successful()) {
            $responseData = $response->json();

            if (isset($responseData['id']) && isset($responseData['pix'])) {
                $transactionId = $responseData['id'];
                $qrcode = $responseData['pix']['code'] ?? null;
                $qrImage = $responseData['pix']['imageBase64'] ?? null;

                if ($transactionId) {
                    self::generateTransaction($transactionId, $amount);
                    self::generateDeposit($transactionId, $amount);

                    return [
                        'status' => true,
                        'idTransaction' => $transactionId,
                        'qrcode' => $qrcode,
                        'qrImage' => $qrImage,
                    ];
                }
            }
        }

        return [
            'status' => false,
            'message' => $response->body(),
        ];
    }

    public static function consultStatusTransaction($request)
    {
        Log::info('BlackPearlPay Consult Status: ', $request->all());

        if (!$request->idTransaction) {
            Log::error('BlackPearlPay Consult: Missing idTransaction');
            return response()->json(['status' => 'error', 'message' => 'Missing idTransaction'], 400);
        }

        $transaction = Transaction::where('payment_id', $request->idTransaction)->first();
        if ($transaction) {
            if ($transaction->status == 1) {
                return response()->json(['status' => 'PAID']);
            }
            return response()->json(['status' => 'pending'], 200);
        }

        return response()->json(['status' => 'pending'], 200);
    }

    public static function finalizePayment($idTransaction): bool
    {
        $transaction = Transaction::where('payment_id', $idTransaction)->where('status', 0)->first();
        $setting = \Helper::getSetting();

        if (!empty($transaction)) {
            $user = User::find($transaction->user_id);

            $wallet = Wallet::where('user_id', $transaction->user_id)->first();
            if (!empty($wallet)) {
                $setting = Setting::first();

                $checkTransactions = Transaction::where('user_id', $transaction->user_id)
                    ->where('status', 1)
                    ->count();

                if ($checkTransactions == 0 || empty($checkTransactions)) {
                    $bonus = Helper::porcentagem_xn($setting->initial_bonus, $transaction->price);
                    $wallet->increment('balance_bonus', $bonus);
                    $wallet->update(['balance_bonus_rollover' => $bonus * $setting->rollover]);
                }

                $wallet->update(['balance_deposit_rollover' => $transaction->price * intval($setting->rollover_deposit)]);
                Helper::payBonusVip($wallet, $transaction->price);

                if ($wallet->increment('balance', $transaction->price)) {
                    if ($transaction->update(['status' => 1])) {
                        $deposit = Deposit::where('payment_id', $idTransaction)->where('status', 0)->first();
                        if (!empty($deposit)) {

                            $affHistoryCPA = AffiliateHistory::where('user_id', $user->id)
                                ->where('commission_type', 'cpa')
                                ->where('status', 0)
                                ->first();

                            if (!empty($affHistoryCPA)) {
                                $sponsorCpa = User::find($user->inviter);
                                if (!empty($sponsorCpa)) {
                                    if ($affHistoryCPA->deposited_amount >= $sponsorCpa->affiliate_baseline || $deposit->amount >= $sponsorCpa->affiliate_baseline) {
                                        $walletCpa = Wallet::where('user_id', $affHistoryCPA->inviter)->first();
                                        if (!empty($walletCpa)) {
                                            $walletCpa->increment('refer_rewards', $sponsorCpa->affiliate_cpa);
                                            $affHistoryCPA->update(['status' => 1, 'commission_paid' => $sponsorCpa->affiliate_cpa]);
                                        }
                                    } else {
                                        $affHistoryCPA->update(['deposited_amount' => $transaction->price]);
                                    }
                                }
                            }

                            if ($deposit->update(['status' => 1])) {
                                $admins = User::where('role_id', 0)->get();
                                foreach ($admins as $admin) {
                                    $admin->notify(new NewDepositNotification($user->name, $transaction->price));
                                }
                                return true;
                            }
                            return false;
                        }
                        return false;
                    }
                }
                return false;
            }
            return false;
        }
        return false;
    }

    private static function generateDeposit($idTransaction, $amount)
    {
        $userId = auth('api')->user()->id;
        $wallet = Wallet::where('user_id', $userId)->first();

        Deposit::create([
            'payment_id' => $idTransaction,
            'user_id'    => $userId,
            'amount'     => $amount,
            'type'       => 'pix',
            'currency'   => $wallet->currency,
            'symbol'     => $wallet->symbol,
            'status'     => 0,
        ]);
    }

    private static function generateTransaction($idTransaction, $amount)
    {
        $setting = \Helper::getSetting();

        Transaction::create([
            'payment_id'     => $idTransaction,
            'user_id'        => auth('api')->user()->id,
            'payment_method' => 'pix',
            'price'          => $amount,
            'currency'       => $setting->currency_code,
            'status'         => 0,
        ]);
    }

    public static function pixCashOut(array $array): bool
    {
        self::generateCredentials();

        if (empty(self::$blackpearlpayApiToken)) {
            Log::error('BlackPearlPay: API token not configured');
            return false;
        }

        $externalId = uniqid('wd_') . '_' . time();

        $body = [
            'amount' => floatval($array['amount']),
            'externalId' => $externalId,
            'pixKey' => $array['pix_key'],
            'pixKeyType' => strtoupper($array['pix_type'] ?? 'CPF'),
            'payerName' => $array['name'] ?? 'Usuario',
            'payerDocument' => preg_replace('/\D/', '', $array['pix_key']),
            'description' => 'Saque via plataforma',
        ];

        Log::info('BlackPearlPay Withdrawal Payload: ', $body);

        $response = Http::withoutVerifying()->withHeaders(self::getHeaders())
            ->post(self::getBaseUrl() . '/withdrawals', $body);

        Log::info('BlackPearlPay Withdrawal Response: ' . $response->status() . ' - ' . $response->body());

        if ($response->successful()) {
            $responseData = $response->json();

            $paymentId = $responseData['id'] ?? $externalId;

            $blackPearlPayPayment = BlackPearlPayPayment::lockForUpdate()->find($array['blackpearlpay_payment_id']);
            if (!empty($blackPearlPayPayment)) {
                if ($blackPearlPayPayment->update(['status' => 1, 'payment_id' => $paymentId])) {
                    return true;
                }
                return false;
            }
            return false;
        }
        return false;
    }

    public static function processDepositWebhook($data): bool
    {
        $status = $data['status'] ?? null;
        $transactionId = $data['id'] ?? null;

        if (!$transactionId) {
            Log::warning('BlackPearlPay Webhook: Missing transaction ID');
            return false;
        }

        Log::info('BlackPearlPay Deposit Webhook: ', $data);

        if ($status === 'paid') {
            return self::finalizePayment($transactionId);
        }

        if ($status === 'expired' || $status === 'cancelled') {
            $transaction = Transaction::where('payment_id', $transactionId)->first();
            if ($transaction) {
                $transaction->update(['status' => 2]);
            }
            $deposit = Deposit::where('payment_id', $transactionId)->first();
            if ($deposit) {
                $deposit->update(['status' => 2]);
            }
            return true;
        }

        return false;
    }

    public static function processWithdrawalWebhook($data): bool
    {
        $status = $data['status'] ?? null;
        $transactionId = $data['id'] ?? null;

        Log::info('BlackPearlPay Withdrawal Webhook: ', $data);

        if (!$transactionId) {
            Log::warning('BlackPearlPay Withdrawal Webhook: Missing transaction ID');
            return false;
        }

        $payment = BlackPearlPayPayment::where('payment_id', $transactionId)->first();
        if (!$payment) {
            Log::warning('BlackPearlPay Withdrawal Webhook: Payment not found', ['id' => $transactionId]);
            return false;
        }

        switch ($status) {
            case 'transferred':
            case 'completed':
                $payment->update(['status' => 1]);
                $withdrawal = Withdrawal::find($payment->withdrawal_id);
                if ($withdrawal) {
                    $withdrawal->update(['status' => 1]);
                }
                return true;

            case 'failed':
            case 'refused':
            case 'returned':
            case 'cancelled':
                $payment->update(['status' => 2]);
                $withdrawal = Withdrawal::find($payment->withdrawal_id);
                if ($withdrawal) {
                    $wallet = Wallet::where('user_id', $withdrawal->user_id)
                        ->where('currency', $withdrawal->currency)
                        ->first();
                    if ($wallet) {
                        $wallet->increment('balance_withdrawal', $withdrawal->amount);
                    }
                    $withdrawal->update(['status' => 2]);
                }
                return true;

            case 'processing':
            case 'scheduled':
                $payment->update(['status' => 0]);
                return true;
        }

        return false;
    }
}
