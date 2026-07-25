<?php

namespace App\Console\Commands\Platform;

use App\Models\Platform\PlatformUser;
use Database\Seeders\Platform\PlanSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Throwable;

/**
 * One-shot setup for the platform database: migrate, seed the plans, and create
 * the first operator account. Safe to re-run — the entrypoint calls it on every
 * boot.
 */
class PlatformInstallCommand extends Command
{
    protected $signature = 'platform:install
        {--email= : Email for the first operator}
        {--name=Administrateur : Name for the first operator}
        {--password= : Password for that operator (generated if omitted)}
        {--skip-plans : Do not seed the default plans}
        {--force : Run without prompting (production). Accepted for symmetry with artisan migrate; this command never prompts.}';

    protected $description = 'Migrate the platform database, seed plans, and create the first operator';

    public function handle(): int
    {
        $connection = config('tenancy.platform_connection');

        $this->info('Migrating the platform database ...');

        // --path is what keeps these six migrations out of tenant schemas: the
        // default `migrate` never sees database/migrations/platform/ because
        // Migrator globs non-recursively.
        $exit = Artisan::call('migrate', [
            '--database' => $connection,
            '--path' => 'database/migrations/platform',
            '--force' => true,
            '--no-interaction' => true,
        ]);

        $this->line(trim(Artisan::output()));

        if ($exit !== 0) {
            $this->error('Platform migrations failed.');

            return self::FAILURE;
        }

        if (! $this->option('skip-plans')) {
            $this->info('Seeding plans ...');

            try {
                Artisan::call('db:seed', [
                    '--class' => PlanSeeder::class,
                    '--database' => $connection,
                    '--force' => true,
                    '--no-interaction' => true,
                ]);
            } catch (Throwable $e) {
                $this->error('Plan seeding failed: '.$e->getMessage());

                return self::FAILURE;
            }
        }

        return $this->createFirstOperator();
    }

    private function createFirstOperator(): int
    {
        $connection = config('tenancy.platform_connection');
        $email = $this->option('email');

        if (! $email) {
            $this->info('No --email given; skipping operator creation.');

            return self::SUCCESS;
        }

        $existing = PlatformUser::on($connection)->where('email', $email)->first();

        if ($existing) {
            $this->info("Operator {$email} already exists.");

            return self::SUCCESS;
        }

        $password = $this->option('password') ?: bin2hex(random_bytes(8));
        $generated = ! $this->option('password');

        $user = new PlatformUser();
        $user->setConnection($connection);
        $user->forceFill([
            'name' => $this->option('name'),
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'owner',
            'is_active' => true,
        ])->save();

        $this->newLine();
        $this->info('Operator created for '.config('tenancy.admin_domain'));
        $this->table(['', ''], [
            ['Email', $email],
            ['Password', $generated ? $password.'  (generated — shown once)' : '(as supplied)'],
            ['Role', 'owner'],
        ]);

        return self::SUCCESS;
    }
}
