<?php

use App\Http\Controllers\Gateway\BlackPearlPayController;
use Illuminate\Support\Facades\Route;

Route::prefix('blackpearlpay')
    ->group(function () {
        Route::post('callback', [BlackPearlPayController::class, 'callbackMethod']);
        Route::post('callback/withdrawal', [BlackPearlPayController::class, 'callbackWithdrawal']);
        Route::get('withdrawal/{id}', [BlackPearlPayController::class, 'withdrawalFromModal'])->name('blackpearlpay.withdrawal');
        Route::get('cancelwithdrawal/{id}', [BlackPearlPayController::class, 'cancelWithdrawalFromModal'])->name('blackpearlpay.cancelwithdrawal');
    });
