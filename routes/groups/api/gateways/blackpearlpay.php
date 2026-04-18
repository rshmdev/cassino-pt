<?php

use App\Http\Controllers\Gateway\BlackPearlPayController;
use Illuminate\Support\Facades\Route;

Route::prefix('blackpearlpay')
    ->group(function ()
    {
        Route::post('consult-status-transaction', [BlackPearlPayController::class, 'consultStatusTransactionPix']);
    });
