<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Define all permissions grouped by module
        $permissions = [
            // Dashboard
            'dashboard' => [
                'dashboard.view' => 'View Dashboard',
            ],
            
            // Users
            'users' => [
                'users.view' => 'View Users',
                'users.create' => 'Create Users',
                'users.edit' => 'Edit Users',
                'users.delete' => 'Delete Users',
            ],
            
            // Roles
            'roles' => [
                'roles.view' => 'View Roles',
                'roles.create' => 'Create Roles',
                'roles.edit' => 'Edit Roles',
                'roles.delete' => 'Delete Roles',
            ],
            
            // Products
            'products' => [
                'products.view' => 'View Products',
                'products.create' => 'Create Products',
                'products.edit' => 'Edit Products',
                'products.delete' => 'Delete Products',
            ],
            
            // Stock
            'stock' => [
                'stock.view' => 'View Stock',
                'stock.adjust' => 'Adjust Stock',
                'stock.transfer' => 'Transfer Stock',
            ],
            
            // Purchases
            'purchases' => [
                'purchases.view' => 'View Purchases',
                'purchases.create' => 'Create Purchases',
                'purchases.edit' => 'Edit Purchases',
                'purchases.delete' => 'Delete Purchases',
            ],
            
            // Suppliers
            'suppliers' => [
                'suppliers.view' => 'View Suppliers',
                'suppliers.create' => 'Create Suppliers',
                'suppliers.edit' => 'Edit Suppliers',
                'suppliers.delete' => 'Delete Suppliers',
            ],
            
            // Sales
            'sales' => [
                'sales.view' => 'View Sales',
                'sales.create' => 'Create Sales',
                'sales.pos' => 'Access POS',
                'sales.delete' => 'Delete Sales',
            ],
            
            // Customers
            'customers' => [
                'customers.view' => 'View Customers',
                'customers.create' => 'Create Customers',
                'customers.edit' => 'Edit Customers',
                'customers.delete' => 'Delete Customers',
            ],
            
            // Quotations
            'quotations' => [
                'quotations.view' => 'View Quotations',
                'quotations.create' => 'Create Quotations',
                'quotations.edit' => 'Edit Quotations',
                'quotations.delete' => 'Delete Quotations',
            ],
            
            // Invoices
            'invoices' => [
                'invoices.view' => 'View Invoices',
                'invoices.create' => 'Create Invoices',
                'invoices.edit' => 'Edit Invoices',
            ],
            
            // Payments
            'payments' => [
                'payments.view' => 'View Payments',
                'payments.create' => 'Create Payments',
            ],
            
            // Expenses
            'expenses' => [
                'expenses.view' => 'View Expenses',
                'expenses.create' => 'Create Expenses',
                'expenses.edit' => 'Edit Expenses',
                'expenses.delete' => 'Delete Expenses',
            ],
            
            // Reports
            'reports' => [
                'reports.view' => 'View Reports',
                'reports.export' => 'Export Reports',
            ],
            
            // Accounting
            'accounting' => [
                'accounting.view' => 'View Accounting',
                'accounting.transactions' => 'Manage Transactions',
                'accounting.journals' => 'Manage Journals',
            ],
            
            // Employees
            'employees' => [
                'employees.view' => 'View Employees',
                'employees.create' => 'Create Employees',
                'employees.edit' => 'Edit Employees',
                'employees.delete' => 'Delete Employees',
                'employees.payroll' => 'Manage Payroll',
                'employees.attendance' => 'Manage Attendance',
            ],
            
            // CRM
            'crm' => [
                'crm.leads' => 'Manage Leads',
                'crm.opportunities' => 'Manage Opportunities',
                'crm.activities' => 'Manage Activities',
            ],
            
            // Settings
            'settings' => [
                'settings.view' => 'View Settings',
                'settings.edit' => 'Edit Settings',
            ],
            
            // Tenants (Super Admin only)
            'tenants' => [
                'tenants.view' => 'View Tenants',
                'tenants.manage' => 'Manage Tenants',
            ],
        ];

        // Create all permissions
        foreach ($permissions as $group => $perms) {
            foreach ($perms as $name => $description) {
                Permission::firstOrCreate(
                    ['name' => $name],
                    [
                        'guard_name' => 'web',
                        'group' => $group,
                        'description' => $description,
                    ]
                );
            }
        }

        // Create default roles
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['guard_name' => 'web', 'description' => 'Administrator with full access', 'is_default' => false]
        );

        $managerRole = Role::firstOrCreate(
            ['name' => 'manager'],
            ['guard_name' => 'web', 'description' => 'Manager with limited access', 'is_default' => false]
        );

        $userRole = Role::firstOrCreate(
            ['name' => 'user'],
            ['guard_name' => 'web', 'description' => 'Regular user', 'is_default' => true]
        );

        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super-admin'],
            ['guard_name' => 'web', 'description' => 'Super Administrator', 'is_default' => false]
        );

        // Assign ALL permissions to admin and super-admin
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));
        $superAdminRole->permissions()->sync($allPermissions->pluck('id'));

        // Assign limited permissions to manager
        $managerPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'products.view', 'products.create', 'products.edit',
            'stock.view', 'stock.adjust',
            'purchases.view', 'purchases.create',
            'suppliers.view',
            'sales.view', 'sales.create', 'sales.pos',
            'customers.view', 'customers.create',
            'quotations.view', 'quotations.create',
            'invoices.view', 'invoices.create',
            'payments.view', 'payments.create',
            'expenses.view', 'expenses.create',
            'reports.view',
            'employees.view',
        ])->get();
        $managerRole->permissions()->sync($managerPermissions->pluck('id'));

        // Assign basic permissions to user role
        $userPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'products.view',
            'sales.view', 'sales.create', 'sales.pos',
            'customers.view',
            'quotations.view',
            'invoices.view',
        ])->get();
        $userRole->permissions()->sync($userPermissions->pluck('id'));
    }
}
