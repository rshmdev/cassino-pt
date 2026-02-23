<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use App\Models\TriboPayPayment;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Traits\Gateways\TriboPayTrait;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TriboPayController extends Controller
{
    use TriboPayTrait;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callbackMethodPayment(Request $request)
    {
        // Handling payout callback
        // \DB::table('debug')->insert(['text' => json_encode($request->all())]);
        return response()->json([], 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function callbackMethod(Request $request)
    {
        $data = $request->all();

        Log::info('TriboPay Callback: ', $data);

        // check for transaction ID (id or hash)
        $idTransaction = $data['hash'] ?? $data['transaction_id'] ?? null;
        
        // check for status (status or payment_status)
        $statusRaw = $data['status'] ?? $data['payment_status'] ?? '';
        $status = strtoupper($statusRaw);

        if($idTransaction && ($status == "PAID" || $status == "APPROVED" || $status == "COMPLETED" || $status == "SUCCEEDED")) {
            if(self::finalizePayment($idTransaction)) {
                return response()->json(['status' => 'success'], 200);
            }
        }
        
        return response()->json(['status' => 'error'], 400);
    }

    /**
     * @param Request $request
     * @return null
     */
    public function getQRCodePix(Request $request)
    {
        return self::requestQrcode($request);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function consultStatusTransactionPix(Request $request)
    {
        return self::consultStatusTransaction($request);
    }

    /**
     * Display the specified resource.
     */
    public function withdrawalFromModal($id)
    {
        $withdrawal = Withdrawal::find($id);
        if(!empty($withdrawal)) {
            $tribopayment = TriboPayPayment::create([
                'withdrawal_id' => $withdrawal->id,
                'user_id'       => $withdrawal->user_id,
                'pix_key'       => $withdrawal->pix_key,
                'pix_type'      => $withdrawal->pix_type,
                'amount'        => $withdrawal->amount,
                'observation'   => 'Saque direto',
            ]);

            if($tribopayment) {
                $parm = [
                    'pix_key'           => $withdrawal->pix_key,
                    'pix_type'          => $withdrawal->pix_type,
                    'amount'            => $withdrawal->amount,
                    'tribopayment_id'    => $tribopayment->id
                ];

                $resp = self::pixCashOut($parm);

                if($resp) {
                    $withdrawal->update(['status' => 1]);
                    Notification::make()
                        ->title('Saque solicitado')
                        ->body('Saque solicitado com sucesso')
                        ->success()
                        ->send();

                    return back();
                }else{
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
     * Cancel Withdrawal
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelWithdrawalFromModal($id)
    {
        $withdrawal = Withdrawal::find($id);
        if(!empty($withdrawal)) {
            $wallet = Wallet::where('user_id', $withdrawal->user_id)
                ->where('currency', $withdrawal->currency)
                ->first();

            if(!empty($wallet)) {
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
