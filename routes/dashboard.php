<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes - Dashboard
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/sales-chart', [DashboardController::class, 'salesChart']);
    Route::get('/revenue-vs-expenses', [DashboardController::class, 'revenueVsExpenses']);
});
