<?php

use App\Http\Controllers\Gateway\TriboPayController;
use Illuminate\Support\Facades\Route;

Route::prefix('tribopay')
    ->group(function ()
    {
        Route::post('callback', [TriboPayController::class, 'callbackMethod']);
        Route::post('payment', [TriboPayController::class, 'callbackMethodPayment']);
        Route::get('withdrawal/{id}', [TriboPayController::class, 'withdrawalFromModal'])->name('tribopay.withdrawal');
        Route::get('cancelwithdrawal/{id}', [TriboPayController::class, 'cancelWithdrawalFromModal'])->name('tribopay.cancelwithdrawal');
        Route::post('consult-status-transaction', [TriboPayController::class, 'consultStatusTransactionPix']);
    });
