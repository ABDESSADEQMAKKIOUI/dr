<?php

namespace App\Services\Tenancy;

use App\Exceptions\TenantProvisioningException;
use App\Models\Currency;
use App\Models\Language;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Tax;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SmsTemplateSeeder;
use Database\Seeders\UnitSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a freshly migrated tenant schema.
 *
 * The three IDEMPOTENT stock seeders (RolePermissionSeeder, UnitSeeder,
 * SmsTemplateSeeder) are reused as-is. The three NON-IDEMPOTENT ones
 * (SystemDataSeeder, SettingsSeeder, AdminUserSeeder) are replaced here by
 * firstOrCreate/updateOrCreate equivalents that also inject the customer's own
 * values instead of the demo placeholders (admin@admin.com, "Mon Entreprise",
 * "Entrepôt Principal", …).
 *
 * The caller MUST have already pointed the default connection at the tenant
 * schema (App\Tenancy\Tenancy::useDatabase()) before calling any method here.
 */
final class TenantSeeder
{
    /**
     * Seeders that are safe to run and re-run inside a tenant schema, in order.
     * RolePermissionSeeder is first: everything downstream depends on the
     * 'Super Admin' role row and on the permissions rows existing.
     */
    public const SYSTEM_SEEDERS = [
        RolePermissionSeeder::class,
        UnitSeeder::class,
        SmsTemplateSeeder::class,
    ];

    /**
     * Seeders that must NEVER run inside a tenant schema.
     *
     * DemoDataSeeder does SET FOREIGN_KEY_CHECKS=0 + TRUNCATE across 17 business
     * tables. PermissionSeeder installs a second, incompatible role vocabulary
     * with destructive sync() calls.
     */
    public const FORBIDDEN_SEEDERS = [
        \Database\Seeders\DemoDataSeeder::class => 'TRUNCATE de 17 tables métier (SET FOREIGN_KEY_CHECKS=0)',
        \Database\Seeders\PermissionSeeder::class => 'installe un vocabulaire de rôles concurrent avec des sync() destructifs',
    ];

    /**
     * Run the three idempotent system seeders against the current (tenant) connection.
     *
     * @throws TenantProvisioningException
     */
    public function seedSystemDefaults(): void
    {
        foreach (self::SYSTEM_SEEDERS as $seeder) {
            $exitCode = Artisan::call('db:seed', [
                '--class' => $seeder,
                '--force' => true,
                '--no-interaction' => true,
            ]);

            if ($exitCode !== 0) {
                throw TenantProvisioningException::verification(
                    sprintf('le seeder %s a retourné le code %d : %s', $seeder, $exitCode, trim(Artisan::output()))
                );
            }
        }
    }

    /**
     * Replaces SystemDataSeeder. Creates the payment methods, the currency set,
     * the language set, the tax rates and EXACTLY ONE warehouse carrying the
     * customer's own name.
     *
     * The warehouse is mandatory: eleven controllers populate a required
     * warehouse_id selector from Warehouse::where('is_active', true)->get(), and
     * with zero rows no product, sale, purchase or quotation can be saved.
     *
     * @param  array<string, mixed>  $data
     */
    public function seedReferenceData(array $data): Warehouse
    {
        $this->seedPaymentMethods();
        $this->seedCurrencies((string) ($data['currency'] ?? 'MAD'));
        $this->seedLanguages((string) ($data['locale'] ?? 'fr'));
        $this->seedTaxes();

        return $this->seedWarehouse($data);
    }

