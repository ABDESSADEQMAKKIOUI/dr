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

            // 2. Tenant ERP, web. NO domain constraint and NO {tenant} route
            //    parameter -- a wildcard domain parameter would be injected as
            //    the first argument of all 83 controllers' actions and break
            //    every one of them. ResolveTenant validates the Host header.
            Route::middleware('tenant')
                ->group(base_path('routes/web.php'));

            // 3. Tenant ERP, api. Exact parity with what withRouting(api:) did:
            //    prefix 'api', no name prefix.
            Route::middleware('tenant-api')
                ->prefix('api')
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

        $middleware->alias([
            'platform.ability' => \App\Http\Middleware\PlatformAbility::class,
            // routes/system.php uses 'role:super-admin'; the alias never existed.
            'role'             => \App\Http\Middleware\EnsureErpRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
