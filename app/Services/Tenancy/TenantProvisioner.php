<?php

namespace App\Services\Tenancy;

use App\Exceptions\TenantProvisioningException;
use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use App\Services\Platform\PlatformAudit;
use App\Services\Platform\SubscriptionManager;
use App\Tenancy\Tenancy;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/**
 * Creates, repairs and destroys tenants.
 *
 * The ordering here is load-bearing and is documented step by step below. Two
 * invariants matter more than anything else:
 *
 *   1. The platform row is written BEFORE the schema is created, because the
 *      UNIQUE constraints on tenants.slug and tenants.database_name are the only
 *      thing serialising two operators provisioning the same slug at once.
 *
 *   2. The default connection is ALWAYS restored, on every path, success or
 *      failure. A leaked tenant connection means the next thing to touch the
 *      database in this request writes into the wrong customer's schema. That is
 *      why every tenant-scoped block runs inside Tenancy::run(), which restores
 *      in a finally.
 */
final class TenantProvisioner
{
    public function __construct(
        private readonly TenantDatabaseManager $databases,
        private readonly TenantSeeder $seeder,
        private readonly PlatformAudit $audit,
        private readonly SubscriptionManager $subscriptions,
    ) {
    }

    /**
     * Provision a brand new tenant, end to end.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws TenantProvisioningException
     */
    public function provision(array $data): Tenant
    {
        // ── 1. Validate ───────────────────────────────────────────────────────
        $slug = $this->validateSlug((string) ($data['slug'] ?? ''));
        $databaseName = $this->databases->databaseNameFor($slug);

        $plan = Plan::on(config('tenancy.platform_connection'))->find($data['plan_id'] ?? null);

        if (! $plan) {
            throw TenantProvisioningException::validation(
                __('app.plan').' introuvable (id: '.($data['plan_id'] ?? 'null').')'
            );
        }

        // ── 2. Platform row first ─────────────────────────────────────────────
        // Written before any DDL so the UNIQUE indexes reject a concurrent
        // duplicate before we start creating schemas.
        $tenant = new Tenant();
        $tenant->setConnection(config('tenancy.platform_connection'));
        $tenant->forceFill([
            'name' => $data['name'],
            'slug' => $slug,
            'database_name' => $databaseName,
            'status' => Tenant::STATUS_PROVISIONING,
            'plan_id' => $plan->id,
            'contact_name' => $data['contact_name'] ?? null,
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'] ?? null,
            'locale' => $data['locale'] ?? 'fr',
            'currency' => $data['currency'] ?? 'MAD',
            'timezone' => $data['timezone'] ?? 'Africa/Casablanca',
            'notes' => $data['notes'] ?? null,
        ])->save();

        $schemaCreated = false;

        try {
            // ── 3. Subscription ───────────────────────────────────────────────
            $trialDays = isset($data['trial_days']) ? (int) $data['trial_days'] : null;
            $this->subscriptions->start($tenant, $plan, $trialDays);

            // ── 4. Create the schema ──────────────────────────────────────────
            // $schemaCreated distinguishes "we made it" from "it was already
            // there", so rollback never drops a schema we did not create.
            $schemaCreated = $this->databases->create($databaseName);

            // ── 5-11. Everything below runs against the TENANT schema ─────────
            // Tenancy::run restores the previous database in a finally, so the
            // connection cannot leak even if a seeder throws.
            Tenancy::run($tenant, function () use ($data, $tenant): void {
                // 6. Migrate. The six platform migrations live in
                //    database/migrations/platform/ and Migrator globs
                //    non-recursively, so they cannot land in a tenant schema.
                $this->runStep('migrate', fn () => Artisan::call('migrate', [
                    // Elevated connection: the ERP user has DML only and cannot
                    // create tables. Tenancy::useDatabase has already pointed
                    // tenant_admin at this tenant's schema.
                    '--database' => 'tenant_admin',
                    '--force' => true,
                    '--no-interaction' => true,
                ]));

                // 7. Roles, permissions, units, SMS templates.
                $this->runStep('seed_system', fn () => $this->seeder->seedSystemDefaults());

                // 8. Payment methods, currencies, languages, taxes, and the one
                //    mandatory warehouse.
                $warehouse = $this->runStep(
                    'seed_reference',
                    fn () => $this->seeder->seedReferenceData($data)
                );

                // 9. The 23 settings rows, carrying the customer's own company
                //    name, currency, locale and timezone.
                $this->runStep('seed_settings', fn () => $this->seeder->seedSettings($data));

                // 10. The CUSTOMER's admin account — never admin@admin.com.
                $this->runStep(
                    'create_admin',
                    fn () => $this->seeder->createAdminUser($data, $warehouse)
                );

                // 11. Verify loudly rather than hand over a subtly broken tenant.
                $this->verify($tenant, $data);
            });

            // ── 13. Flip status. Only now is the subdomain live. ──────────────
            $tenant->forceFill([
                'status' => Tenant::STATUS_ACTIVE,
                'provisioned_at' => now(),
                'provision_error' => null,
            ])->save();
        } catch (Throwable $e) {
            $this->rollback($tenant, $databaseName, $schemaCreated, $e);

            throw $e instanceof TenantProvisioningException
                ? $e
                : TenantProvisioningException::step('provision', $e);
        }

        $this->audit->log('tenant.provisioned', $tenant, $tenant, [
            'database' => $databaseName,
            'plan' => $plan->slug,
        ]);

        // ── 14. Best-effort post-provisioning. Never fatal: the tenant is
        //        already usable, and a Let's Encrypt hiccup must not undo an
        //        otherwise successful provision.
        $this->prepareTenantStorage($tenant);
        $this->requestCertificate($tenant);

        return $tenant;
    }

