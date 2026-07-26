<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // `web:` and `api:` are DELIBERATELY ABSENT. Both files are registered
        // inside then(), after the platform group, so that the admin subdomain
        // is claimed first. See routingApproach.
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // 1. Operator console. Domain-pinned, registered FIRST.
            Route::domain(config('tenancy.admin_domain'))
                ->middleware('platform')
                ->name('platform.')
                ->group(base_path('routes/platform.php'));

            // 2. Public marketing site on the APEX. Domain-pinned like the
            //    console and, like it, registered BEFORE the tenant group --
            //    routes/web.php has no domain constraint, so if it went first
            //    it would match GET / on every host and swallow the apex.
            //
            //    The 'site' group deliberately excludes ResolveTenant: the apex
            //    is not a tenant and ResolveTenant 404s that host by design.
            Route::domain(config('tenancy.root_domain'))
                ->middleware('site')
                ->name('site.')
                ->group(base_path('routes/site.php'));

            // 3. Tenant ERP, web. NO domain constraint and NO {tenant} route
            //    parameter -- a wildcard domain parameter would be injected as
            //    the first argument of all 83 controllers' actions and break
            //    every one of them. ResolveTenant validates the Host header.
            Route::middleware('tenant')
                ->group(base_path('routes/web.php'));

            // 4. Tenant ERP, api.
            //
            //    ->name('api.') IS LOAD-BEARING, and its absence was a real bug.
            //
            //    routes/api.php does not define its own routes: it `require`s
            //    the SAME module route files as routes/web.php (products.php,
            //    sales.php, ...). Every route name is therefore registered
            //    twice, and Laravel's UrlGenerator keeps the LAST registration
            //    for a given name. With no prefix here, this group ran after the
            //    web group and silently rebound every name to its /api/*
            //    counterpart — so route('products.index') in the sidebar
            //    generated /api/products instead of /products.
            //
            //    The symptom was that every sidebar link led to an auth:sanctum
            //    endpoint: a 500 before Sanctum was installed, a redirect back
            //    to the dashboard afterwards. Typing /products by hand always
            //    worked, because the web route itself was fine all along.
            //
            //    Namespacing the API names keeps route() resolving to the web
            //    URLs. The API is still reachable at /api/*; its names are now
            //    api.products.index and so on.
            Route::middleware('tenant-api')
                ->prefix('api')
                ->name('api.')
                ->group(base_path('routes/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ── Tenant ERP web stack. Stock 'web' group, with ResolveTenant at 0.
        $middleware->group('tenant', [
            \App\Http\Middleware\ResolveTenant::class,
            \App\Http\Middleware\CheckInstalled::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\LocaleMiddleware::class,
        ]);

        // ── Tenant ERP api stack. Laravel 11's default 'api' group is exactly
        //    [SubstituteBindings] (no throttle: apiLimiter is null unless
        //    ->throttleApi() is called, and no rate limiter named 'api' is
        //    defined anywhere in this app). Parity + ResolveTenant.
        $middleware->group('tenant-api', [
            \App\Http\Middleware\ResolveTenant::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // ── Operator console. Stock 'web' minus the two tenant-DB-coupled
        //    middlewares (CheckInstalled, LocaleMiddleware).
        $middleware->group('platform', [
            \App\Http\Middleware\UsePlatformConnection::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\PlatformLocale::class,
        ]);

        // ── Public site on the apex. Stock 'web' minus everything that assumes
        //    a resolved tenant:
        //      ResolveTenant   the apex is not a tenant; it aborts on this host.
        //      CheckInstalled  reads the tenant database's installation state.
        //      LocaleMiddleware queries the tenant 'languages' table.
        //    Sessions ARE present: the demo form needs a CSRF token and the
        //    thank-you redirect carries a flash message. UsePlatformConnection
        //    is intentionally absent too -- it would point anonymous visitors'
        //    session files at the operator console's own session directory.
        $middleware->group('site', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->alias([
            'platform.ability' => \App\Http\Middleware\PlatformAbility::class,
            // routes/system.php uses 'role:super-admin'; the alias never existed.
            'role'             => \App\Http\Middleware\EnsureErpRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
