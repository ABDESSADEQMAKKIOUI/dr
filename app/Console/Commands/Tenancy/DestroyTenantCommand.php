<?php

namespace App\Console\Commands\Tenancy;

use App\Models\Platform\Tenant;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Console\Command;

class DestroyTenantCommand extends Command
{
    protected $signature = 'tenant:destroy
        {slug : Subdomain of the company to remove}
        {--drop-database : Also DROP the schema. Irreversible.}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Remove a company. Without --drop-database the schema is left on disk.';

    public function handle(TenantProvisioner $provisioner): int
    {
        $tenant = Tenant::on(config('tenancy.platform_connection'))
            ->where('slug', $this->argument('slug'))
            ->first();

        if (! $tenant) {
            $this->error('No such tenant: '.$this->argument('slug'));

            return self::FAILURE;
        }

        $drop = (bool) $this->option('drop-database');

        $this->table(['', ''], [
            ['Company', $tenant->name],
            ['Subdomain', $tenant->slug],
            ['Database', $tenant->database_name],
            ['Status', $tenant->status],
        ]);

        if ($drop) {
            $this->error('--drop-database will PERMANENTLY DESTROY this company\'s data.');
        } else {
            $this->warn('The schema '.$tenant->database_name.' will be LEFT ON DISK (recoverable).');
        }

        // Typing the slug back is deliberate: a y/N prompt is too easy to answer
        // reflexively for something that can destroy a customer's invoices.
        if (! $this->option('force')) {
            $typed = $this->ask('Type the subdomain to confirm');

            if ($typed !== $tenant->slug) {
                $this->info('Aborted.');

                return self::SUCCESS;
            }
        }

        $provisioner->destroy($tenant, $drop);

        $this->info($drop
            ? "Tenant {$tenant->slug} removed and schema dropped."
            : "Tenant {$tenant->slug} removed. Schema {$tenant->database_name} kept.");

        return self::SUCCESS;
    }
}
