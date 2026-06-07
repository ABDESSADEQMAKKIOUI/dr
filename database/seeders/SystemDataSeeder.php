<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\Currency;
use App\Models\Language;
use App\Models\Tax;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class SystemDataSeeder extends Seeder
{
    public function run(): void
    {
        // Payment Methods
        $paymentMethods = [
            ['name' => 'Espèces', 'code' => 'cash', 'is_active' => true],
            ['name' => 'Carte Bancaire', 'code' => 'card', 'is_active' => true],
            ['name' => 'Chèque', 'code' => 'check', 'is_active' => true],
            ['name' => 'Virement', 'code' => 'transfer', 'is_active' => true],
            ['name' => 'Crédit', 'code' => 'credit', 'is_active' => true],
        ];
        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }

        // Currencies
        Currency::create([
            'name' => 'Dirham Marocain',
            'code' => 'MAD',
            'symbol' => 'DH',
            'exchange_rate' => 1.0000,
            'is_default' => true,
        ]);

        Currency::create([
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
            'exchange_rate' => 10.5000,
            'is_default' => false,
        ]);

        // Languages
        Language::create([
            'name' => 'Français',
            'code' => 'fr',
            'is_rtl' => false,
            'is_active' => true,
            'is_default' => true,
        ]);

        Language::create([
            'name' => 'English',
            'code' => 'en',
            'is_rtl' => false,
            'is_active' => true,
            'is_default' => false,
        ]);

        Language::create([
            'name' => 'العربية',
            'code' => 'ar',
            'is_rtl' => true,
            'is_active' => true,
            'is_default' => false,
        ]);

        // Taxes
        Tax::create([
            'name' => 'TVA 20%',
            'rate' => 20.00,
            'type' => 'percentage',
            'is_active' => true,
        ]);

        Tax::create([
            'name' => 'TVA 14%',
            'rate' => 14.00,
            'type' => 'percentage',
            'is_active' => true,
        ]);

        Tax::create([
            'name' => 'TVA 10%',
            'rate' => 10.00,
            'type' => 'percentage',
            'is_active' => true,
        ]);

        // Default Warehouse
        Warehouse::create([
            'name' => 'Entrepôt Principal',
            'code' => 'WH-001',
            'phone' => '+212 5XX XX XX XX',
            'email' => 'warehouse@example.com',
            'address' => 'Casablanca',
            'city' => 'Casablanca',
            'postal_code' => '20000',
            'country' => 'Morocco',
            'is_active' => true,
        ]);
    }
}
