<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseCategoryController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Expenses
    Route::apiResource('expenses', ExpenseController::class);
    Route::get('expenses/stats', [ExpenseController::class, 'stats']);

    // Expense Categories
    Route::apiResource('expense-categories', ExpenseCategoryController::class);
});
