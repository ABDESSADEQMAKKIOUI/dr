<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\StockController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Warehouses
    Route::apiResource('warehouses', WarehouseController::class);
    Route::get('warehouses/{warehouse}/stock', [WarehouseController::class, 'stock']);

    // Stock Adjustments
    Route::get('stock/adjustments', [StockController::class, 'adjustments']);
    Route::post('stock/adjustments', [StockController::class, 'createAdjustment']);

    // Stock Transfers
    Route::get('stock/transfers', [StockController::class, 'transfers']);
    Route::post('stock/transfers', [StockController::class, 'createTransfer']);
    Route::post('stock/transfers/{transfer}/send', [StockController::class, 'sendTransfer']);
    Route::post('stock/transfers/{transfer}/receive', [StockController::class, 'receiveTransfer']);

    // Stock Alerts & History
    Route::get('stock/alerts', [StockController::class, 'alerts']);
    Route::get('stock/history', [StockController::class, 'history']);
});
