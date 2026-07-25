<?php

namespace App\Console\Commands\Platform;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Runs ONLY the platform migrations, against ONLY the platform connection.
 *
 * A plain `php artisan migrate` cannot do this by accident and must not be able
 * to: database/migrations/platform/ is a subdirectory, and Migrator globs its
 * paths non-recursively, so the six platform migrations are invisible to the
 * default run. That is the mechanism preventing platform tables from being
 * created inside a customer's schema.
 */
class PlatformMigrateCommand extends Command
{
    protected $signature = 'platform:migrate
        {--rollback : Roll the last batch back}
        {--status : Show migration status instead of migrating}
        {--force : Run without prompting (production). Always implied — the underlying migrate call passes --force.}';

    protected $description = 'Run the platform database migrations';

    public function handle(): int
    {
        $options = [
            '--database' => config('tenancy.platform_connection'),
            '--path' => 'database/migrations/platform',
            '--force' => true,
            '--no-interaction' => true,
        ];

        $command = match (true) {
            (bool) $this->option('status') => 'migrate:status',
            (bool) $this->option('rollback') => 'migrate:rollback',
            default => 'migrate',
        };

        $exit = Artisan::call($command, $options);
        $this->line(trim(Artisan::output()));

        return $exit === 0 ? self::SUCCESS : self::FAILURE;
    }
}
