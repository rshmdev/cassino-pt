<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use App\Models\BlackPearlPayPayment;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Traits\Gateways\BlackPearlPayTrait;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlackPearlPayController extends Controller
{
    use BlackPearlPayTrait;

    public function callbackMethod(Request $request)
    {
        $data = $request->all();
        Log::info('BlackPearlPay Deposit Callback: ', $data);

        if (self::processDepositWebhook($data)) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'received'], 200);
    }

    public function callbackWithdrawal(Request $request)
    {
        $data = $request->all();
        Log::info('BlackPearlPay Withdrawal Callback: ', $data);

        if (self::processWithdrawalWebhook($data)) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'received'], 200);
    }

    public function getQRCodePix(Request $request)
    {
        return self::requestQrcode($request);
    }

    public function consultStatusTransactionPix(Request $request)
    {
        return self::consultStatusTransaction($request);
    }

    public function withdrawalFromModal($id)
    {
        $withdrawal = Withdrawal::find($id);
        if (!empty($withdrawal)) {
            $user = $withdrawal->user;

            $blackPearlPayPayment = BlackPearlPayPayment::create([
                'withdrawal_id' => $withdrawal->id,
                'user_id'       => $withdrawal->user_id,
                'pix_key'       => $withdrawal->pix_key,
                'pix_type'      => $withdrawal->pix_type,
                'amount'        => $withdrawal->amount,
                'observation'   => 'Saque direto',
            ]);

            if ($blackPearlPayPayment) {
                $parm = [
                    'pix_key'          => $withdrawal->pix_key,
                    'pix_type'         => $withdrawal->pix_type,
                    'amount'           => $withdrawal->amount,
                    'name'             => $user->name ?? 'Usuario',
                    'blackpearlpay_payment_id' => $blackPearlPayPayment->id,
                ];

                $resp = self::pixCashOut($parm);

                if ($resp) {
                    $withdrawal->update(['status' => 1]);
                    Notification::make()
                        ->title('Saque solicitado')
                        ->body('Saque solicitado com sucesso')
                        ->success()
                        ->send();

                    return back();
                } else {
                    Notification::make()
                        ->title('Erro no saque')
                        ->body('Erro ao solicitar o saque')
                        ->danger()
                        ->send();

                    return back();
                }
            }
        }
    }

    public function cancelWithdrawalFromModal($id)
    {
        $withdrawal = Withdrawal::find($id);
        if (!empty($withdrawal)) {
            $wallet = Wallet::where('user_id', $withdrawal->user_id)
                ->where('currency', $withdrawal->currency)
                ->first();

            if (!empty($wallet)) {
                $wallet->increment('balance_withdrawal', $withdrawal->amount);

                $withdrawal->update(['status' => 2]);
                Notification::make()
                    ->title('Saque cancelado')
                    ->body('Saque cancelado com sucesso')
                    ->success()
                    ->send();

                return back();
            }
            return back();
        }
        return back();
    }
}
