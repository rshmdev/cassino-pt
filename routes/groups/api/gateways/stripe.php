<?php

use App\Http\Controllers\Gateway\StripeController;
use Illuminate\Support\Facades\Route;

Route::prefix('stripe')
    ->group(function () {
        Route::post('consult-status-transaction', [StripeController::class, 'consultStatusTransactionPix']);
    });