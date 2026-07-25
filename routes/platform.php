<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Platform\AuthController;
use App\Http\Controllers\Platform\DashboardController;
use App\Http\Controllers\Platform\TenantController;
use App\Http\Controllers\Platform\PlanController;
use App\Http\Controllers\Platform\SubscriptionController;
use App\Http\Controllers\Platform\OperatorController;
use App\Http\Controllers\Platform\AuditLogController;

Route::middleware('guest:platform')->group(function () {
    Route::get('login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth:platform')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/',         [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard', [DashboardController::class, 'index']);

    // NOTE the ordering: the literal `create` route is declared BEFORE
    // tenants/{tenant}, and {tenant} is additionally ->whereNumber()'d, so the
    // wildcard can never swallow it.
    Route::prefix('tenants')->name('tenants.')->group(function () {
        Route::get('create',  [TenantController::class, 'create'])->middleware('platform.ability:tenants.create')->name('create');
        Route::post('/',      [TenantController::class, 'store'])->middleware('platform.ability:tenants.create')->name('store');
        Route::get('/',       [TenantController::class, 'index'])->middleware('platform.ability:tenants.view')->name('index');

        Route::middleware('platform.ability:tenants.view')->group(function () {
            Route::get('{tenant}', [TenantController::class, 'show'])->whereNumber('tenant')->name('show');
        });
        Route::middleware('platform.ability:tenants.update')->group(function () {
            Route::get('{tenant}/edit', [TenantController::class, 'edit'])->whereNumber('tenant')->name('edit');
            Route::put('{tenant}',      [TenantController::class, 'update'])->whereNumber('tenant')->name('update');
        });
        Route::middleware('platform.ability:tenants.suspend')->group(function () {
            Route::post('{tenant}/suspend',    [TenantController::class, 'suspend'])->whereNumber('tenant')->name('suspend');
            Route::post('{tenant}/reactivate', [TenantController::class, 'reactivate'])->whereNumber('tenant')->name('reactivate');
        });
        Route::middleware('platform.ability:tenants.delete')->group(function () {
            Route::delete('{tenant}', [TenantController::class, 'destroy'])->whereNumber('tenant')->name('destroy');
        });
        Route::middleware('platform.ability:tenants.provision')->group(function () {
            Route::post('{tenant}/reprovision', [TenantController::class, 'reprovision'])->whereNumber('tenant')->name('reprovision');
        });
    });

    Route::middleware('platform.ability:plans.view')->group(function () {
        Route::resource('plans', PlanController::class)->except(['show']);
    });

    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->middleware('platform.ability:subscriptions.view')->name('index');
        Route::get('{subscription}/edit', [SubscriptionController::class, 'edit'])->whereNumber('subscription')->middleware('platform.ability:subscriptions.update')->name('edit');
        Route::put('{subscription}',      [SubscriptionController::class, 'update'])->whereNumber('subscription')->middleware('platform.ability:subscriptions.update')->name('update');
    });

    Route::middleware('platform.ability:operators.manage')->group(function () {
        Route::resource('operators', OperatorController::class)->except(['show']);
    });

    Route::middleware('platform.ability:audit.view')->group(function () {
        Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index');
    });
});
