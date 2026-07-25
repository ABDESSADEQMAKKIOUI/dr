<?php

namespace App\Tenancy;

use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;

final class Tenancy
{
    private static ?Tenant $tenant = null;

    public static function useTenant(Tenant $tenant): void
    {
        self::useDatabase($tenant->database_name);
        self::$tenant = $tenant;
    }

    public static function useDatabase(string $database): void
    {
        $name = config('tenancy.tenant_connection'); // 'mysql'

        // (1) CONFIG FIRST. DatabaseManager::makeConnection() reads config at
        //     call time; purging before writing would re-open the old schema.
        config(["database.connections.{$name}.database" => $database]);

        // (2) PURGE. DatabaseManager::connection() memoises $connections[$name].
        //     purge() = disconnect() + unset(). reconnect() alone is NOT enough:
        //     it calls setPdo() on the SURVIVING Connection object, which keeps
        //     its stale $database property and stale $config array.
        DB::purge($name);

        // (3) RECONNECT. Eager-open so an unprovisioned/dropped schema throws
        //     here, where the caller can render a clean 503, not 20 frames deep.
        DB::reconnect($name);

        // (4) Facade::resolveFacadeInstance() memoises 'db.schema' in
        //     Facade::$resolvedInstance. DB::purge() does not touch that cache.
        Facade::clearResolvedInstance('db.schema');
        Facade::clearResolvedInstance('db.connection');

        // Keep the elevated migration connection aimed at the same schema. It is
        // only ever used by `migrate --database=tenant_admin`, because the ERP
        // user holds DML only and cannot CREATE/ALTER/DROP a table. Pointing it
        // here means the provisioner never has to hand a schema name around.
        config(['database.connections.tenant_admin.database' => $database]);
        DB::purge('tenant_admin');
    }

    public static function forget(): void
    {
        self::useDatabase(config('tenancy.sentinel_database'));
        self::$tenant = null;
    }

    /** Point the tenant connection at $tenant for the duration of $callback. */
    public static function run(Tenant $tenant, callable $callback): mixed
    {
        $previousTenant = self::$tenant;
        $previousDb = config('database.connections.'.config('tenancy.tenant_connection').'.database');

        try {
            self::useTenant($tenant);
            return $callback($tenant);
        } finally {
            self::useDatabase($previousDb);
            self::$tenant = $previousTenant;
        }
    }

    public static function tenant(): ?Tenant { return self::$tenant; }
    public static function slug(): ?string   { return self::$tenant?->slug; }
    public static function check(): bool     { return self::$tenant !== null; }
}
