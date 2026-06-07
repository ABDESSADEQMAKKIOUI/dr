<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LanguageService;

class LocaleMiddleware
{
    public function __construct(protected LanguageService $languageService) {}

    public function handle(Request $request, Closure $next)
    {
        $locale = null;

        // 1. Check Session (Priority 1)
        if ($request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        }

        // 2. Check Request Parameter ?lang= (Priority 2 - overrides session temporarily if needed, but usually we'd want to set it)
        if ($request->has('lang')) {
            $locale = $request->get('lang');
        }

        // 3. Check Authenticated User (Priority 3 - if no session set yet)
        if (!$locale && $user = $request->user()) {
            $locale = $user->language;
        }

        // 4. Default Locale if nothing found
        if (!$locale) {
            $locale = $this->languageService->getDefault()->code;
        }

        // Verify validity
        $language = $this->languageService->getByCode($locale);
        
        if (!$language) {
            $locale = $this->languageService->getDefault()->code;
            $language = $this->languageService->getDefault();
        }

        // Sets the locale
        app()->setLocale($locale);

        // Store in session if not already there or if it changed via logic above (optional but good for consistency)
        if ($request->session()->get('locale') !== $locale) {
            $request->session()->put('locale', $locale);
        }

        // Share with views
        $request->attributes->set('language', $language);
        $request->attributes->set('is_rtl', $language->is_rtl);

        return $next($request);
    }
}
