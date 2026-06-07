<?php

namespace App\Http\Controllers;

use App\Services\TranslationService;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function __construct(protected TranslationService $translationService) {}

    /**
     * Obtenir toutes les traductions pour une langue
     */
    public function index(Request $request, string $languageCode)
    {
        $group = $request->get('group');
        $translations = $this->translationService->getTranslations($languageCode, $group);
        
        return response()->json($translations);
    }

    /**
     * Créer/Mettre à jour une traduction
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'language_code' => 'required|exists:languages,code',
            'key' => 'required|string',
            'value' => 'required|string',
            'group' => 'nullable|string',
        ]);

        $translation = $this->translationService->set(
            $validated['language_code'],
            $validated['key'],
            $validated['value'],
            $validated['group'] ?? null
        );

        return response()->json($translation, 201);
    }

    /**
     * Import bulk de traductions
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'language_code' => 'required|exists:languages,code',
            'translations' => 'required|array',
            'group' => 'nullable|string',
        ]);

        $count = $this->translationService->importBulk(
            $validated['language_code'],
            $validated['translations'],
            $validated['group'] ?? 'general'
        );

        return response()->json(['message' => "{$count} traductions importées"]);
    }

    /**
     * Export des traductions
     */
    public function export(string $languageCode)
    {
        $translations = $this->translationService->export($languageCode);
        return response()->json($translations);
    }

    /**
     * Supprimer une traduction
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'language_code' => 'required|exists:languages,code',
            'key' => 'required|string',
        ]);

        $this->translationService->delete($validated['language_code'], $validated['key']);
        
        return response()->json(['message' => 'Traduction supprimée']);
    }

    /**
     * Obtenir les clés manquantes
     */
    public function missing(string $sourceLanguage, string $targetLanguage)
    {
        $missing = $this->translationService->getMissingKeys($sourceLanguage, $targetLanguage);
        
        return response()->json([
            'count' => count($missing),
            'keys' => $missing,
        ]);
    }
}
