<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        // SaaS mode: tenant databases are provisioned by TenantProvisioner, not
        // by the wizard. storage/app/.installed is a single process-global file
        // and cannot express per-tenant state, so the wizard is removed from the
        // routing surface entirely rather than made tenant-aware.
        if (config('tenancy.enabled')) {
            if ($request->is('install') || $request->is('install/*')) {
                abort(404);
            }

            return $next($request);
        }

        // ── Legacy single-tenant behaviour, unchanged ────────────────────────
        $installed = file_exists(storage_path('app/.installed'));

        if ($request->is('install') || $request->is('install/*')) {
            if ($installed) {
                return redirect('/')->with('error', 'Application is already installed.');
            }

            return $next($request);
        }

        if (!$installed) {
            return redirect('/install');
        }

        return $next($request);
    }
}
