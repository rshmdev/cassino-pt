<?php

use App\Http\Controllers\Gateway\VeoPagController;
use Illuminate\Support\Facades\Route;

Route::prefix('veopag')
    ->group(function ()
    {
        Route::post('callback', [VeoPagController::class, 'callbackMethod']);
        Route::post('callback/withdrawal', [VeoPagController::class, 'callbackWithdrawal']);
        Route::get('withdrawal/{id}', [VeoPagController::class, 'withdrawalFromModal'])->name('veopag.withdrawal');
        Route::get('cancelwithdrawal/{id}', [VeoPagController::class, 'cancelWithdrawalFromModal'])->name('veopag.cancelwithdrawal');
    });
