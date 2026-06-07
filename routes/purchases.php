<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Suppliers
    Route::apiResource('suppliers', SupplierController::class);
    Route::get('suppliers/{supplier}/balance', [SupplierController::class, 'balance']);

    // Purchases
    Route::apiResource('purchases', PurchaseController::class);
    Route::post('purchases/{purchase}/confirm', [PurchaseController::class, 'confirm']);
    Route::post('purchases/{purchase}/receive', [PurchaseController::class, 'receive']);
    Route::post('purchases/{purchase}/payments', [PurchaseController::class, 'addPayment']);
});
