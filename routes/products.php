<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\UnitController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Products
    Route::apiResource('products', ProductController::class);
    Route::post('products/import', [ProductController::class, 'importCSV']);
    Route::get('products/export', [ProductController::class, 'exportCSV']);

    // Categories
    Route::apiResource('categories', CategoryController::class);
    Route::get('categories/tree', [CategoryController::class, 'tree']);

    // Brands
    Route::apiResource('brands', BrandController::class);

    // Units
    Route::apiResource('units', UnitController::class);
});
