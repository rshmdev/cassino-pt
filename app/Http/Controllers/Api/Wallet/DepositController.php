<?php

namespace App\Http\Controllers\Api\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Traits\Gateways\DigitoPayTrait;
use App\Traits\Gateways\SharkPayTrait;
use App\Traits\Gateways\SuitpayTrait;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    Use SharkPayTrait;
    Use SuitpayTrait;


    /**
     * @param Request $request
     * @return array|false[]
     */
    public function submitPayment(Request $request)
    {
        switch ($request->gateway) {
            case 'sharkpay':
                return self::requestQrcodeSharkPay($request);
            case 'suitpay':
                return self::requestQrcode($request);
            default:
                return response()->json(['message' => 'Gateway não encontrado'], 400);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function consultStatusTransactionPix(Request $request)
    {
        switch ($request->gateway) {
            case 'sharkpay':
                return self::consultStatusTransactionSharkpay($request->idTransaction);
            case 'suitpay':
                return self::consultStatusTransaction($request);
            default:
                return response()->json(['message' => 'Gateway não encontrado'], 400);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $deposits = Deposit::whereUserId(auth('api')->id())->paginate();
        return response()->json(['deposits' => $deposits], 200);
    }

}
