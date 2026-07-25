<?php

namespace App\Console\Commands\Tenancy;

use App\Models\Platform\Tenant;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Console\Command;
use Throwable;

/**
 * Applies pending ERP migrations to tenant schemas.
 *
 * This is the command that stops tenant databases drifting behind a deploy.
 * Adding a 103rd migration does nothing at all until this runs: the new code
 * then meets the old schema and fails with "Unknown column". docker/entrypoint.sh
 * runs `tenant:migrate --all` on every boot for exactly that reason, and this
 * command exits non-zero if ANY tenant fails so a bad deploy is visible.
 */
class MigrateTenantsCommand extends Command
{
    protected $signature = 'tenant:migrate
        {slug? : Migrate a single company by subdomain}
        {--all : Migrate every non-archived company}
        {--seed : Also re-run the idempotent system seeders}
        {--force : Run without prompting (production). Always implied — the underlying migrate call passes --force.}';

    protected $description = 'Apply pending ERP migrations to one or every tenant database';

    public function handle(TenantProvisioner $provisioner): int
    {
        $tenants = $this->targets();

        if ($tenants->isEmpty()) {
            $this->info('No tenants to migrate.');

            return self::SUCCESS;
        }

        $withSeed = (bool) $this->option('seed');
        $failed = [];

        foreach ($tenants as $tenant) {
            $this->line("→ {$tenant->slug} ({$tenant->database_name})");

            try {
                $withSeed
                    ? $provisioner->reprovision($tenant)
                    : $provisioner->migrate($tenant);

                $this->info('  ok');
            } catch (Throwable $e) {
                // Keep going: one broken tenant must not stop the other twenty
                // from being brought up to date.
                $failed[$tenant->slug] = $e->getMessage();
                $this->error('  failed: '.$e->getMessage());
            }
        }

        if ($failed !== []) {
            $this->newLine();
            $this->error(count($failed).' of '.$tenants->count().' tenant(s) failed:');

            foreach ($failed as $slug => $message) {
                $this->error("  {$slug}: {$message}");
            }

            return self::FAILURE;
        }

        $this->newLine();
        $this->info($tenants->count().' tenant(s) migrated.');

        return self::SUCCESS;
    }

    /** @return \Illuminate\Support\Collection<int, Tenant> */
    private function targets()
    {
        $query = Tenant::on(config('tenancy.platform_connection'))
            ->whereNotIn('status', [Tenant::STATUS_ARCHIVED, Tenant::STATUS_PROVISIONING]);

        if ($slug = $this->argument('slug')) {
            return $query->where('slug', $slug)->get();
        }

        if (! $this->option('all')) {
            $this->warn('Pass a slug or --all.');

            return collect();
        }

        return $query->orderBy('slug')->get();
    }
}
