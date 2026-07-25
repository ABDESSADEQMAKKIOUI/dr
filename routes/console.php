<?php

use App\Models\Platform\Tenant;
use App\Models\Setting;
use App\Tenancy\Tenancy;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Tenant-aware scheduling
|--------------------------------------------------------------------------
|
| `php artisan schedule:run` starts with config('database.default') pointing
| at config('tenancy.sentinel_database') -- a schema that deliberately does
| not exist. Nothing scheduled here may rely on "whatever connection happens
| to be default": every task must either be explicitly platform-scoped, or be
| executed once per tenant inside App\Tenancy\Tenancy::run().
|
| That is not a theoretical concern. The previous version of this file guarded
| the nightly backup with
|     ->when(fn () => \App\Models\Setting::where('key','enable_auto_backup')...)
| which, in the CLI, queried the sentinel schema: SQLSTATE[HY000][1049], the
| closure threw, and both recurring invoicing and automated backups silently
| stopped for every tenant.
|
| WHY Schedule::call() AND NOT Schedule::command() FOR THE PER-TENANT TASKS.
| Schedule::command() spawns a separate `php artisan ...` OS process, which
| boots its own container and would come up on the sentinel connection again;
| a swap performed in this process could never reach it. The two per-tenant
| entries below are therefore closures that run in-process via Artisan::call()
| while the swap is in effect.
|
| This is the same reasoning that makes a persistent worker (Octane, a real
| queue worker) unsafe here: any process that handles more than one tenant in
| its lifetime must wrap each unit of work in Tenancy::run() and restore the
| sentinel afterwards.
|
*/

/**
 * Active tenants, read from the pinned `platform` connection.
 *
 * @return Collection<int, Tenant>
 */
$activeTenants = static function (): Collection {
    return Tenant::on(config('tenancy.platform_connection'))
        ->where('status', Tenant::STATUS_ACTIVE)
        ->whereNotNull('database_name')
        ->orderBy('id')
        ->get();
};

/**
 * Run $callback with BOTH the tenant database connection and the file cache
 * pointed at $tenant, restoring both afterwards.
 *
 * Mirrors the isolation App\Http\Middleware\ResolveTenant performs for HTTP
 * requests. Sessions are irrelevant in the CLI and are deliberately not
 * touched. The cache is not: Illuminate\Cache\FileStore ignores cache.prefix
 * entirely, so only cache.stores.file.path isolates, and CacheManager
 * memoises the store while 'cache.store' is a container singleton -- both
 * have to be dropped or a command would read tenant A's cached settings while
 * running against tenant B's database.
 */
$withTenant = static function (Tenant $tenant, callable $callback) {
    $key       = str_replace('-', '_', (string) $tenant->slug);
    $cachePath = storage_path('framework/cache/'.$tenant->slug.'/data');

    if (! is_dir($cachePath)) {
        @mkdir($cachePath, 0775, true);
    }

    $previousCache = [
        'cache.prefix'                => config('cache.prefix'),
        'cache.stores.file.path'      => config('cache.stores.file.path'),
        'cache.stores.file.lock_path' => config('cache.stores.file.lock_path'),
    ];

    config([
        'cache.prefix'                => 'safm_'.$key.'_',
        'cache.stores.file.path'      => $cachePath,
        'cache.stores.file.lock_path' => $cachePath,
    ]);

    Cache::purge('file');
    app()->forgetInstance('cache.store');

    try {
        return Tenancy::run($tenant, $callback);
    } finally {
        config($previousCache);
        Cache::purge('file');
        app()->forgetInstance('cache.store');
    }
};

/**
 * Execute $callback once per active tenant. A failure in one tenant is logged
 * and never aborts the remaining tenants, and never aborts schedule:run.
 */
$forEachTenant = static function (string $task, callable $callback) use ($activeTenants, $withTenant): void {
    try {
        $tenants = $activeTenants();
    } catch (\Throwable $e) {
        Log::error("Scheduled task [{$task}] could not list tenants: ".$e->getMessage(), ['exception' => $e]);

        return;
    }

    foreach ($tenants as $tenant) {
        try {
            $withTenant($tenant, $callback);
        } catch (\Throwable $e) {
            Log::error("Scheduled task [{$task}] failed for tenant [{$tenant->slug}]: ".$e->getMessage(), [
                'tenant_id'   => $tenant->id,
                'tenant_slug' => $tenant->slug,
                'exception'   => $e,
            ]);
        }
    }
};

/**
 * Legacy single-tenant fallback (config('tenancy.enabled') === false): the
 * default connection IS the ERP database, so the task runs once, exactly as it
 * did before the SaaS conversion. Guarded so a failure cannot abort
 * schedule:run for the remaining entries.
 */
$runOnce = static function (string $task, callable $callback): void {
    try {
        $callback(null);
    } catch (\Throwable $e) {
        Log::error("Scheduled task [{$task}] failed: ".$e->getMessage(), ['exception' => $e]);
    }
};

// -- Recurring invoices ------------------------------------------------------
// Once per active tenant, inside that tenant's own database.
Schedule::call(static function () use ($forEachTenant, $runOnce): void {
    $task = static function (?Tenant $tenant = null): void {
        if (Artisan::call('invoices:process-recurring') !== 0) {
            Log::warning('invoices:process-recurring exited non-zero for tenant ['.($tenant?->slug ?? 'default').'].');
        }
    };

    if (! config('tenancy.enabled')) {
        $runOnce('invoices:process-recurring', $task);

        return;
    }

    $forEachTenant('invoices:process-recurring', $task);
})->name('recurring-invoices')->daily()->withoutOverlapping();

// -- Nightly database backup -------------------------------------------------
// The enable_auto_backup setting is per tenant, so it can only be read AFTER
// the swap -- hence the check inside the callback rather than in ->when().
// The backup file name carries the slug because storage/app/backups is shared
// by every tenant and a bare `auto_<date>` would have them overwrite one
// another.
Schedule::call(static function () use ($forEachTenant, $runOnce): void {
    $task = static function (?Tenant $tenant = null): void {
        if (Setting::where('key', 'enable_auto_backup')->value('value') !== '1') {
            return;
        }

        $prefix = $tenant ? str_replace('-', '_', (string) $tenant->slug).'_' : '';

        if (Artisan::call('backup:database', ['--name' => 'auto_'.$prefix.date('Y-m-d')]) !== 0) {
            Log::warning('backup:database exited non-zero for tenant ['.($tenant?->slug ?? 'default').'].');
        }
    };

    if (! config('tenancy.enabled')) {
        $runOnce('backup:database', $task);

        return;
    }

    $forEachTenant('backup:database', $task);
})->name('auto-backup')->dailyAt('02:00')->withoutOverlapping();

// -- Subscription / tenant status transitions --------------------------------
// Explicitly platform-scoped: the command only touches App\Models\Platform\*
// models, every one of which pins protected $connection = 'platform', so it is
// safe as a separate process left on the sentinel default connection. It is
// the ONLY writer of time-driven tenants.status transitions.
Schedule::command('platform:sync-subscriptions')->dailyAt('03:00')->withoutOverlapping();
