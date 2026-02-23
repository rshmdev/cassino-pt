<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use App\Models\VeoPagPayment;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Traits\Gateways\VeoPagTrait;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VeoPagController extends Controller
{
    use VeoPagTrait;

    /**
     * Handle deposit webhook from VeoPag
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callbackMethod(Request $request)
    {
        $data = $request->all();
        Log::info('VeoPag Deposit Callback: ', $data);

        $type = $data['type'] ?? '';
        $status = strtoupper($data['status'] ?? '');
        $transactionId = $data['transaction_id'] ?? $data['external_id'] ?? null;

        if ($type === 'Deposit' && $status === 'COMPLETED' && $transactionId) {
            if (self::finalizePayment($transactionId)) {
                return response()->json(['status' => 'success'], 200);
            }
        }

        if ($status === 'FAILED') {
            Log::warning('VeoPag Deposit FAILED', [
                'transaction_id' => $transactionId,
                'error_code' => $data['error_code'] ?? null,
                'error_message' => $data['error_message'] ?? null,
            ]);
        }

        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Handle withdrawal webhook from VeoPag
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callbackWithdrawal(Request $request)
    {
        $data = $request->all();
        Log::info('VeoPag Withdrawal Callback: ', $data);

        $status = strtoupper($data['status'] ?? '');
        $transactionId = $data['transaction_id'] ?? null;

        if ($status === 'FAILED') {
            Log::warning('VeoPag Withdrawal FAILED', [
                'transaction_id' => $transactionId,
                'error_code' => $data['error_code'] ?? null,
                'error_message' => $data['error_message'] ?? null,
            ]);
        }

        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Get PIX QR Code for deposit
     * @param Request $request
     * @return array
     */
    public function getQRCodePix(Request $request)
    {
        return self::requestQrcode($request);
    }

    /**
     * Consult the status of a PIX transaction
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function consultStatusTransactionPix(Request $request)
    {
        return self::consultStatusTransaction($request);
    }

    /**
     * Process withdrawal from admin modal
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function withdrawalFromModal($id)
    {
        $withdrawal = Withdrawal::find($id);
        if (!empty($withdrawal)) {
            $user = $withdrawal->user;

            $veoPagPayment = VeoPagPayment::create([
                'withdrawal_id' => $withdrawal->id,
                'user_id'       => $withdrawal->user_id,
                'pix_key'       => $withdrawal->pix_key,
                'pix_type'      => $withdrawal->pix_type,
                'amount'        => $withdrawal->amount,
                'observation'   => 'Saque direto',
            ]);

            if ($veoPagPayment) {
                $parm = [
                    'pix_key'          => $withdrawal->pix_key,
                    'pix_type'         => $withdrawal->pix_type,
                    'amount'           => $withdrawal->amount,
                    'name'             => $user->name ?? 'Usuario',
                    'veopag_payment_id' => $veoPagPayment->id,
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

    /**
     * Cancel a withdrawal and return funds to user wallet
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
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
