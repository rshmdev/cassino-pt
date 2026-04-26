<?php

namespace App\Http\Controllers\Api\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Setting;
use App\Traits\Gateways\BlackPearlPayTrait;
use App\Traits\Gateways\StripeTrait;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    use BlackPearlPayTrait, StripeTrait;

    public function submitPayment(Request $request)
    {
        $setting = Setting::first();
        $gateway = $request->get('gateway', 'blackpearlpay');

        if ($gateway === 'stripe' && $setting->stripe_is_enable) {
            return self::requestCheckoutSession($request);
        }

        return self::requestQrcode($request);
    }

    public function consultStatusTransactionPix(Request $request)
    {
        $gateway = $request->get('gateway', 'blackpearlpay');

        if ($gateway === 'stripe') {
            return self::consultStatusTransaction($request);
        }

        return self::consultStatusTransaction($request);
    }

    public function index()
    {
        $deposits = Deposit::whereUserId(auth('api')->id())->paginate();
        return response()->json(['deposits' => $deposits], 200);
    }
}