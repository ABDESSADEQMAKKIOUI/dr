<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'company_name', 'value' => 'Mon Entreprise', 'group' => 'general', 'type' => 'text'],
            ['key' => 'company_email', 'value' => ' admin@example.com', 'group' => 'general', 'type' => 'text'],
            ['key' => 'company_phone', 'value' => '+212 5XX XX XX XX', 'group' => 'general', 'type' => 'text'],
            ['key' => 'company_address', 'value' => 'Casablanca, Morocco', 'group' => 'general', 'type' => 'text'],
            ['key' => 'company_logo', 'value' => null, 'group' => 'general', 'type' => 'file'],
            ['key' => 'company_favicon', 'value' => null, 'group' => 'general', 'type' => 'file'],
            ['key' => 'default_language', 'value' => 'fr', 'group' => 'general', 'type' => 'text'],
            ['key' => 'default_currency', 'value' => 'MAD', 'group' => 'general', 'type' => 'text'],
            ['key' => 'timezone', 'value' => 'Africa/Casablanca', 'group' => 'general', 'type' => 'text'],
            
            // Invoice
            ['key' => 'invoice_prefix', 'value' => 'INV-', 'group' => 'invoice', 'type' => 'text'],
            ['key' => 'quotation_prefix', 'value' => 'QT-', 'group' => 'invoice', 'type' => 'text'],
            ['key' => 'sale_prefix', 'value' => 'SA-', 'group' => 'invoice', 'type' => 'text'],
            ['key' => 'purchase_prefix', 'value' => 'PO-', 'group' => 'invoice', 'type' => 'text'],
            ['key' => 'invoice_terms', 'value' => 'Paiement à 30 jours', 'group' => 'invoice', 'type' => 'textarea'],
            ['key' => 'invoice_footer', 'value' => 'Merci pour votre confiance', 'group' => 'invoice', 'type' => 'textarea'],
            
            // Tax
            ['key' => 'default_tax_rate', 'value' => '20', 'group' => 'tax', 'type' => 'number'],
            ['key' => 'tax_name', 'value' => 'TVA', 'group' => 'tax', 'type' => 'text'],
            
            // Email
            ['key' => 'mail_from_name', 'value' => 'Mon Entreprise', 'group' => 'email', 'type' => 'text'],
            ['key' => 'mail_from_address', 'value' => 'no-reply@example.com', 'group' => 'email', 'type' => 'text'],
            
            // Notifications
            ['key' => 'low_stock_alert', 'value' => '10', 'group' => 'notifications', 'type' => 'number'],
            ['key' => 'notify_low_stock', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean'],
            ['key' => 'notify_invoice_sent', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
