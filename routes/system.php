<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SystemController;

Route::middleware(['auth:sanctum', 'role:super-admin'])->prefix('system')->group(function () {
    // Cache
    Route::get('cache/stats', [SystemController::class, 'cacheStats']);
    Route::post('cache/clear', [SystemController::class, 'clearCache']);

    // Backups
    Route::post('backups', [SystemController::class, 'createBackup']);
    Route::get('backups', [SystemController::class, 'listBackups']);
    Route::get('backups/{filename}', [SystemController::class, 'downloadBackup']);
    Route::delete('backups/{filename}', [SystemController::class, 'deleteBackup']);

    // System Info
    Route::get('info', [SystemController::class, 'systemInfo']);
});
