<?php

namespace App\Providers;

use App\Support\PlatformAbilities;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/**
 * Registers the ONLY view-layer authorisation primitive for the operator
 * console: the @platformCan / @endplatformCan Blade conditional.
 *
 * It deliberately does NOT touch the framework Gate. Gate resolves its user
 * from the default ('web') guard, so @can is blind to the 'platform' guard and
 * would silently deny every operator. All console authorisation therefore flows
 * through PlatformAbilities: the 'platform.ability' middleware for routes and
 * @platformCan for views.
 */
class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Blade::if('platformCan', function (string $ability): bool {
            $operator = Auth::guard('platform')->user();

            return $operator !== null && $operator->hasAbility($ability);
        });

        $this->registerLoginRateLimiters();
    }

    /**
     * Brute-force protection for both login forms.
     *
     * This matters far more than it would in a single-tenant app. The operator
     * console is reachable from the internet with no network-level gate in front
     * of it, and an operator account can CREATE and DROP customer databases —
     * so an unlimited password oracle there is the highest-value target in the
     * whole system. Neither login had any limit before.
     *
     * Two limits per form, deliberately:
     *   - per email+IP  stops someone grinding one account
     *   - per IP        stops someone spraying one password across many accounts,
     *                   which the first limit alone would never notice
     *
     * Keyed on the lowercased email so alternating capitalisation is not a
     * trivial bypass.
     */
    private function registerLoginRateLimiters(): void
    {
        RateLimiter::for('platform-login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(5)->by('pf:'.$email.'|'.$request->ip()),
                Limit::perMinute(20)->by('pf-ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('erp-login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            // Slightly more generous: a tenant's staff share an office IP, and
            // locking out a whole company over one person's typo is its own
            // kind of outage.
            return [
                Limit::perMinute(5)->by('erp:'.$request->getHost().'|'.$email),
                Limit::perMinute(40)->by('erp-ip:'.$request->ip()),
            ];
        });
    }
}
