<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Créer les permissions par modules
        $permissions = [
            // Dashboard
            'dashboard.view',

            // Products
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'brands.view', 'brands.create', 'brands.edit', 'brands.delete',
            
            // Stock
            'stock.view', 'stock.adjust', 'stock.transfer',
            'warehouses.view', 'warehouses.create', 'warehouses.edit', 'warehouses.delete',
            
            // Sales
            'sales.view', 'sales.create', 'sales.edit', 'sales.delete',
            'quotations.view', 'quotations.create', 'quotations.edit', 'quotations.delete',
            
            // Purchases
            'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.delete',
            
            // Invoices
            'invoices.view', 'invoices.create', 'invoices.send', 'invoices.delete',
            
            // Customers & Suppliers
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            
            // Reports
            'reports.view', 'reports.export',
            
            // Expenses
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
            
            // HRM
            'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
            'payrolls.view', 'payrolls.create',
            
            // CRM
            'leads.view', 'leads.create', 'leads.edit', 'leads.delete',
            'opportunities.view', 'opportunities.create', 'opportunities.edit',
            
            // Accounting
            'accounting.view', 'accounting.manage',
            
            // Settings
            'settings.view', 'settings.manage',
            
            // Users & Roles
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
        ];

        foreach ($permissions as $permission) {
            $group = explode('.', $permission)[0];
            Permission::firstOrCreate(
                ['name' => $permission],
                [
                    'group' => $group,
                    'guard_name' => 'web',
                ]
            );
        }

        // Créer les rôles
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin'],
            [
                'guard_name' => 'web',
                'description' => 'Super administrateur avec tous les accès',
                'is_default' => false,
            ]
        );

        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin'],
            [
                'guard_name' => 'web',
                'description' => 'Administrateur avec la plupart des accès',
                'is_default' => false,
            ]
        );

        $managerRole = Role::firstOrCreate(
            ['name' => 'Manager'],
            [
                'guard_name' => 'web',
                'description' => 'Manager avec accès limité',
                'is_default' => false,
            ]
        );

        $sellerRole = Role::firstOrCreate(
            ['name' => 'Vendeur'],
            [
                'guard_name' => 'web',
                'description' => 'Vendeur avec accès ventes uniquement',
                'is_default' => false,
            ]
        );

        $cashierRole = Role::firstOrCreate(
            ['name' => 'Caissier'],
            [
                'guard_name' => 'web',
                'description' => 'Caissier POS',
                'is_default' => true,
            ]
        );

        // Assigner toutes les permissions au Super Admin
        $superAdminRole->permissions()->syncWithoutDetaching(Permission::all());

        // Permissions Admin (tout sauf users et settings critiques)
        $adminPermissions = Permission::whereNotIn('name', [
            'users.delete', 'roles.delete', 'settings.manage'
        ])->get();
        $adminRole->permissions()->syncWithoutDetaching($adminPermissions);

        // Permissions Manager
        $managerPermissions = Permission::whereIn('group', [
            'dashboard', 'products', 'categories', 'brands', 'stock', 'sales', 'purchases',
            'customers', 'suppliers', 'reports', 'expenses'
        ])->get();
        $managerRole->permissions()->syncWithoutDetaching($managerPermissions);

        // Permissions Vendeur
        $sellerPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'products.view', 'sales.view', 'sales.create', 'sales.edit',
            'quotations.view', 'quotations.create', 'quotations.edit',
            'customers.view', 'customers.create', 'customers.edit',
            'invoices.view', 'invoices.create', 'invoices.send',
        ])->get();
        $sellerRole->permissions()->syncWithoutDetaching($sellerPermissions);

        // Permissions Caissier
        $cashierPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'products.view', 'sales.view', 'sales.create', 'customers.view'
        ])->get();
        $cashierRole->permissions()->syncWithoutDetaching($cashierPermissions);
    }
}
