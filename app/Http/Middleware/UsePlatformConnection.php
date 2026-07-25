<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Position 0 of the 'platform' middleware group (the operator console).
 *
 * Runs BEFORE StartSession, so it is the only place the per-console session
 * cookie name and file path can be set — rewriting config('session.*') after
 * StartSession has already built the session driver would be a no-op. Keeping
 * the operator session under its own cookie and directory guarantees it never
 * collides with a tenant's 'web' session.
 *
 * It also asserts the platform database is reachable up-front so an outage on
 * safm_platform renders a clean 503 here rather than a stack-trace 20 frames
 * deep inside the auth guard.
 */
class UsePlatformConnection
{
    public function handle(Request $request, Closure $next): Response
    {
        // Dedicated session cookie + storage path for the operator console.
        config([
            'session.cookie' => 'safm_platform_session',
            'session.files' => storage_path('framework/sessions/_platform'),
        ]);

        $directory = storage_path('framework/sessions/_platform');

        if (! is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        // Fail closed if the platform database cannot be reached: without it no
        // operator can be authenticated and every downstream query would throw.
        try {
            DB::connection(config('tenancy.platform_connection', 'platform'))->getPdo();
        } catch (Throwable $e) {
            abort(503, __('tenancy.platform_unavailable'));
        }

        return $next($request);
    }
}