    /**
     * Replaces SettingsSeeder. Setting::updateOrCreate on the UNIQUE `key`
     * column — never Setting::create(), which throws 23000 on any re-run.
     *
     * Writes 'default_currency' (the key the application actually reads), never 'currency'.
     *
     * @param  array<string, mixed>  $data
     */
    public function seedSettings(array $data): void
    {
        $companyName = (string) ($data['name'] ?? 'Mon Entreprise');
        $companyEmail = (string) ($data['contact_email'] ?? '');
        $companyPhone = (string) ($data['contact_phone'] ?? '');
        $companyAddress = (string) ($data['company_address'] ?? $data['address'] ?? '');
        $currency = strtoupper((string) ($data['currency'] ?? 'MAD'));
        $locale = strtolower((string) ($data['locale'] ?? 'fr'));
        $timezone = (string) ($data['timezone'] ?? 'Africa/Casablanca');
        $mailFromName = (string) ($data['mail_from_name'] ?? $companyName);
        $mailFromAddress = (string) ($data['mail_from_address'] ?? $companyEmail);

        $settings = [
            // General
            ['key' => 'company_name', 'value' => $companyName, 'group' => 'general', 'type' => 'text'],
            ['key' => 'company_email', 'value' => $companyEmail, 'group' => 'general', 'type' => 'text'],
            ['key' => 'company_phone', 'value' => $companyPhone, 'group' => 'general', 'type' => 'text'],
            ['key' => 'company_address', 'value' => $companyAddress, 'group' => 'general', 'type' => 'text'],
            ['key' => 'company_logo', 'value' => null, 'group' => 'general', 'type' => 'file'],
            ['key' => 'company_favicon', 'value' => null, 'group' => 'general', 'type' => 'file'],
            ['key' => 'default_language', 'value' => $locale, 'group' => 'general', 'type' => 'text'],
            ['key' => 'default_currency', 'value' => $currency, 'group' => 'general', 'type' => 'text'],
            ['key' => 'timezone', 'value' => $timezone, 'group' => 'general', 'type' => 'text'],

            // Invoice
            ['key' => 'invoice_prefix', 'value' => 'INV-', 'group' => 'invoice', 'type' => 'text'],
            ['key' => 'quotation_prefix', 'value' => 'QT-', 'group' => 'invoice', 'type' => 'text'],
            ['key' => 'sale_prefix', 'value' => 'SA-', 'group' => 'invoice', 'type' => 'text'],
            ['key' => 'purchase_prefix', 'value' => 'PO-', 'group' => 'invoice', 'type' => 'text'],
            ['key' => 'invoice_terms', 'value' => 'Paiement à 30 jours', 'group' => 'invoice', 'type' => 'textarea'],
            ['key' => 'invoice_footer', 'value' => 'Merci pour votre confiance', 'group' => 'invoice', 'type' => 'textarea'],

            // Tax
            ['key' => 'default_tax_rate', 'value' => '0', 'group' => 'tax', 'type' => 'number'],
            ['key' => 'tax_name', 'value' => 'TVA', 'group' => 'tax', 'type' => 'text'],

            // Email
            ['key' => 'mail_from_name', 'value' => $mailFromName, 'group' => 'email', 'type' => 'text'],
            ['key' => 'mail_from_address', 'value' => $mailFromAddress, 'group' => 'email', 'type' => 'text'],

            // Notifications
            ['key' => 'low_stock_alert', 'value' => '10', 'group' => 'notifications', 'type' => 'number'],
            ['key' => 'notify_low_stock', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean'],
            ['key' => 'notify_invoice_sent', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'type' => $setting['type'],
                ]
            );
        }
    }

    /**
     * Replaces AdminUserSeeder. Creates the CUSTOMER's administrator — never
     * admin@admin.com — and attaches both the 'Super Admin' and 'Admin' roles.
     *
     * NEVER passes 'name' or 'username': neither is a column on `users` nor a
     * $fillable attribute, so they would be silently discarded (this is exactly
     * the bug in App\Http\Controllers\AuthController::register).
     *
     * role_user has a COMPOSITE PRIMARY KEY (role_id, user_id), so attach()
     * throws 23000 on a re-run — syncWithoutDetaching() is mandatory.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws TenantProvisioningException when a required role row is missing
     */
    public function createAdminUser(array $data, Warehouse $warehouse): User
    {
        $email = strtolower(trim((string) ($data['admin_email'] ?? '')));

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw TenantProvisioningException::validation(
                sprintf('Adresse e-mail administrateur invalide : « %s ».', $email)
            );
        }

        $password = (string) ($data['admin_password'] ?? '');

        if ($password === '') {
            throw TenantProvisioningException::validation('Le mot de passe administrateur est obligatoire.');
        }

        $roleIds = Role::whereIn('name', ['Super Admin', 'Admin'])->pluck('id', 'name');

