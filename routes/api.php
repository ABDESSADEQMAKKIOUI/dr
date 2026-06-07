<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

Route::get('/', function () {
    return response()->json([
        'name' => 'ERP SaaS API',
        'version' => '1.0.0',
        'status' => 'running'
    ]);
});

// Load all module routes
require __DIR__ . '/dashboard.php';
require __DIR__ . '/products.php';
require __DIR__ . '/stock.php';
require __DIR__ . '/purchases.php';
require __DIR__ . '/sales.php';
require __DIR__ . '/payments.php';
require __DIR__ . '/expenses.php';
require __DIR__ . '/reports.php';
require __DIR__ . '/accounting.php';
require __DIR__ . '/hrm.php';
require __DIR__ . '/crm.php';
require __DIR__ . '/saas.php';
require __DIR__ . '/i18n.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/system.php';
