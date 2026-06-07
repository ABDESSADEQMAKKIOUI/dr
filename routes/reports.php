<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::middleware(['auth:sanctum'])->prefix('reports')->group(function () {
    Route::get('sales', [ReportController::class, 'sales']);
    Route::get('purchases', [ReportController::class, 'purchases']);
    Route::get('profit-loss', [ReportController::class, 'profitLoss']);
    Route::get('stock', [ReportController::class, 'stock']);
});
