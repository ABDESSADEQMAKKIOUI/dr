<?php

namespace App\Http\Controllers;

use App\Services\LanguageService;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function __construct(protected LanguageService $languageService) {}

    public function index()
    {
        return response()->json($this->languageService->list());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:languages',
            'flag' => 'nullable|string',
            'is_rtl' => 'boolean',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $language = $this->languageService->create($validated);
        return response()->json($language, 201);
    }

    public function show(Language $language)
    {
        return response()->json($language);
    }

    public function update(Request $request, Language $language)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_rtl' => 'boolean',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $language = $this->languageService->update($language, $validated);
        return response()->json($language);
    }

    public function destroy(Language $language)
    {
        $this->languageService->delete($language);
        return response()->json(['message' => 'Langue supprimée']);
    }

    public function setDefault(Language $language)
    {
        $this->languageService->update($language, ['is_default' => true]);
        return response()->json(['message' => 'Langue par défaut définie']);
    }
}
