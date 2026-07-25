<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Locale resolver for the operator console. Deliberately DB-free: unlike the
 * ERP's LocaleMiddleware it never touches LanguageService (which queries the
 * tenant 'languages' table — a table that does not exist in safm_platform).
 *
 * Supported locales are the console's own translation files, fr and en. The
 * choice is persisted in the platform session and can be switched with ?lang=.
 */
class PlatformLocale
{
    /** Locales the operator console ships translations for. */
    private const SUPPORTED = ['fr', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $default = config('app.locale', 'fr');
        $locale = in_array($default, self::SUPPORTED, true) ? $default : 'fr';

        if ($request->session()->has('platform_locale')) {
            $locale = $request->session()->get('platform_locale');
        }

        if ($request->has('lang')) {
            $locale = $request->get('lang');
        }

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = 'fr';
        }

        app()->setLocale($locale);

        if ($request->session()->get('platform_locale') !== $locale) {
            $request->session()->put('platform_locale', $locale);
        }

        return $next($request);
    }
}
