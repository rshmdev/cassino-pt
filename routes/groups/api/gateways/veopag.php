<?php

use App\Http\Controllers\Gateway\VeoPagController;
use Illuminate\Support\Facades\Route;

Route::prefix('veopag')
    ->group(function ()
    {
        Route::post('consult-status-transaction', [VeoPagController::class, 'consultStatusTransactionPix']);
    });
