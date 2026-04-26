<?php

use App\Http\Controllers\Gateway\StripeController;
use Illuminate\Support\Facades\Route;

Route::prefix('stripe')
    ->group(function () {
        Route::post('webhook', [StripeController::class, 'webhook']);
        Route::get('success', [StripeController::class, 'success'])->name('stripe.success');
        Route::get('cancel', [StripeController::class, 'cancel'])->name('stripe.cancel');
    });