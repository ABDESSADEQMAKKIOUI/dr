<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    /**
     * Obtenir toutes les traductions pour une langue
     */
    public function getTranslations(string $languageCode, ?string $group = null): array
    {
        $cacheKey = "translations.{$languageCode}" . ($group ? ".{$group}" : '');

        return Cache::remember($cacheKey, 3600, function () use ($languageCode, $group) {
            $query = Translation::where('language_code', $languageCode);

            if ($group) {
                $query->where('group', $group);
            }

            return $query->get()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Traduire une clé
     */
    public function translate(string $key, string $languageCode, array $replacements = []): string
    {
        $translations = $this->getTranslations($languageCode);
        $translation = $translations[$key] ?? $key;

        // Remplacer les placeholders
        foreach ($replacements as $placeholder => $value) {
            $translation = str_replace(":{$placeholder}", $value, $translation);
        }

        return $translation;
    }

    /**
     * Créer ou mettre à jour une traduction
     */
    public function set(string $languageCode, string $key, string $value, ?string $group = null): Translation
    {
        $translation = Translation::updateOrCreate(
            [
                'language_code' => $languageCode,
                'key' => $key,
            ],
            [
                'value' => $value,
                'group' => $group ?? 'general',
            ]
        );

        // Invalider le cache
        Cache::forget("translations.{$languageCode}");
        Cache::forget("translations.{$languageCode}.{$group}");

        return $translation;
    }

    /**
     * Import bulk de traductions
     */
    public function importBulk(string $languageCode, array $translations, string $group = 'general'): int
    {
        $count = 0;

        foreach ($translations as $key => $value) {
            $this->set($languageCode, $key, $value, $group);
            $count++;
        }

        return $count;
    }

    /**
     * Export des traductions
     */
    public function export(string $languageCode): array
    {
        return Translation::where('language_code', $languageCode)
            ->get()
            ->groupBy('group')
            ->map(fn($items) => $items->pluck('value', 'key'))
            ->toArray();
    }

    /**
     * Supprimer une traduction
     */
    public function delete(string $languageCode, string $key): bool
    {
        $deleted = Translation::where('language_code', $languageCode)
            ->where('key', $key)
            ->delete();

        Cache::forget("translations.{$languageCode}");
        
        return $deleted > 0;
    }

    /**
     * Obtenir les clés manquantes pour une langue
     */
    public function getMissingKeys(string $sourceLanguage, string $targetLanguage): array
    {
        $sourceKeys = Translation::where('language_code', $sourceLanguage)->pluck('key')->toArray();
        $targetKeys = Translation::where('language_code', $targetLanguage)->pluck('key')->toArray();

        return array_diff($sourceKeys, $targetKeys);
    }
}
