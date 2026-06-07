<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function __construct(protected \App\Services\LanguageService $languageService) {}

    /**
     * Switch the application locale
     *
     * @param string $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch($locale)
    {
        // Check if the language exists and is active using the service
        $language = $this->languageService->getByCode($locale);
        
        if (!$language) {
            return redirect()->back()->with('error', __('Language not supported'));
        }

        // Store the locale in the session
        Session::put('locale', $language->code);

        // If user is authenticated, save to their profile
        if (auth()->check()) {
            auth()->user()->update(['language' => $language->code]);
        }

        // Redirect back to the previous page
        return redirect()->back()->with('success', __('Language changed successfully'));
    }
}
