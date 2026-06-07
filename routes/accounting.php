<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\TransactionController;

Route::middleware(['auth:sanctum'])->prefix('accounting')->group(function () {
    // Chart of Accounts
    Route::apiResource('accounts', AccountController::class);
    Route::get('accounts-tree', [AccountController::class, 'tree']);

    // Journals
    Route::apiResource('journals', JournalController::class);

    // Transactions
    Route::apiResource('transactions', TransactionController::class);
    Route::get('general-ledger', [TransactionController::class, 'generalLedger']);
    Route::get('trial-balance', [TransactionController::class, 'trialBalance']);
    Route::get('vat-report', [TransactionController::class, 'vatReport']);
});
