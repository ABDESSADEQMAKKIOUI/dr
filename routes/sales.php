<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\QuotationController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Customers
    Route::apiResource('customers', CustomerController::class);
    Route::get('customers/{customer}/balance', [CustomerController::class, 'balance']);

    // Quotations
    Route::apiResource('quotations', QuotationController::class);
    Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convertToSale']);

    // Sales
    Route::apiResource('sales', SaleController::class);
    Route::post('sales/{sale}/confirm', [SaleController::class, 'confirm']);
    Route::post('sales/{sale}/payments', [SaleController::class, 'addPayment']);
});
