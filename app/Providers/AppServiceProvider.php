<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Intentionally empty. SMTP settings are tenant-scoped and are applied
        // by App\Http\Middleware\ResolveTenant AFTER the connection swap.
    }
}
