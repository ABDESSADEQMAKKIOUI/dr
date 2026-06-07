<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\TranslationController;

Route::middleware(['auth:sanctum'])->prefix('i18n')->group(function () {
    // Languages
    Route::apiResource('languages', LanguageController::class);
    Route::post('languages/{language}/set-default', [LanguageController::class, 'setDefault']);

    // Translations
    Route::get('translations/{languageCode}', [TranslationController::class, 'index']);
    Route::post('translations', [TranslationController::class, 'store']);
    Route::post('translations/import', [TranslationController::class, 'import']);
    Route::get('translations/{languageCode}/export', [TranslationController::class, 'export']);
    Route::delete('translations', [TranslationController::class, 'destroy']);
    Route::get('translations/missing/{sourceLanguage}/{targetLanguage}', [TranslationController::class, 'missing']);
});

// Public endpoint pour récupérer les traductions (frontend)
Route::get('translations/{languageCode}', [TranslationController::class, 'index']);
