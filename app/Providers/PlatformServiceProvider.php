<?php

namespace App\Providers;

use App\Support\PlatformAbilities;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
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
    }
}
