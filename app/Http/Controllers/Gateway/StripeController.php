<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use App\Models\StripePayment;
use App\Traits\Gateways\StripeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeController extends Controller
{
    use StripeTrait;

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->server('HTTP_STRIPE_SIGNATURE');

        self::generateCredentials();

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                self::$stripeWebhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe Webhook: Invalid payload', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Stripe Webhook: Invalid signature', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $sessionId = $session->id;

                if ($session->payment_status === 'paid') {
                    self::finalizePayment($sessionId);
                }
                break;

            default:
                Log::info('Stripe Webhook: Unhandled event type', ['type' => $event->type]);
        }

        return response()->json(['status' => 'success'], 200);
    }

    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');

        if ($sessionId) {
            $transaction = \App\Models\Transaction::where('payment_id', $sessionId)->first();
            if (!empty($transaction) && (int) $transaction->status === 1) {
                return redirect('/profile/deposit?status=paid');
            }

            self::finalizePayment($sessionId);
        }

        return redirect('/profile/deposit?status=pending');
    }

    public function cancel()
    {
        return redirect('/profile/deposit?status=cancelled');
    }

    public function consultStatusTransactionPix(Request $request)
    {
        return self::consultStatusTransaction($request);
    }
}