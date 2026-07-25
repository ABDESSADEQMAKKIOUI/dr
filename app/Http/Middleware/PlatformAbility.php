<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authorises the current operator against the hard-coded ability matrix in
 * App\Support\PlatformAbilities. Aliased as 'platform.ability'.
 *
 * The framework Gate is NEVER used on the operator console: Gate resolves its
 * user from the default ('web') guard, so an operator authenticated on the
 * 'platform' guard is invisible to @can and every check silently returns false.
 * This middleware and the @platformCan Blade conditional are the only two
 * authorisation surfaces on the panel.
 */
class PlatformAbility
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $operator = Auth::guard('platform')->user();

        if ($operator === null || ! $operator->hasAbility($ability)) {
            abort(403, __('tenancy.forbidden'));
        }

        return $next($request);
    }
}
