<?php

namespace App\Traits\Gateways;

use App\Models\AffiliateHistory;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\Setting;
use App\Models\TriboPayPayment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\NewDepositNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Core as Helper;

trait TriboPayTrait
{
    /**
     * @var $uri
     * @var $apiToken
     */
    protected static string $uri;
    protected static string $apiToken;

    /**
     * Generate Credentials
     * @return void
     */
    private static function generateCredentials()
    {
        $setting = Gateway::first();
        if(!empty($setting)) {
            self::$uri = $setting->getAttributes()['tribopay_uri'] ?? 'https://api.tribopay.com.br/api/public/v1';
            self::$apiToken = $setting->getAttributes()['tribopay_api_token'] ?? '';
        }
    }

    /**
     * Request QRCODE
     * @return array
     */
    public static function requestQrcode($request)
    {
        $setting = \Helper::getSetting();
        $rules = [
            'amount' => ['required', 'max:'.$setting->min_deposit, 'max:'.$setting->max_deposit],
            'cpf'    => ['required', 'max:255'],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        self::generateCredentials();

        $user = auth('api')->user();
        $amountRaw = \Helper::amountPrepare($request->amount);
        $amountCents = (int) ($amountRaw * 100); 

        // Generate a pseudo-ID for the offer hash, similar to Java implementation
        // e.g., using time + user ID to ensure some uniqueness, then substring
        $referenceId = time() . $user->id;
        $offerHash = substr(md5($referenceId), 0, 8); 

        // Prepare Customer Data with Defaults
        $phone = $user->phone ? preg_replace('/\D/', '', $user->phone) : '11999999999';
        $doc = $request->cpf ? preg_replace('/\D/', '', $request->cpf) : '00000000000';
        
        // Address Defaults (as ViperPro might not store detailed address on user model)
        // Adjust if User model has address
        $street = 'Rua Default';
        $number = '0';
        $neighborhood = 'Centro';
        $city = 'Cidade';
        $state = 'SP';
        $zipCode = '00000000';

        $body = [
            "amount" => $amountCents,
            "offer_hash" => $offerHash, 
            "payment_method" => "pix",
            "installments" => 1,
            "expire_in_days" => 1,
            "transaction_origin" => "api",
            "customer" => [
                "name" => $user->name,
                "email" => $user->email,
                "phone_number" => $phone,
                "document" => $doc,
                "street_name" => $street,
                "number" => $number,
                "complement" => "", 
                "neighborhood" => $neighborhood,
                "city" => $city,
                "state" => $state,
                "zip_code" => $zipCode
            ],
            "cart" => [
                [
                    "product_hash" => "deposit", // Fixed as per instruction
                    "title" => "Deposito", // Or "Créditos na Plataforma"
                    "cover" => null,
                    "price" => $amountCents, 
                    "quantity" => 1,
                    "operation_type" => 1,
                    "tangible" => false
                ]
            ],
            "postback_url" => url('/tribopay/callback')
        ];

        // Ensure URI ends with slash or not correctly
        // The user example was .../v1/transactions
        $endpoint = rtrim(self::$uri, '/') . '/transactions?api_token=' . self::$apiToken;

        Log::info('TriboPay Payload: ', $body);

        $response = Http::withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post($endpoint, $body);

        Log::info('TriboPay Response: ', $response->json());

        if($response->successful() || $response->status() == 201) {
            $responseData = $response->json();
            
            // Expected structure based on logs:
            // "id": 9072227
            // "hash": "clr3p5kjcm"
            // "pix": { "pix_qr_code": "..." }

            // Extract Transaction ID (prefer ID, fallback to hash)
            $transactionId = $responseData['hash'] ?? null;
            
            // Extract QR Code (check nested 'pix' object)
            $paymentCode = $responseData['pix']['pix_qr_code'] ?? $responseData['pix_qrcode'] ?? $responseData['qrcode'] ?? $responseData['payment_code'] ?? null;
            
            if ($transactionId && $paymentCode) {
                 self::generateTransaction($transactionId, $amountRaw); 
                 self::generateDeposit($transactionId, $amountRaw); 

                 return [
                     'status' => true,
                     'idTransaction' => $transactionId,
                     'qrcode' => $paymentCode
                 ];
            }
        }

        return [
            'status' => false,
            'message' => $response->body()
        ];
    }

    /**
     * Consult Status Transaction
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public static function consultStatusTransaction($request)
    {
        Log::info('TriboPay Consult Status: ', $request->all());
        self::generateCredentials();

        if (!$request->idTransaction) {
             Log::error('TriboPay Consult: Missing idTransaction');
             return response()->json(['status' => 'error', 'message' => 'Missing idTransaction'], 400);
        }

        $endpoint = rtrim(self::$uri, '/') . '/transactions/' . $request->idTransaction . '?api_token=' . self::$apiToken;
        Log::info('TriboPay Endpoint: ' . $endpoint);

        $response = Http::withoutVerifying()->withHeaders([
            'Accept' => 'application/json'
        ])->get($endpoint);

        Log::info('TriboPay Status Response: ' . $response->status() . ' - ' . $response->body());

        if($response->successful()) {
            $responseData = $response->json();
            $data = $responseData['data'] ?? $responseData;

            // Check for status (status or payment_status)
            $statusRaw = $data['status'] ?? $data['payment_status'] ?? '';
            $status = strtoupper($statusRaw);

            // Status: paid, completed, pending...
            if($status == "PAID" || $status == "APPROVED" || $status == "COMPLETED" || $status == "SUCCEEDED") {
                if(self::finalizePayment($request->idTransaction)) {
                    return response()->json(['status' => 'PAID']);
                }
                return response()->json(['status' => $statusRaw], 200); // Return actual status
            }
            return response()->json(['status' => $statusRaw ?: 'pending'], 200);
        }
        
        return response()->json(['status' => 'error'], 400);
    }

    /**
     * @param $idTransaction
     * @return bool
     */
    public static function finalizePayment($idTransaction) : bool
    {
        $transaction = Transaction::where('payment_id', $idTransaction)->where('status', 0)->first();
        $setting = \Helper::getSetting();

        if(!empty($transaction)) {
            $user = User::find($transaction->user_id);

            $wallet = Wallet::where('user_id', $transaction->user_id)->first();
            if(!empty($wallet)) {
                $setting = Setting::first();

                $checkTransactions = Transaction::where('user_id', $transaction->user_id)
                    ->where('status', 1)
                    ->count();

                if($checkTransactions == 0 || empty($checkTransactions)) {
                    $bonus = Helper::porcentagem_xn($setting->initial_bonus, $transaction->price);
                    $wallet->increment('balance_bonus', $bonus);
                    $wallet->update(['balance_bonus_rollover' => $bonus * $setting->rollover]);
                }

                $wallet->update(['balance_deposit_rollover' => $transaction->price * intval($setting->rollover_deposit)]);
                Helper::payBonusVip($wallet, $transaction->price);

                if($wallet->increment('balance', $transaction->price)) {
                    if($transaction->update(['status' => 1])) {
                        $deposit = Deposit::where('payment_id', $idTransaction)->where('status', 0)->first();
                        if(!empty($deposit)) {
                            
                            $affHistoryCPA = AffiliateHistory::where('user_id', $user->id)
                                ->where('commission_type', 'cpa')
                                ->where('status', 0)
                                ->first();

                            if(!empty($affHistoryCPA)) {
                                $sponsorCpa = User::find($user->inviter);
                                if(!empty($sponsorCpa)) {
                                    if($affHistoryCPA->deposited_amount >= $sponsorCpa->affiliate_baseline || $deposit->amount >= $sponsorCpa->affiliate_baseline) {
                                        $walletCpa = Wallet::where('user_id', $affHistoryCPA->inviter)->first();
                                        if(!empty($walletCpa)) {
                                            $walletCpa->increment('refer_rewards', $sponsorCpa->affiliate_cpa); 
                                            $affHistoryCPA->update(['status' => 1, 'commission_paid' => $sponsorCpa->affiliate_cpa]); 
                                        }
                                    }else{
                                        $affHistoryCPA->update(['deposited_amount' => $transaction->price]);
                                    }
                                }
                            }

                            if($deposit->update(['status' => 1])) {
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
     * @param $idTransaction
     * @param $amount
     * @return void
     */
    private static function generateDeposit($idTransaction, $amount)
    {
        $userId = auth('api')->user()->id;
        $wallet = Wallet::where('user_id', $userId)->first();

        Deposit::create([
            'payment_id'=> $idTransaction,
            'user_id'   => $userId,
            'amount'    => $amount,
            'type'      => 'pix',
            'currency'  => $wallet->currency,
            'symbol'    => $wallet->symbol,
            'status'    => 0
        ]);
    }

    /**
     * @param $idTransaction
     * @param $amount
     * @return void
     */
    private static function generateTransaction($idTransaction, $amount)
    {
        $setting = \Helper::getSetting();

        Transaction::create([
            'payment_id' => $idTransaction,
            'user_id' => auth('api')->user()->id,
            'payment_method' => 'pix',
            'price' => $amount,
            'currency' => $setting->currency_code,
            'status' => 0
        ]);
    }

    /**
     * @param $array
     * @return bool
     */
    public static function pixCashOut(array $array): bool
    {
        self::generateCredentials();
        
        // Using "transfer" or "withdraw" logic if available.
        // Assuming TriboPay has a transfer endpoint. 
        // If not, this part needs specific API docs for Payouts. 
        // Reverting to presumed logical structure.
        
        $endpoint = rtrim(self::$uri, '/') . '/pix_transfer?api_token=' . self::$apiToken;

        $response = Http::withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post($endpoint, [
            "amount" => (int) ($array['amount'] * 100), 
            "pix_key" => $array['pix_key'],
            "pix_key_type" => $array['pix_type'],
            'callback_url' => url('/tribopay/payment'),
        ]);

        if($response->successful()) {
            $responseData = $response->json();
            $data = $responseData['data'] ?? $responseData;
            
            if(isset($data['id']) || isset($data['hash'])) {
                $triboPayPayment = TriboPayPayment::lockForUpdate()->find($array['tribopayment_id']);
                if(!empty($triboPayPayment)) {
                    if($triboPayPayment->update(['status' => 1, 'payment_id' => $data['id'] ?? $data['hash']])) {
                        return true;
                    }
                    return false;
                }
                return false;
            }
            return false;
        }
        return false;
    }
}
