<?php

namespace App\Traits\Gateways;

use App\Helpers\Core as Helper;
use App\Models\AffiliateHistory;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\Setting;
use App\Models\StripePayment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\NewDepositNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\Checkout\Session;
use Stripe\Stripe;

trait StripeTrait
{
    protected static string $stripePublicKey;
    protected static string $stripeSecretKey;
    protected static string $stripeWebhookSecret;
    protected static ?string $stripeConnectedAccountId;

    private static function generateCredentials(): void
    {
        $gateway = Gateway::first();

        if (!empty($gateway)) {
            self::$stripePublicKey = $gateway->getAttributes()['stripe_public_key'] ?? '';
            self::$stripeSecretKey = $gateway->getAttributes()['stripe_secret_key'] ?? '';
            self::$stripeWebhookSecret = $gateway->getAttributes()['stripe_webhook_secret'] ?? '';
            self::$stripeConnectedAccountId = $gateway->getAttributes()['stripe_connected_account_id'] ?? null;
            return;
        }

        self::$stripePublicKey = '';
        self::$stripeSecretKey = '';
        self::$stripeWebhookSecret = '';
        self::$stripeConnectedAccountId = null;
    }

    public static function requestCheckoutSession($request)
    {
        $setting = Helper::getSetting();
        $rules = [
            'amount' => ['required', 'numeric', 'min:' . $setting->min_deposit, 'max:' . $setting->max_deposit],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        self::generateCredentials();

        if (empty(self::$stripeSecretKey)) {
            return [
                'status' => false,
                'message' => 'Stripe secret key not configured.',
            ];
        }

        $user = auth('api')->user();
        $amount = (float) Helper::amountPrepare($request->amount);
        $amountCents = (int) round($amount * 100);

        $currency = strtoupper($request->get('currency', $setting->currency_code ?? 'USD'));
        if (in_array(strtolower($currency), ['brl', 'br'])) {
            $currency = 'BRL';
        } else {
            $currency = 'USD';
        }

        try {
            Stripe::setApiKey(self::$stripeSecretKey);

            $sessionOptions = [
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => strtolower($currency),
                            'product_data' => [
                                'name' => 'Deposit - ' . ($setting->software_name ?? 'Platform'),
                                'description' => 'Account deposit',
                            ],
                            'unit_amount' => $amountCents,
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => url('/stripe/success?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => url('/stripe/cancel'),
                'metadata' => [
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'currency' => $currency,
                    'accept_bonus' => $request->get('accept_bonus', true) ? '1' : '0',
                ],
            ];

            if (!empty(self::$stripeConnectedAccountId)) {
                $sessionOptions['payment_intent_data'] = [
                    'transfer_data' => [
                        'destination' => self::$stripeConnectedAccountId,
                    ],
                ];
            }

            $session = Session::create($sessionOptions);

            $idTransaction = $session->id;

            self::generateTransaction($idTransaction, $amount, $currency);
            self::generateDeposit($idTransaction, $amount);

            StripePayment::create([
                'session_id' => $session->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
            ]);

            return [
                'status' => true,
                'idTransaction' => $idTransaction,
                'url' => $session->url,
                'type' => 'stripe_checkout',
            ];
        } catch (\Exception $e) {
            Log::error('Stripe Checkout Session Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
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

        return response()->json(['status' => 'pending'], 200);
    }

    public static function finalizePayment($sessionId): bool
    {
        $transaction = Transaction::where('payment_id', $sessionId)->where('status', 0)->first();

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
                        $deposit = Deposit::where('payment_id', $sessionId)->where('status', 0)->first();

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
                                $stripePayment = StripePayment::where('session_id', $sessionId)->first();
                                if (!empty($stripePayment)) {
                                    $stripePayment->update(['status' => 'completed']);
                                }

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

    private static function generateTransaction($idTransaction, $amount, $currency = null): void
    {
        $setting = Helper::getSetting();

        Transaction::create([
            'payment_id' => $idTransaction,
            'user_id' => auth('api')->user()->id,
            'payment_method' => 'stripe',
            'price' => $amount,
            'currency' => $currency ?? $setting->currency_code,
            'status' => 0,
        ]);
    }

    private static function generateDeposit($idTransaction, $amount): void
    {
        $userId = auth('api')->user()->id;
        $wallet = Wallet::where('user_id', $userId)->first();

        Deposit::create([
            'payment_id' => $idTransaction,
            'user_id' => $userId,
            'amount' => $amount,
            'type' => 'stripe',
            'currency' => $wallet->currency,
            'symbol' => $wallet->symbol,
            'status' => 0,
        ]);
    }
}