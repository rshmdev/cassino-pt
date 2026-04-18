<?php

namespace App\Traits\Gateways;

use App\Helpers\Core as Helper;
use App\Models\AffiliateHistory;
use App\Models\BlackPearlPayPayment;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\NewDepositNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

trait BlackPearlPayTrait
{
    protected static string $uri;
    protected static string $apiToken;

    private static function generateCredentials(): void
    {
        $gateway = Gateway::first();

        if (!empty($gateway)) {
            self::$uri = $gateway->getAttributes()['blackpearlpay_uri'] ?? 'https://api.blackpearlpay.com/api/public/cash';
            self::$apiToken = $gateway->getAttributes()['blackpearlpay_api_token'] ?? '';
            return;
        }

        self::$uri = 'https://api.blackpearlpay.com/api/public/cash';
        self::$apiToken = '';
    }

    public static function requestQrcode($request)
    {
        $setting = Helper::getSetting();
        $rules = [
            'amount' => ['required', 'numeric', 'min:' . $setting->min_deposit, 'max:' . $setting->max_deposit],
            'cpf' => ['required', 'max:255'],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        self::generateCredentials();

        if (empty(self::$apiToken)) {
            return [
                'status' => false,
                'message' => 'Token da BlackPearlPay nao configurado.',
            ];
        }

        $user = auth('api')->user();
        $amount = (float) Helper::amountPrepare($request->amount);
        $amountCents = (int) round($amount * 100);
        $doc = preg_replace('/\D/', '', (string) $request->cpf);
        $externalId = uniqid('dep_') . '_' . $user->id . '_' . time();

        $body = [
            'amount' => $amountCents,
            'externalId' => $externalId,
            'postbackUrl' => url('/blackpearlpay/callback'),
            'payer' => [
                'name' => $user->name,
                'email' => $user->email,
                'document' => $doc,
            ],
        ];

        $endpoint = rtrim(self::$uri, '/') . '/deposits/pix';

        $response = Http::withoutVerifying()
            ->withToken(self::$apiToken)
            ->acceptJson()
            ->post($endpoint, $body);

        Log::info('BlackPearlPay Deposit Response', [
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ]);

        if ($response->successful()) {
            $payload = $response->json();
            $idTransaction = $payload['id'] ?? $payload['hash'] ?? null;
            $pixCode = $payload['pix']['code'] ?? null;

            if (!empty($idTransaction) && !empty($pixCode)) {
                self::generateTransaction($idTransaction, $amount);
                self::generateDeposit($idTransaction, $amount);

                return [
                    'status' => true,
                    'idTransaction' => $idTransaction,
                    'qrcode' => $pixCode,
                ];
            }
        }

        return [
            'status' => false,
            'message' => $response->body(),
        ];
    }

    public static function consultStatusTransaction($request)
    {
        if (empty($request->idTransaction)) {
            return response()->json(['status' => 'error', 'message' => 'Missing idTransaction'], 400);
        }

        $transaction = Transaction::where('payment_id', $request->idTransaction)->first();
        if (!empty($transaction) && (int) $transaction->status === 1) {
            return response()->json(['status' => 'PAID']);
        }

        self::generateCredentials();

        if (empty(self::$apiToken)) {
            return response()->json(['status' => 'pending'], 200);
        }

        $endpoint = rtrim(self::$uri, '/') . '/deposits/' . $request->idTransaction;

        $response = Http::withoutVerifying()
            ->withToken(self::$apiToken)
            ->acceptJson()
            ->get($endpoint);

        if ($response->successful()) {
            $payload = $response->json();
            $status = strtolower((string) ($payload['status'] ?? 'pending'));

            if ($status === 'paid' && self::finalizePayment($request->idTransaction)) {
                return response()->json(['status' => 'PAID']);
            }

            return response()->json(['status' => $status], 200);
        }

        return response()->json(['status' => 'pending'], 200);
    }

    public static function finalizePayment($idTransaction): bool
    {
        $transaction = Transaction::where('payment_id', $idTransaction)->where('status', 0)->first();

        if (!empty($transaction)) {
            $user = User::find($transaction->user_id);
            $wallet = Wallet::where('user_id', $transaction->user_id)->first();

            if (!empty($wallet)) {
                $setting = Setting::first();

                $checkTransactions = Transaction::where('user_id', $transaction->user_id)
                    ->where('status', 1)
                    ->count();

                if ($checkTransactions === 0) {
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
                        }
                    }
                }
            }
        }

        return false;
    }

    private static function generateDeposit($idTransaction, $amount): void
    {
        $userId = auth('api')->user()->id;
        $wallet = Wallet::where('user_id', $userId)->first();

        Deposit::create([
            'payment_id' => $idTransaction,
            'user_id' => $userId,
            'amount' => $amount,
            'type' => 'pix',
            'currency' => $wallet->currency,
            'symbol' => $wallet->symbol,
            'status' => 0,
        ]);
    }

    private static function generateTransaction($idTransaction, $amount): void
    {
        $setting = Helper::getSetting();

        Transaction::create([
            'payment_id' => $idTransaction,
            'user_id' => auth('api')->user()->id,
            'payment_method' => 'pix',
            'price' => $amount,
            'currency' => $setting->currency_code,
            'status' => 0,
        ]);
    }

    public static function pixCashOut(array $array): bool
    {
        self::generateCredentials();

        if (empty(self::$apiToken)) {
            return false;
        }

        $amountCents = (int) round(((float) $array['amount']) * 100);

        $payload = [
            'amount' => $amountCents,
            'type' => 'pix',
            'pixKey' => $array['pix_key'],
            'pixType' => strtolower((string) $array['pix_type']),
            'description' => $array['description'] ?? 'Saque via plataforma',
            'callbackUrl' => url('/blackpearlpay/callback/withdrawal'),
            'externalId' => uniqid('wth_') . '_' . time(),
        ];

        $endpoint = rtrim(self::$uri, '/') . '/withdrawals';

        $response = Http::withoutVerifying()
            ->withToken(self::$apiToken)
            ->acceptJson()
            ->post($endpoint, $payload);

        Log::info('BlackPearlPay Withdrawal Response', [
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ]);

        if ($response->successful()) {
            $body = $response->json();
            $paymentId = $body['id'] ?? $body['hash'] ?? null;

            if (!empty($paymentId)) {
                $record = BlackPearlPayPayment::lockForUpdate()->find($array['blackpearlpay_payment_id']);

                if (!empty($record)) {
                    return (bool) $record->update([
                        'status' => 1,
                        'payment_id' => $paymentId,
                    ]);
                }
            }
        }

        return false;
    }
}