    /**
     * Bring an existing tenant's schema back up to date: pending migrations plus
     * the idempotent system seeders. Used to retry a failed provision and after
     * a deploy that adds migrations. Safe to run repeatedly.
     *
     * @throws TenantProvisioningException
     */
    public function reprovision(Tenant $tenant): void
    {
        $this->assertSchemaExists($tenant);

        try {
            Tenancy::run($tenant, function (): void {
                $this->runStep('migrate', fn () => Artisan::call('migrate', [
                    // Elevated connection: the ERP user has DML only and cannot
                    // create tables. Tenancy::useDatabase has already pointed
                    // tenant_admin at this tenant's schema.
                    '--database' => 'tenant_admin',
                    '--force' => true,
                    '--no-interaction' => true,
                ]));

                $this->runStep('seed_system', fn () => $this->seeder->seedSystemDefaults());
            });
        } catch (Throwable $e) {
            $this->recordFailure($tenant, $e);

            throw $e instanceof TenantProvisioningException
                ? $e
                : TenantProvisioningException::step('reprovision', $e);
        }
    }

    /**
     * Apply pending ERP migrations only — no seeding, no user creation.
     * This is what tenant:migrate runs across every tenant after a deploy.
     *
     * @throws TenantProvisioningException
     */
    public function migrate(Tenant $tenant): void
    {
        $this->assertSchemaExists($tenant);

        try {
            Tenancy::run($tenant, function (): void {
                $this->runStep('migrate', fn () => Artisan::call('migrate', [
                    // Elevated connection: the ERP user has DML only and cannot
                    // create tables. Tenancy::useDatabase has already pointed
                    // tenant_admin at this tenant's schema.
                    '--database' => 'tenant_admin',
                    '--force' => true,
                    '--no-interaction' => true,
                ]));
            });
        } catch (Throwable $e) {
            $this->recordFailure($tenant, $e);

            throw $e instanceof TenantProvisioningException
                ? $e
                : TenantProvisioningException::step('migrate', $e);
        }
    }

