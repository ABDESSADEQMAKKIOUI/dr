<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\EmailTemplateController;

Route::middleware(['auth:sanctum'])->prefix('settings')->group(function () {
    // General Settings
    Route::get('/', [SettingController::class, 'index']);
    Route::put('/', [SettingController::class, 'update']);
    Route::get('/{key}', [SettingController::class, 'get']);
    Route::post('/set', [SettingController::class, 'set']);

    // Taxes
    Route::apiResource('taxes', TaxController::class);

    // Currencies
    Route::apiResource('currencies', CurrencyController::class);

    // Email Templates
    Route::apiResource('email-templates', EmailTemplateController::class);
});
