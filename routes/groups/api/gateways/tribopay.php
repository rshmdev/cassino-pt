<?php

use App\Http\Controllers\Gateway\TriboPayController;
use Illuminate\Support\Facades\Route;

Route::prefix('tribopay')
    ->group(function ()
    {
        Route::post('consult-status-transaction', [TriboPayController::class, 'consultStatusTransactionPix']);
    });