        foreach (['Super Admin', 'Admin'] as $roleName) {
            if (! $roleIds->has($roleName)) {
                throw TenantProvisioningException::verification(
                    sprintf('le rôle « %s » est introuvable (RolePermissionSeeder n\'a pas abouti)', $roleName)
                );
            }
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'first_name' => (string) ($data['admin_first_name'] ?? 'Admin'),
                'last_name' => (string) ($data['admin_last_name'] ?? 'Principal'),
                'phone' => $data['admin_phone'] ?? $data['contact_phone'] ?? null,
                'password' => Hash::make($password),
                'is_active' => true,
                'email_verified_at' => now(),
                'warehouse_id' => $warehouse->id,
            ]
        );

        $user->roles()->syncWithoutDetaching($roleIds->values()->all());
        $user->load('roles');

        return $user;
    }

    /**
     * The five payment methods every ERP screen expects, keyed on the unique `code`.
     */
    private function seedPaymentMethods(): void
    {
        $methods = [
            ['code' => 'cash', 'name' => 'Espèces'],
            ['code' => 'card', 'name' => 'Carte Bancaire'],
            ['code' => 'check', 'name' => 'Chèque'],
            ['code' => 'transfer', 'name' => 'Virement'],
            ['code' => 'credit', 'name' => 'Crédit'],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(
                ['code' => $method['code']],
                ['name' => $method['name'], 'is_active' => true]
            );
        }
    }

    /**
     * The currency set, with the customer's own currency flagged is_default.
     */
    private function seedCurrencies(string $default): void
    {
        $default = strtoupper(trim($default)) ?: 'MAD';

        $currencies = [
            ['code' => 'MAD', 'name' => 'Dirham Marocain', 'symbol' => 'DH', 'exchange_rate' => 1.0000],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'exchange_rate' => 10.5000],
            ['code' => 'USD', 'name' => 'Dollar Américain', 'symbol' => '$', 'exchange_rate' => 9.8000],
        ];

        $codes = array_column($currencies, 'code');

        if (! in_array($default, $codes, true)) {
            $currencies[] = [
                'code' => $default,
                'name' => $default,
                'symbol' => $default,
                'exchange_rate' => 1.0000,
            ];
        }

        foreach ($currencies as $currency) {
            Currency::firstOrCreate(
                ['code' => $currency['code']],
                [
                    'name' => $currency['name'],
                    'symbol' => $currency['symbol'],
                    'exchange_rate' => $currency['exchange_rate'],
                    'is_default' => false,
                ]
            );
        }

        // Exactly one default. Two passes so a re-run with a changed currency
        // never leaves two rows flagged.
        Currency::query()->where('is_default', true)->update(['is_default' => false]);
        Currency::query()->where('code', $default)->update(['is_default' => true]);
    }

    /**
     * The language set, with the customer's own locale flagged is_default.
     *
     * A missing default row makes App\Services\LanguageService::getDefaultLanguage()
     * firstOrFail() and every single tenant request 404s.
     */
    private function seedLanguages(string $default): void
    {
        $default = strtolower(trim($default)) ?: 'fr';

        $languages = [
            ['code' => 'fr', 'name' => 'Français', 'is_rtl' => false],
            ['code' => 'en', 'name' => 'English', 'is_rtl' => false],
            ['code' => 'ar', 'name' => 'العربية', 'is_rtl' => true],
        ];

        $codes = array_column($languages, 'code');

        if (! in_array($default, $codes, true)) {
            $languages[] = ['code' => $default, 'name' => strtoupper($default), 'is_rtl' => false];
        }

        foreach ($languages as $language) {
            Language::firstOrCreate(
                ['code' => $language['code']],
                [
                    'name' => $language['name'],
                    'is_rtl' => $language['is_rtl'],
                    'is_active' => true,
                    'is_default' => false,
                ]
            );
        }

        Language::query()->where('is_default', true)->update(['is_default' => false]);
        Language::query()->where('code', $default)->update(['is_default' => true, 'is_active' => true]);
    }

    /**
     * The Moroccan VAT rates, keyed on the unique `name`.
     */
    private function seedTaxes(): void
    {
        $taxes = [
            ['name' => 'TVA 20%', 'rate' => 20.00],
            ['name' => 'TVA 14%', 'rate' => 14.00],
            ['name' => 'TVA 10%', 'rate' => 10.00],
            ['name' => 'TVA 7%', 'rate' => 7.00],
            ['name' => 'Exonéré', 'rate' => 0.00],
        ];

        foreach ($taxes as $tax) {
            // withTrashed(): `name` is UNIQUE at the database level and taxes are
            // soft-deleted, so a trashed row still occupies the index.
            $existing = Tax::withTrashed()->where('name', $tax['name'])->first();

            if ($existing !== null) {
                continue;
            }

            Tax::create([
                'name' => $tax['name'],
                'rate' => $tax['rate'],
                'type' => 'percentage',
                'is_active' => true,
            ]);
        }
    }

    /**
     * Exactly one active warehouse, carrying the customer's own name.
     *
     * Both `name` and `code` are UNIQUE on `warehouses`, so both are checked
     * before inserting — that is what makes a re-run safe.
     *
     * @param  array<string, mixed>  $data
     */
    private function seedWarehouse(array $data): Warehouse
    {
        $name = trim((string) ($data['warehouse_name'] ?? '')) ?: (string) ($data['name'] ?? 'Entrepôt Principal');
        $code = strtoupper(trim((string) ($data['warehouse_code'] ?? ''))) ?: 'WH-001';

        // withTrashed(): both unique indexes still contain soft-deleted rows.
        $warehouse = Warehouse::withTrashed()->where('code', $code)->first()
            ?? Warehouse::withTrashed()->where('name', $name)->first()
            ?? Warehouse::where('is_active', true)->orderBy('id')->first();

        if ($warehouse === null) {
            return Warehouse::create([
                'name' => $name,
                'code' => $code,
                'phone' => $data['contact_phone'] ?? null,
                'email' => $data['contact_email'] ?? null,
                'address' => $data['company_address'] ?? $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country' => (string) ($data['country'] ?? 'Morocco'),
                'is_active' => true,
            ]);
        }

        if ($warehouse->trashed()) {
            $warehouse->restore();
        }

        if (! $warehouse->is_active) {
            $warehouse->update(['is_active' => true]);
        }

        return $warehouse;
    }
}
