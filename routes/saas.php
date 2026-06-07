<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\SubscriptionController;

// Routes Super Admin (gestion de tous les tenants)
Route::middleware(['auth:sanctum', 'role:super-admin'])->prefix('saas')->group(function () {
    // Tenants
    Route::apiResource('tenants', TenantController::class);
    Route::post('tenants/{tenant}/suspend', [TenantController::class, 'suspend']);
    Route::post('tenants/{tenant}/activate', [TenantController::class, 'activate']);
    Route::get('tenants/stats', [TenantController::class, 'stats']);

    // Subscription Plans
    Route::get('plans', [SubscriptionController::class, 'plans']);
});

// Routes Tenant (auto-scoped par middleware)
Route::middleware(['auth:sanctum', 'tenant.scope'])->prefix('subscription')->group(function () {
    Route::post('subscribe', [SubscriptionController::class, 'subscribe']);
    Route::post('{subscription}/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('{subscription}/upgrade', [SubscriptionController::class, 'upgrade']);
});
