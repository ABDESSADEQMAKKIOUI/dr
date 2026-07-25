<?php

namespace App\Console\Commands\Tenancy;

use App\Exceptions\TenantProvisioningException;
use App\Models\Platform\Plan;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Console\Command;

class ProvisionTenantCommand extends Command
{
    protected $signature = 'tenant:provision
        {slug : Subdomain for the company, e.g. "acme" for acme.facturation.cfpss.ma}
        {--name= : Company name}
        {--contact-email= : Billing/contact email}
        {--contact-name= : Contact person}
        {--contact-phone= : Contact phone}
        {--plan= : Plan slug (defaults to the first active plan)}
        {--trial-days= : Override the plan trial length}
        {--admin-email= : Login email for the company administrator}
        {--admin-password= : Password for that administrator (generated if omitted)}
        {--admin-first-name=Admin : Administrator first name}
        {--admin-last-name=Système : Administrator last name}
        {--locale=fr : fr, en or ar}
        {--currency=MAD : ISO currency code}
        {--timezone=Africa/Casablanca : PHP timezone}
        {--warehouse-name= : Name of the default warehouse}
        {--warehouse-code=WH-001 : Code of the default warehouse}';

    protected $description = 'Provision a new company: create its database, migrate, seed and create its administrator';

    public function handle(TenantProvisioner $provisioner): int
    {
        $slug = strtolower(trim((string) $this->argument('slug')));
        $name = $this->option('name') ?: ucfirst($slug);

        $plan = $this->resolvePlan();

        if (! $plan) {
            $this->error('No plan found. Run `php artisan platform:install` first, or pass --plan.');

            return self::FAILURE;
        }

        $adminEmail = $this->option('admin-email') ?: $this->option('contact-email');

        if (! $adminEmail) {
            $this->error('--admin-email (or --contact-email) is required: it is the login for the company administrator.');

            return self::FAILURE;
        }

        // Generated rather than defaulted, so a tenant is never created with a
        // guessable password when the operator forgets the flag.
        $adminPassword = $this->option('admin-password') ?: bin2hex(random_bytes(6));
        $generated = ! $this->option('admin-password');

        $payload = [
            'name' => $name,
            'slug' => $slug,
            'contact_name' => $this->option('contact-name'),
            'contact_email' => $this->option('contact-email') ?: $adminEmail,
            'contact_phone' => $this->option('contact-phone'),
            'locale' => $this->option('locale'),
            'currency' => $this->option('currency'),
            'timezone' => $this->option('timezone'),
            'notes' => null,
            'plan_id' => $plan->id,
            'trial_days' => $this->option('trial-days') !== null
                ? (int) $this->option('trial-days')
                : null,
            'admin_first_name' => $this->option('admin-first-name'),
            'admin_last_name' => $this->option('admin-last-name'),
            'admin_email' => $adminEmail,
            'admin_password' => $adminPassword,
            'admin_phone' => $this->option('contact-phone'),
            'warehouse_name' => $this->option('warehouse-name') ?: $name,
            'warehouse_code' => $this->option('warehouse-code'),
        ];

        $this->info("Provisioning «{$name}» at {$slug}.".config('tenancy.root_domain').' ...');
        $this->line('  This runs 102 migrations and the system seeders. Expect 30-60 seconds.');

        try {
            $tenant = $provisioner->provision($payload);
        } catch (TenantProvisioningException $e) {
            $this->newLine();
            $this->error('Provisioning failed at step: '.$e->failedStep());
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Provisioned.');
        $this->table(['', ''], [
            ['URL', 'https://'.$tenant->slug.'.'.config('tenancy.root_domain')],
            ['Database', $tenant->database_name],
            ['Plan', $plan->name],
            ['Admin login', $adminEmail],
            ['Admin password', $generated ? $adminPassword.'  (generated — shown once)' : '(as supplied)'],
        ]);

        return self::SUCCESS;
    }

    private function resolvePlan(): ?Plan
    {
        $connection = config('tenancy.platform_connection');

        if ($slug = $this->option('plan')) {
            return Plan::on($connection)->where('slug', $slug)->first();
        }

        return Plan::on($connection)->where('is_active', true)->orderBy('sort_order')->first();
    }
}
