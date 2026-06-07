<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        $installed = file_exists(storage_path('app/.installed'));

        // Allow install routes regardless
        if ($request->is('install') || $request->is('install/*')) {
            if ($installed) {
                return redirect('/')->with('error', 'Application is already installed.');
            }
            return $next($request);
        }

        // Block all other routes until installed
        if (!$installed) {
            return redirect('/install');
        }

        return $next($request);
    }
}
