<?php

namespace App\Console\Commands\Tenancy;

use App\Models\Platform\Tenant;
use App\Services\Tenancy\TenantSeeder;
use App\Tenancy\Tenancy;
use Illuminate\Console\Command;
use Throwable;

/**
 * Re-runs the idempotent system seeders inside one tenant schema.
 *
 * Only the safe three (roles/permissions, units, SMS templates). DemoDataSeeder
 * and PermissionSeeder are refused outright by TenantSeeder::FORBIDDEN_SEEDERS —
 * the first TRUNCATEs 17 business tables, the second installs a competing role
 * vocabulary with destructive sync() calls. Either would destroy live customer
 * data.
 */
class SeedTenantCommand extends Command
{
    protected $signature = 'tenant:seed {slug : Subdomain of the company}';

    protected $description = 'Re-run the idempotent system seeders for one company';

    public function handle(TenantSeeder $seeder): int
    {
        $tenant = Tenant::on(config('tenancy.platform_connection'))
            ->where('slug', $this->argument('slug'))
            ->first();

        if (! $tenant) {
            $this->error('No such tenant: '.$this->argument('slug'));

            return self::FAILURE;
        }

        $this->info("Seeding system defaults into {$tenant->database_name} ...");

        try {
            Tenancy::run($tenant, fn () => $seeder->seedSystemDefaults());
        } catch (Throwable $e) {
            $this->error('Seeding failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
