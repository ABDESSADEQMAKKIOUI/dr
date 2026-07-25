<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, string $ability) {
            // Platform operators authenticate on the 'platform' guard and are
            // not App\Models\User. Returning null falls through cleanly; the
            // old `User $user` type hint threw a TypeError, which extends
            // \Error and is therefore NOT caught by any catch(\Exception).
            if (! $user instanceof User) {
                return null;
            }

            // hasRole() compares LOWER(name) = ?, so 'Super Admin' (as seeded by
            // RolePermissionSeeder:78) matches only the spaced form. The old
            // 'super-admin' check never fired; the old 'admin' check DID fire
            // and silently bypassed users.delete / roles.delete /
            // settings.manage, which RolePermissionSeeder:127-129 withholds.
            if ($user->hasRole('Super Admin') || $user->hasRole('super-admin')) {
                return true;
            }

            // Lazy, connection-agnostic. Replaces the Permission::all() +
            // Gate::define() loop that ran on every request BEFORE routing --
            // in a database-per-tenant design that loop always read the wrong
            // schema, registered zero gates, and 403'd every non-admin user
            // while silently working for admins. Return null (not false) so
            // policies and other before-callbacks can still run.
            return $user->hasPermission($ability) ? true : null;
        });
    }
}