    /**
     * Remove a tenant.
     *
     * $dropDatabase=false soft-deletes the platform row and leaves the schema on
     * disk — the reversible option, and the default everywhere in the UI.
     * $dropDatabase=true destroys the customer's data irrecoverably.
     */
    public function destroy(Tenant $tenant, bool $dropDatabase = false): void
    {
        $databaseName = $tenant->database_name;

        $this->audit->log('tenant.destroyed', $tenant, $tenant, [
            'database' => $databaseName,
            'database_dropped' => $dropDatabase,
        ]);

        if ($dropDatabase) {
            // TenantDatabaseManager refuses to drop the platform or sentinel
            // schema, so a corrupted database_name cannot take the stack out.
            $this->databases->drop($databaseName);
        }

        $tenant->forceFill(['status' => Tenant::STATUS_ARCHIVED])->save();
        $tenant->delete();
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @throws TenantProvisioningException
     */
    private function validateSlug(string $slug): string
    {
        $slug = Str::lower(trim($slug));

        if ($slug === '' || ! preg_match(config('tenancy.slug_pattern'), $slug)) {
            throw TenantProvisioningException::validation(
                "Le sous-domaine « {$slug} » est invalide (minuscules, chiffres et tirets ; 63 caractères max)."
            );
        }

        if (in_array($slug, (array) config('tenancy.reserved_slugs', []), true)) {
            throw TenantProvisioningException::validation(
                "Le sous-domaine « {$slug} » est réservé."
            );
        }

        return $slug;
    }

    /**
     * Run one provisioning step, tagging any failure with the step name so
     * tenants.provision_error says which part broke rather than just "SQLSTATE".
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     *
     * @throws TenantProvisioningException
     */
    private function runStep(string $step, callable $callback): mixed
    {
        try {
            return $callback();
        } catch (TenantProvisioningException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw TenantProvisioningException::step($step, $e);
        }
    }

    /**
     * Assert the tenant schema is actually usable before we hand it to a
     * customer. Each check corresponds to a way this specific application breaks
     * when a seeder half-ran.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws TenantProvisioningException
     */
    private function verify(Tenant $tenant, array $data): void
    {
        foreach (['users', 'settings', 'roles', 'permissions', 'warehouses', 'languages'] as $table) {
            if (! Schema::hasTable($table)) {
                throw TenantProvisioningException::verification("table manquante : {$table}");
            }
        }

        // LocaleMiddleware -> LanguageService::getDefault() does firstOrFail() on
        // this row for EVERY request. Without it the tenant 404s on every page.
        if (! DB::table('languages')->where('is_default', true)->exists()) {
            throw TenantProvisioningException::verification('aucune langue par défaut');
        }

        // Eleven controllers populate a required warehouse_id selector from
        // active warehouses. With none, nothing can be saved.
        if (! DB::table('warehouses')->where('is_active', true)->exists()) {
            throw TenantProvisioningException::verification('aucun entrepôt actif');
        }

        // AuthServiceProvider defines one gate per permissions row. With zero
        // rows every can() returns false and the tenant admin logs in to a UI
        // where nothing is permitted — the silent failure mode.
        if (! DB::table('permissions')->exists()) {
            throw TenantProvisioningException::verification('aucune permission');
        }

        $adminEmail = $data['admin_email'] ?? null;

        if ($adminEmail && ! DB::table('users')->where('email', $adminEmail)->exists()) {
            throw TenantProvisioningException::verification(
                "compte administrateur absent : {$adminEmail}"
            );
        }
    }

    /**
     * @throws TenantProvisioningException
     */
    private function assertSchemaExists(Tenant $tenant): void
    {
        if (! $this->databases->exists($tenant->database_name)) {
            throw TenantProvisioningException::verification(
                "le schéma {$tenant->database_name} n'existe pas"
            );
        }
    }

    /**
     * Undo a failed provision. Deliberately swallows its own errors: the caller
     * is already throwing, and a rollback failure must not mask the real cause.
     */
    private function rollback(Tenant $tenant, string $databaseName, bool $schemaCreated, Throwable $cause): void
    {
        if ($schemaCreated) {
            try {
                $this->databases->drop($databaseName);
            } catch (Throwable $e) {
                Log::error('tenant rollback: could not drop '.$databaseName, ['exception' => $e]);
            }
        }

        $this->recordFailure($tenant, $cause);

        $this->audit->log('tenant.provision_failed', $tenant, $tenant, [
            'database' => $databaseName,
            'schema_dropped' => $schemaCreated,
            'error' => $cause->getMessage(),
        ]);
    }

    private function recordFailure(Tenant $tenant, Throwable $cause): void
    {
        try {
            $tenant->forceFill([
                'status' => Tenant::STATUS_FAILED,
                'provision_error' => Str::limit($cause->getMessage(), 60000),
            ])->save();
        } catch (Throwable $e) {
            Log::error('tenant rollback: could not mark tenant failed', ['exception' => $e]);
        }
    }

    /**
     * Pre-create the per-tenant session and cache directories that ResolveTenant
     * points at, so the first request never races an @mkdir under load.
     *
     * Two traps here, both of which produce the same symptom — every page on the
     * new tenant 500s with "Failed to open stream: Permission denied" from
     * FileSessionHandler:
     *
     *   1. mkdir()'s mode is masked by the process umask (022), so a requested
     *      0775 lands as 0755 and the group loses write. chmod() is NOT masked,
     *      so the mode has to be re-applied afterwards.
     *   2. `artisan tenant:provision` runs as root, so the directories it creates
     *      are root-owned — unwritable by the www-data process that Apache later
     *      serves the tenant with. Provisioning from the web UI runs as www-data
     *      already, hence the posix_geteuid() guard.
     */
    private function prepareTenantStorage(Tenant $tenant): void
    {
        // Inherit whoever owns the storage tree rather than hard-coding
        // www-data, so this stays correct if the image's web user changes.
        $reference = storage_path('framework/sessions');
        $owner = @fileowner($reference);
        $group = @filegroup($reference);

        $paths = [
            storage_path('framework/sessions/'.$tenant->slug),
            storage_path('framework/cache/'.$tenant->slug),
            storage_path('framework/cache/'.$tenant->slug.'/data'),
        ];

        foreach ($paths as $path) {
            try {
                if (! is_dir($path) && ! @mkdir($path, 0775, true) && ! is_dir($path)) {
                    Log::warning('tenant storage: could not create '.$path);

                    continue;
                }

                @chmod($path, 0775);

                if ($owner !== false && function_exists('posix_geteuid') && posix_geteuid() === 0) {
                    @chown($path, $owner);

                    if ($group !== false) {
                        @chgrp($path, $group);
                    }
                }
            } catch (Throwable $e) {
                Log::warning('tenant storage: could not prepare '.$path, ['exception' => $e]);
            }
        }
    }

    /**
     * Ask the certbot sidecar for a certificate by dropping a request file in a
     * shared volume.
     *
     * Deliberately fire-and-forget. Let's Encrypt allows 50 certificates per
     * registered domain per week and every tenant draws from that one bucket, so
     * this writes ONE request per tenant and never retries in a loop. Until it is
     * answered the tenant is served by the wildcard self-signed fallback vhost —
     * a browser warning, not an outage.
     */
    private function requestCertificate(Tenant $tenant): void
    {
        if (! config('tenancy.cert_issuance', false)) {
            return;
        }

        $dir = config('tenancy.cert_request_dir', '/var/www/certbot-requests');

        try {
            if (! is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }

            // The ".request" suffix is REQUIRED, not decorative: the certbot
            // sidecar's drain loop globs "$REQUEST_DIR"/*.request and renames
            // each entry to .done or .failed as it goes. A file written without
            // the suffix is never matched, never issued and never reported — the
            // tenant just stays on the self-signed fallback forever.
            $host = $tenant->slug.'.'.config('tenancy.root_domain');
            @file_put_contents(rtrim($dir, '/').'/'.$host.'.request', $host."\n");
        } catch (Throwable $e) {
            Log::warning('tenant cert: could not queue a request for '.$tenant->slug, [
                'exception' => $e,
            ]);
        }
    }
}
