<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentMethodController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Payments
    Route::get('payments', [PaymentController::class, 'index']);
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy']);
    Route::get('payments/stats', [PaymentController::class, 'stats']);

    // Payment Methods
    Route::apiResource('payment-methods', PaymentMethodController::class);
});
