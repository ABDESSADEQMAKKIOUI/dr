<?php

namespace Database\Seeders\Platform;

use App\Models\Platform\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Seed the starter set of plans. Idempotent: keyed on plans.slug so it can
     * be re-run after deploy without creating duplicates. All prices in MAD.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Essai',
                'slug' => 'essai',
                'description' => "Période d'essai gratuite pour découvrir la plateforme.",
                'price' => 0,
                'currency' => 'MAD',
                'billing_period' => 'monthly',
                'trial_days' => 14,
                'max_users' => 2,
                'max_warehouses' => 1,
                'max_products' => 100,
                'features' => [
                    'pos' => true,
                    'accounting' => false,
                    'hrm' => false,
                    'projects' => false,
                    'shipments' => false,
                    'recurring_invoices' => false,
                    'warranties' => false,
                    'combo_products' => false,
                    'deposits' => false,
                    'sms' => false,
                    'support' => 'communautaire',
                ],
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'description' => 'Pour les petites entreprises: facturation, stock et caisse.',
                'price' => 199,
                'currency' => 'MAD',
                'billing_period' => 'monthly',
                'trial_days' => 14,
                'max_users' => 5,
                'max_warehouses' => 2,
                'max_products' => 2000,
                'features' => [
                    'pos' => true,
                    'accounting' => true,
                    'hrm' => false,
                    'projects' => false,
                    'shipments' => false,
                    'recurring_invoices' => true,
                    'warranties' => false,
                    'combo_products' => true,
                    'deposits' => false,
                    'sms' => false,
                    'support' => 'email',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Pour les entreprises en croissance: multi-dépôts et RH.',
                'price' => 499,
                'currency' => 'MAD',
                'billing_period' => 'monthly',
                'trial_days' => 14,
                'max_users' => 20,
                'max_warehouses' => 10,
                'max_products' => 50000,
                'features' => [
                    'pos' => true,
                    'accounting' => true,
                    'hrm' => true,
                    'projects' => true,
                    'shipments' => true,
                    'recurring_invoices' => true,
                    'warranties' => true,
                    'combo_products' => true,
                    'deposits' => true,
                    'sms' => true,
                    'support' => 'prioritaire',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Entreprise',
                'slug' => 'entreprise',
                'description' => 'Sur mesure: utilisateurs, dépôts et produits illimités.',
                'price' => 1499,
                'currency' => 'MAD',
                'billing_period' => 'monthly',
                'trial_days' => 0,
                'max_users' => null,
                'max_warehouses' => null,
                'max_products' => null,
                'features' => [
                    'pos' => true,
                    'accounting' => true,
                    'hrm' => true,
                    'projects' => true,
                    'shipments' => true,
                    'recurring_invoices' => true,
                    'warranties' => true,
                    'combo_products' => true,
                    'deposits' => true,
                    'sms' => true,
                    'support' => 'dédié',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
