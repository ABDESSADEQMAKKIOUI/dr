<?php

namespace App\Console\Commands\Tenancy;

use App\Models\Platform\Tenant;
use Illuminate\Console\Command;

class ListTenantsCommand extends Command
{
    protected $signature = 'tenant:list {--status= : Filter by status}';

    protected $description = 'List every company on this installation';

    public function handle(): int
    {
        $query = Tenant::on(config('tenancy.platform_connection'))->with('plan')->orderBy('slug');

        if ($status = $this->option('status')) {
            $query->where('status', $status);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->info('No tenants.');

            return self::SUCCESS;
        }

        $root = config('tenancy.root_domain');

        $this->table(
            ['Subdomain', 'Company', 'Status', 'Plan', 'Database', 'URL'],
            $tenants->map(fn (Tenant $t) => [
                $t->slug,
                $t->name,
                $t->status,
                $t->plan?->name ?? '—',
                $t->database_name,
                "https://{$t->slug}.{$root}",
            ])->all()
        );

        return self::SUCCESS;
    }
}
