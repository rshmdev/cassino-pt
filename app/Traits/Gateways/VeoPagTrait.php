<?php

namespace App\Traits\Gateways;

use App\Models\AffiliateHistory;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VeoPagPayment;
use App\Models\Wallet;
use App\Notifications\NewDepositNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Core as Helper;

trait VeoPagTrait
{
    /**
     * @var string
     */
    protected static string $veopagClientId;
    protected static string $veopagClientSecret;

    /**
     * Load VeoPag credentials from database
     * @return void
     */
    private static function generateCredentials()
    {
        $setting = Gateway::first();
        if (!empty($setting)) {
            self::$veopagClientId = $setting->getAttributes()['veopag_client_id'] ?? '';
            self::$veopagClientSecret = $setting->getAttributes()['veopag_client_secret'] ?? '';
        }
    }

    /**
     * Authenticate with VeoPag API and get Bearer token
     * Token is cached for 50 minutes (expires in 60min)
     * @return string|null
     */
    private static function authenticate(): ?string
    {
        $cacheKey = 'veopag_bearer_token';

        // Return cached token if available
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = Http::withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://api.veopag.com/api/auth/login', [
            'client_id' => self::$veopagClientId,
            'client_secret' => self::$veopagClientSecret,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $token = $data['token'] ?? null;
            $expiresIn = $data['expires_in'] ?? 3600;

            if ($token) {
                // Cache for 50 min (safety margin from 60 min expiry)
                Cache::put($cacheKey, $token, now()->addSeconds($expiresIn - 600));
                return $token;
            }
        }

        Log::error('VeoPag Auth Failed: ' . $response->body());
        return null;
    }

    /**
     * Request QR Code for deposit
     * @param $request
     * @return array|\Illuminate\Http\JsonResponse
     */
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
        $token = self::authenticate();

        if (!$token) {
            return [
                'status' => false,
                'message' => 'Falha na autenticação com o gateway de pagamento.'
            ];
        }

        $user = auth('api')->user();
        $amount = floatval(\Helper::amountPrepare($request->amount));
        $externalId = uniqid('dep_') . '_' . $user->id . '_' . time();

        $doc = $request->cpf ? preg_replace('/\D/', '', $request->cpf) : '00000000000';

        $body = [
            'amount' => $amount,
            'external_id' => $externalId,
            'clientCallbackUrl' => url('/veopag/callback'),
            'payer' => [
                'name' => $user->name,
                'email' => $user->email,
                'document' => $doc,
            ],
        ];

        Log::info('VeoPag Deposit Payload: ', $body);

        $response = Http::withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->post('https://api.veopag.com/api/payments/deposit', $body);

        Log::info('VeoPag Deposit Response: ' . $response->status() . ' - ' . $response->body());

        if ($response->successful()) {
            $responseData = $response->json();
            $qrData = $responseData['qrCodeResponse'] ?? null;

            if ($qrData) {
                $transactionId = $qrData['transactionId'] ?? $externalId;
                $qrcode = $qrData['qrcode'] ?? null;

                if ($transactionId && $qrcode) {
                    self::generateTransaction($transactionId, $amount);
                    self::generateDeposit($transactionId, $amount);

                    return [
                        'status' => true,
                        'idTransaction' => $transactionId,
                        'qrcode' => $qrcode,
                    ];
                }
            }
        }

        return [
            'status' => false,
            'message' => $response->body(),
        ];
    }

    /**
     * Consult status of a transaction (polling fallback)
     * VeoPag primarily uses webhooks, but we keep this for frontend polling
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public static function consultStatusTransaction($request)
    {
        Log::info('VeoPag Consult Status: ', $request->all());

        if (!$request->idTransaction) {
            Log::error('VeoPag Consult: Missing idTransaction');
            return response()->json(['status' => 'error', 'message' => 'Missing idTransaction'], 400);
        }

        // Check if the transaction has already been finalized in our DB
        $transaction = Transaction::where('payment_id', $request->idTransaction)->first();
        if ($transaction) {
            if ($transaction->status == 1) {
                return response()->json(['status' => 'PAID']);
            }
            return response()->json(['status' => 'pending'], 200);
        }

        return response()->json(['status' => 'pending'], 200);
    }

    /**
     * Finalize payment - credit balance, apply bonuses, notify admins
     * @param string $idTransaction
     * @return bool
     */
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

    /**
     * Create deposit record
     * @param string $idTransaction
     * @param float $amount
     * @return void
     */
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

    /**
     * Create transaction record
     * @param string $idTransaction
     * @param float $amount
     * @return void
     */
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

    /**
     * PIX Cash Out (Withdrawal)
     * @param array $array
     * @return bool
     */
    public static function pixCashOut(array $array): bool
    {
        self::generateCredentials();
        $token = self::authenticate();

        if (!$token) {
            Log::error('VeoPag: Failed to authenticate for withdrawal');
            return false;
        }

        $externalId = uniqid('wd_') . '_' . time();

        $body = [
            'amount' => floatval($array['amount']),
            'external_id' => $externalId,
            'pix_key' => $array['pix_key'],
            'key_type' => strtoupper($array['pix_type'] ?? 'CPF'),
            'name' => $array['name'] ?? 'Usuario',
            'taxId' => preg_replace('/\D/', '', $array['pix_key']),
            'description' => 'Saque via plataforma',
            'clientCallbackUrl' => url('/veopag/callback/withdrawal'),
        ];

        Log::info('VeoPag Withdrawal Payload: ', $body);

        $response = Http::withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->post('https://api.veopag.com/api/withdrawals/withdraw', $body);

        Log::info('VeoPag Withdrawal Response: ' . $response->status() . ' - ' . $response->body());

        if ($response->successful()) {
            $responseData = $response->json();
            $withdrawal = $responseData['withdrawal'] ?? $responseData;

            $paymentId = $withdrawal['transaction_id'] ?? $externalId;

            $veoPagPayment = VeoPagPayment::lockForUpdate()->find($array['veopag_payment_id']);
            if (!empty($veoPagPayment)) {
                if ($veoPagPayment->update(['status' => 1, 'payment_id' => $paymentId])) {
                    return true;
                }
                return false;
            }
            return false;
        }
        return false;
    }
}
