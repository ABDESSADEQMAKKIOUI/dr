<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\OpportunityController;

Route::middleware(['auth:sanctum'])->prefix('crm')->group(function () {
    // Leads
    Route::apiResource('leads', LeadController::class);
    Route::post('leads/{lead}/convert', [LeadController::class, 'convertToCustomer']);

    // Opportunities
    Route::apiResource('opportunities', OpportunityController::class);
    Route::post('opportunities/{opportunity}/win', [OpportunityController::class, 'win']);
    Route::post('opportunities/{opportunity}/lose', [OpportunityController::class, 'lose']);
});
