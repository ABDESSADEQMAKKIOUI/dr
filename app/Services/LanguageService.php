<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;

class LanguageService
{
    public function list(): array
    {
        return Cache::remember('languages.all', 3600, function () {
            return Language::where('is_active', true)->orderBy('is_default', 'desc')->get()->toArray();
        });
    }

    public function create(array $data): Language
    {
        // Si c'est la langue par défaut, désactiver les autres
        if ($data['is_default'] ?? false) {
            Language::where('is_default', true)->update(['is_default' => false]);
        }

        $language = Language::create([
            'name' => $data['name'],
            'code' => $data['code'], // ex: 'en', 'fr', 'ar'
            'flag' => $data['flag'] ?? null,
            'is_rtl' => $data['is_rtl'] ?? false,
            'is_active' => $data['is_active'] ?? true,
            'is_default' => $data['is_default'] ?? false,
        ]);

        Cache::forget('languages.all');
        return $language;
    }

    public function update(Language $language, array $data): Language
    {
        if ($data['is_default'] ?? false) {
            Language::where('is_default', true)->update(['is_default' => false]);
        }

        $language->update($data);
        Cache::forget('languages.all');
        
        return $language;
    }

    public function delete(Language $language): bool
    {
        if ($language->is_default) {
            throw new \Exception('Impossible de supprimer la langue par défaut');
        }

        $deleted = $language->delete();
        Cache::forget('languages.all');
        
        return $deleted;
    }

    public function getDefault(): Language
    {
        return Language::where('is_default', true)->firstOrFail();
    }

    public function getByCode(string $code): ?Language
    {
        return Language::where('code', $code)->where('is_active', true)->first();
    }
}
