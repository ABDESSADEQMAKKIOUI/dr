<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\SaleDraftController;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\EmailLogController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\ComboProductController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\OfficeShiftController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\DepositCategoryController;
use App\Http\Controllers\MoneyTransferController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\RecurringInvoiceController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ErrorLogController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\InstallController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Installation Wizard (no auth, blocked once APP_INSTALLED=true)
Route::prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('index');
    Route::get('/{step}', [InstallController::class, 'step'])->name('step')->where('step', '[1-6]');
    Route::post('/{step}', [InstallController::class, 'processStep'])->name('process')->where('step', '[1-6]');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
    Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Locale Switch (Public)
Route::get('locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Documentation
    Route::get('docs', fn() => view('docs.index', [
        'accent' => \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5',
    ]))->name('docs');

    // Dashboard
    Route::middleware('can:dashboard.view')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('dashboard/sales-chart', [DashboardController::class, 'salesChart'])->name('dashboard.sales-chart');
        Route::get('dashboard/revenue-vs-expenses', [DashboardController::class, 'revenueVsExpenses'])->name('dashboard.revenue-vs-expenses');
        Route::get('dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    });
    
    // Users Management
    Route::resource('users', UserController::class);
    Route::get('users/{user}/profile', [UserController::class, 'profile'])->name('users.profile');
    
    // Roles & Permissions
    Route::resource('roles', RoleController::class);
    Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
    
    // Product Categories, Brands, Units
    Route::prefix('products')->name('products.')->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::resource('brands', BrandController::class);
        Route::resource('units', UnitController::class);
    });

    // Products Module
    Route::resource('products', ProductController::class);
    Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
    Route::get('products/{product}/serials', [ProductController::class, 'serials'])->name('products.serials');

    // Duplicate check (AJAX)
    Route::get('api/check-duplicate', [ProductController::class, 'checkDuplicate'])->name('check-duplicate');
    
    // Stock Management Module
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::resource('warehouses', WarehouseController::class);
        Route::get('adjustments', [StockController::class, 'adjustments'])->name('adjustments.index');
        Route::get('adjustments/create', [StockController::class, 'adjustmentsCreate'])->name('adjustments.create');
        Route::post('adjustments', [StockController::class, 'adjustmentsStore'])->name('adjustments.store');
        Route::get('adjustments/{adjustment}', [StockController::class, 'adjustmentsShow'])->name('adjustments.show');
        Route::get('adjustments/{adjustment}/edit', [StockController::class, 'adjustmentsEdit'])->name('adjustments.edit');
        Route::put('adjustments/{adjustment}', [StockController::class, 'adjustmentsUpdate'])->name('adjustments.update');
        Route::delete('adjustments/{adjustment}', [StockController::class, 'adjustmentsDestroy'])->name('adjustments.destroy');
        Route::get('transfers', [StockController::class, 'transfers'])->name('transfers.index');
        Route::get('transfers/create', [StockController::class, 'transfersCreate'])->name('transfers.create');
        Route::post('transfers', [StockController::class, 'transfersStore'])->name('transfers.store');
        Route::get('transfers/{transfer}', [StockController::class, 'transfersShow'])->name('transfers.show');
        Route::get('transfers/{transfer}/edit', [StockController::class, 'transfersEdit'])->name('transfers.edit');
        Route::put('transfers/{transfer}', [StockController::class, 'transfersUpdate'])->name('transfers.update');
        Route::delete('transfers/{transfer}', [StockController::class, 'transfersDestroy'])->name('transfers.destroy');
        Route::get('inventory', [StockController::class, 'inventory'])->name('inventory.index');
        Route::get('inventory/count', [StockController::class, 'inventoryCount'])->name('inventory.count');
        Route::post('inventory/count', [StockController::class, 'storeInventoryCount'])->name('inventory.store_count');
        Route::get('inventory/products-by-warehouse', [StockController::class, 'productsByWarehouse'])->name('inventory.products_by_warehouse');
        Route::get('alerts', [StockController::class, 'alerts'])->name('alerts.index');

        // Stock Count Workflow (Phase 4)
        Route::get('inventory/counts', [StockController::class, 'inventoryCountList'])->name('inventory.counts');
        Route::get('inventory/counts/start', [StockController::class, 'inventoryCountStart'])->name('inventory.count-start');
        Route::post('inventory/counts', [StockController::class, 'inventoryCountCreate'])->name('inventory.count-create');
        Route::get('inventory/counts/{count}', [StockController::class, 'inventoryCountShow'])->name('inventory.count-show');
        Route::get('inventory/counts/{count}/entry', [StockController::class, 'inventoryCountEntry'])->name('inventory.count-entry');
        Route::post('inventory/counts/{count}/save', [StockController::class, 'inventoryCountSave'])->name('inventory.count-save');
        Route::post('inventory/counts/{count}/complete', [StockController::class, 'inventoryCountComplete'])->name('inventory.count-complete');
        Route::post('inventory/counts/{count}/approve', [StockController::class, 'inventoryCountApprove'])->name('inventory.count-approve');
        Route::delete('inventory/counts/{count}', [StockController::class, 'inventoryCountDestroy'])->name('inventory.count-destroy');
    });
    
    // Supplier Due tracking (must be before resource to avoid {supplier} binding)
    Route::get('suppliers/due', [SupplierController::class, 'due'])->name('suppliers.due');
    Route::post('suppliers/{supplier}/pay-due', [SupplierController::class, 'payDue'])->name('suppliers.pay-due');

    // Purchases & Suppliers Module
    Route::resource('suppliers', SupplierController::class);
    Route::prefix('purchases/returns')->name('purchases.returns.')->group(function () {
        Route::get('/', [PurchaseController::class, 'returns'])->name('index');
        Route::get('/create', [PurchaseController::class, 'createReturn'])->name('create');
        Route::post('/', [PurchaseController::class, 'storeReturn'])->name('store');
        Route::get('/{id}', [PurchaseController::class, 'showReturn'])->name('show');
        Route::post('/{id}/apply', [PurchaseController::class, 'applyReturn'])->name('apply');
    });
    Route::post('purchases/import', [PurchaseController::class, 'import'])->name('purchases.import');
    Route::get('purchases/sample-csv', [PurchaseController::class, 'downloadSample'])->name('purchases.sample');
    Route::resource('purchases', PurchaseController::class);
    
    // Customer Due tracking (must be before resource to avoid {customer} binding)
    Route::get('customers/due', [CustomerController::class, 'due'])->name('customers.due');
    Route::post('customers/{customer}/pay-due', [CustomerController::class, 'payDue'])->name('customers.pay-due');

    // Sales & Customers Module
    Route::resource('customers', CustomerController::class);
    Route::prefix('sales/returns')->name('sales.returns.')->group(function () {
        Route::get('/', [SaleController::class, 'returns'])->name('index');
        Route::get('/create', [SaleController::class, 'createReturn'])->name('create');
        Route::post('/', [SaleController::class, 'storeReturn'])->name('store');
        Route::get('/{id}', [SaleController::class, 'showReturn'])->name('show');
        Route::post('/{id}/update-status', [SaleController::class, 'updateReturnStatus'])->name('update-status');
    });
    Route::get('pos', [SaleController::class, 'pos'])->name('sales.pos');
    Route::post('pos', [SaleController::class, 'posStore'])->name('sales.pos.store');
    Route::get('sales/{sale}/print', [SaleController::class, 'print'])->name('sales.print');
    Route::get('sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
    Route::resource('sales', SaleController::class);

    // Barcode Routes
    Route::get('barcodes/print', [BarcodeController::class, 'print'])->name('barcodes.print');
    Route::get('api/products/generate-barcode', [BarcodeController::class, 'generate'])->name('barcodes.generate');
    Route::get('api/products/by-barcode', [BarcodeController::class, 'findByBarcode'])->name('products.by-barcode');

    // POS Draft / Hold Sales Routes
    Route::get('api/pos/drafts', [SaleDraftController::class, 'index'])->name('pos.drafts.index');
    Route::post('api/pos/drafts', [SaleDraftController::class, 'store'])->name('pos.drafts.store');
    Route::delete('api/pos/drafts/{draft}', [SaleDraftController::class, 'destroy'])->name('pos.drafts.destroy');
    
    // Quotations & Invoices
    Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convert'])->name('quotations.convert');
    Route::get('quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');
    Route::resource('quotations', QuotationController::class);
    Route::get('invoices/{invoice}/print', [\App\Http\Controllers\InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('invoices/{invoice}/download', [\App\Http\Controllers\InvoiceController::class, 'download'])->name('invoices.download');
    Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
    
    // Payments Module
    Route::resource('payments', PaymentController::class);
    
    // Expenses Module
    Route::prefix('expenses/categories')->name('expenses.categories.')->group(function () {
        Route::get('/', [ExpenseController::class, 'categories'])->name('index');
        Route::get('/create', [ExpenseController::class, 'categoriesCreate'])->name('create');
        Route::post('/', [ExpenseController::class, 'categoriesStore'])->name('store');
        Route::get('/{category}/edit', [ExpenseController::class, 'categoriesEdit'])->name('edit');
        Route::put('/{category}', [ExpenseController::class, 'categoriesUpdate'])->name('update');
        Route::delete('/{category}', [ExpenseController::class, 'categoriesDestroy'])->name('destroy');
    });
    Route::resource('expenses', ExpenseController::class);
    
    // Reports Module
    Route::prefix('reports')->name('reports.')->group(function () {
        // Existing reports
        Route::get('sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('purchases', [ReportController::class, 'purchases'])->name('purchases');
        Route::get('stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('financial', [ReportController::class, 'financial'])->name('financial');
        Route::get('customers', [ReportController::class, 'customers'])->name('customers');

        // Phase 3 — Extended Sales Reports
        Route::get('sales-by-category', [ReportController::class, 'salesByCategory'])->name('sales-by-category');
        Route::get('sales-by-brand', [ReportController::class, 'salesByBrand'])->name('sales-by-brand');
        Route::get('sales-by-warehouse', [ReportController::class, 'salesByWarehouse'])->name('sales-by-warehouse');
        Route::get('sales-by-payment-method', [ReportController::class, 'salesByPaymentMethod'])->name('sales-by-payment-method');
        Route::get('sales-by-user', [ReportController::class, 'salesByUser'])->name('sales-by-user');
        Route::get('top-products', [ReportController::class, 'topProducts'])->name('top-products');
        Route::get('top-customers', [ReportController::class, 'topCustomers'])->name('top-customers');

        // Phase 3 — Purchase Reports
        Route::get('purchases-by-warehouse', [ReportController::class, 'purchasesByWarehouse'])->name('purchases-by-warehouse');
        Route::get('purchases-by-user', [ReportController::class, 'purchasesByUser'])->name('purchases-by-user');

        // Phase 3 — Customer / Supplier Detail
        Route::get('customers/{customer}', [ReportController::class, 'customerDetail'])->name('customer-detail');
        Route::get('suppliers/{supplier}', [ReportController::class, 'supplierDetail'])->name('supplier-detail');

        // Phase 3 — Warehouse & Financial
        Route::get('stock-by-warehouse', [ReportController::class, 'stockByWarehouse'])->name('stock-by-warehouse');
        Route::get('payment-transactions', [ReportController::class, 'paymentTransactions'])->name('payment-transactions');
        Route::get('inventory-valuation', [ReportController::class, 'inventoryValuation'])->name('inventory-valuation');

        // Phase 3 — CSV Export
        Route::get('export-csv', [ReportController::class, 'exportCsv'])->name('export-csv');

        // Missing: Supplier summary, Product purchases
        Route::get('suppliers', [ReportController::class, 'suppliers'])->name('suppliers');
        Route::get('product-purchases', [ReportController::class, 'productPurchases'])->name('product-purchases');

        // Missing: User-level reports
        Route::get('quotations-by-user', [ReportController::class, 'quotationsByUser'])->name('quotations-by-user');
        Route::get('returns-by-user', [ReportController::class, 'returnsByUser'])->name('returns-by-user');
        Route::get('transfers-by-user', [ReportController::class, 'transfersByUser'])->name('transfers-by-user');
        Route::get('adjustments-by-user', [ReportController::class, 'adjustmentsByUser'])->name('adjustments-by-user');

        // Missing: Warehouse-level reports
        Route::get('expenses-by-warehouse', [ReportController::class, 'expensesByWarehouse'])->name('expenses-by-warehouse');
        Route::get('quotations-by-warehouse', [ReportController::class, 'quotationsByWarehouse'])->name('quotations-by-warehouse');

        // Missing: Financial reports
        Route::get('deposits', [ReportController::class, 'depositsReport'])->name('deposits');
        Route::get('money-transfers', [ReportController::class, 'moneyTransfersReport'])->name('money-transfers');

        // FIFO / Average Cost P&L
        Route::get('profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
    });

    // Customer Due tracking
    Route::get('customers/due', [CustomerController::class, 'due'])->name('customers.due');
    Route::post('customers/{customer}/pay-due', [CustomerController::class, 'payDue'])->name('customers.pay-due');

    // Supplier Due tracking
    Route::get('suppliers/due', [SupplierController::class, 'due'])->name('suppliers.due');
    Route::post('suppliers/{supplier}/pay-due', [SupplierController::class, 'payDue'])->name('suppliers.pay-due');
    
    // Accounting Module
    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::resource('accounts', AccountController::class);
        Route::resource('transactions', TransactionController::class);
        Route::get('journals', [AccountController::class, 'journals'])->name('journals.index');
        Route::get('tax', [TaxController::class, 'index'])->name('tax.index');
        Route::get('tax/declaration', [TaxController::class, 'declaration'])->name('tax.declaration');
    });
    
    // Employees (HRM) Module — sub-routes MUST come before the resource to avoid
    // Route::resource generating GET employees/{employee} that swallows "attendance" etc.

    // Attendance
    Route::get('employees/attendance',                    [\App\Http\Controllers\AttendanceWebController::class, 'index'])->name('employees.attendance.index');
    Route::post('employees/attendance',                   [\App\Http\Controllers\AttendanceWebController::class, 'store'])->name('employees.attendance.store');
    Route::delete('employees/attendance/{attendance}',    [\App\Http\Controllers\AttendanceWebController::class, 'destroy'])->name('employees.attendance.destroy');

    // Payroll
    Route::get('employees/payroll',                       [\App\Http\Controllers\PayrollWebController::class, 'index'])->name('employees.payroll.index');
    Route::get('employees/payroll/create',                [\App\Http\Controllers\PayrollWebController::class, 'create'])->name('employees.payroll.create');
    Route::post('employees/payroll',                      [\App\Http\Controllers\PayrollWebController::class, 'store'])->name('employees.payroll.store');
    Route::get('employees/payroll/{payroll}',             [\App\Http\Controllers\PayrollWebController::class, 'show'])->name('employees.payroll.show');
    Route::post('employees/payroll/{payroll}/mark-paid',  [\App\Http\Controllers\PayrollWebController::class, 'markPaid'])->name('employees.payroll.mark-paid');

    // Leaves
    Route::get('employees/leaves',                        [\App\Http\Controllers\LeaveController::class, 'index'])->name('employees.leaves.index');
    Route::get('employees/leaves/create',                 [\App\Http\Controllers\LeaveController::class, 'create'])->name('employees.leaves.create');
    Route::post('employees/leaves',                       [\App\Http\Controllers\LeaveController::class, 'store'])->name('employees.leaves.store');
    Route::get('employees/leaves/{leave}',                [\App\Http\Controllers\LeaveController::class, 'show'])->name('employees.leaves.show');
    Route::post('employees/leaves/{leave}/status',        [\App\Http\Controllers\LeaveController::class, 'updateStatus'])->name('employees.leaves.update-status');
    Route::delete('employees/leaves/{leave}',             [\App\Http\Controllers\LeaveController::class, 'destroy'])->name('employees.leaves.destroy');

    // Employee CRUD resource (registered last so {employee} wildcard doesn't shadow the above)
    Route::resource('employees', EmployeeController::class);
    Route::resource('departments', \App\Http\Controllers\DepartmentController::class);
    Route::resource('designations', \App\Http\Controllers\DesignationController::class);
    
    // CRM Module
    Route::prefix('crm')->name('crm.')->group(function () {
        Route::get('leads', fn() => view('crm.leads.index'))->name('leads.index');
        Route::get('leads/create', fn() => view('crm.leads.create'))->name('leads.create');
        Route::get('opportunities', fn() => view('crm.opportunities.index'))->name('opportunities.index');
        Route::get('opportunities/create', fn() => view('crm.opportunities.create'))->name('opportunities.create');
        Route::get('activities', fn() => view('crm.activities.index'))->name('activities.index');
        Route::get('activities/create', fn() => view('crm.activities.create'))->name('activities.create');
    });
    
    // 2FA Routes
    Route::get('settings/security', [TwoFactorController::class, 'setup'])->name('settings.security');
    Route::post('settings/security/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::post('settings/security/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
    Route::get('auth/two-factor', [TwoFactorController::class, 'showVerify'])->name('2fa.verify-page')->withoutMiddleware('auth');
    Route::post('auth/two-factor', [TwoFactorController::class, 'verify'])->name('2fa.verify')->withoutMiddleware('auth');

    // Settings Module
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('general', [SettingController::class, 'general'])->name('general');
        Route::post('general', [SettingController::class, 'updateGeneral'])->name('general.update');
        Route::get('invoice', [SettingController::class, 'invoice'])->name('invoice');
        Route::post('invoice', [SettingController::class, 'updateInvoice'])->name('invoice.update');
        Route::get('tax', [SettingController::class, 'tax'])->name('tax');
        Route::post('tax', [SettingController::class, 'updateTax'])->name('tax.update');
        Route::get('notifications', [SettingController::class, 'notifications'])->name('notifications');
        Route::post('notifications', [SettingController::class, 'updateNotifications'])->name('notifications.update');
        Route::get('languages', [SettingController::class, 'languages'])->name('languages');
        Route::post('languages', [SettingController::class, 'updateLanguages'])->name('languages.update');
        Route::get('currencies', [SettingController::class, 'currencies'])->name('currencies');
        Route::post('currencies', [SettingController::class, 'updateCurrencies'])->name('currencies.update');
        Route::get('email-templates', [SettingController::class, 'emailTemplates'])->name('email-templates');
        Route::post('email-templates', [SettingController::class, 'updateEmailTemplates'])->name('email-templates.update');
        Route::get('backup', [SettingController::class, 'backup'])->name('backup');
        Route::post('backup/create', [SettingController::class, 'createBackup'])->name('backup.create');
        Route::post('backup/schedule', [SettingController::class, 'saveBackupSchedule'])->name('backup.schedule');
        Route::get('backup/download', [SettingController::class, 'downloadBackup'])->name('backup.download');
        Route::post('backup/delete', [SettingController::class, 'deleteBackup'])->name('backup.delete');
        Route::post('backup/restore', [SettingController::class, 'restoreBackup'])->name('backup.restore');
        Route::get('pos', [SettingController::class, 'pos'])->name('pos');
        Route::post('pos', [SettingController::class, 'updatePos'])->name('pos.update');
        Route::get('email-smtp', [SettingController::class, 'emailSmtp'])->name('email-smtp');
        Route::post('email-smtp', [SettingController::class, 'updateEmailSmtp'])->name('email-smtp.update');
        Route::post('email-smtp/test', [SettingController::class, 'testEmailSmtp'])->name('email-smtp.test');
    });
    
    // Tenants (SaaS) Module
    Route::resource('tenants', TenantController::class);

    // Stripe Payment Gateway
    Route::get('invoices/{invoice}/pay', [PaymentGatewayController::class, 'invoicePayPage'])->name('invoices.pay');
    Route::post('api/payments/stripe/intent', [PaymentGatewayController::class, 'createIntent'])->name('stripe.intent');
    Route::post('api/payments/stripe/confirm', [PaymentGatewayController::class, 'confirmPayment'])->name('stripe.confirm');
    Route::post('api/payments/stripe/charge-saved', [PaymentGatewayController::class, 'chargeSavedCard'])->name('stripe.charge-saved');
    Route::get('api/customers/{customer}/cards', [PaymentGatewayController::class, 'listCards'])->name('customers.cards');
    Route::delete('api/customers/{customer}/cards/{pmId}', [PaymentGatewayController::class, 'removeCard'])->name('customers.cards.remove');

    // SMS Routes
    Route::prefix('settings/sms')->name('settings.sms.')->group(function () {
        Route::get('/', [SmsController::class, 'settings'])->name('index');
        Route::post('/', [SmsController::class, 'updateSettings'])->name('update');
        Route::post('/test', [SmsController::class, 'test'])->name('test');
        Route::get('/templates', [SmsController::class, 'templates'])->name('templates');
        Route::patch('/templates/{template}', [SmsController::class, 'updateTemplate'])->name('templates.update');
    });
    Route::get('reports/sms-logs', [SmsController::class, 'logs'])->name('reports.sms-logs');

    // Email Logs
    Route::get('reports/email-logs', [EmailLogController::class, 'index'])->name('reports.email-logs');
    Route::post('api/invoices/{invoice}/send-email', [EmailLogController::class, 'sendInvoiceEmail'])->name('invoices.send-email');
    Route::post('api/sales/{sale}/send-email', [EmailLogController::class, 'sendSaleEmail'])->name('sales.send-email');

    // Phase 4 — Warranties & Combos
    Route::resource('warranties', WarrantyController::class);
    Route::resource('combos', ComboProductController::class);

    // Phase 4 — CSV Import/Export
    Route::post('customers/import', [CustomerController::class, 'import'])->name('customers.import');
    Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::get('customers/sample-csv', [CustomerController::class, 'downloadSample'])->name('customers.sample');
    Route::post('suppliers/import', [SupplierController::class, 'import'])->name('suppliers.import');
    Route::get('suppliers/export', [SupplierController::class, 'export'])->name('suppliers.export');
    Route::get('suppliers/sample-csv', [SupplierController::class, 'downloadSample'])->name('suppliers.sample');

    // Phase 4 — Opening Stock Import
    Route::get('stock/opening-import', [StockController::class, 'openingImport'])->name('stock.opening-import');
    Route::post('stock/opening-import', [StockController::class, 'storeOpeningImport'])->name('stock.opening-import.store');
    Route::get('stock/opening-import/sample', [StockController::class, 'openingImportSample'])->name('stock.opening-import.sample');

    // Phase 5 — HRM additions
    Route::resource('companies', CompanyController::class);
    Route::resource('shifts', OfficeShiftController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('holidays', HolidayController::class)->only(['index', 'store', 'update', 'destroy']);

    // Phase 5 — Finance: Deposits & Transfers
    Route::resource('deposit-categories', DepositCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('deposits', DepositController::class);
    Route::resource('money-transfers', MoneyTransferController::class)->only(['index', 'store', 'show', 'destroy']);

    // Phase 5 — Projects & Tasks
    Route::resource('projects', ProjectController::class);
    Route::post('projects/{project}/tasks', [ProjectController::class, 'storeTask'])->name('projects.tasks.store');
    Route::patch('projects/{project}/tasks/{task}', [ProjectController::class, 'updateTask'])->name('projects.tasks.update');
    Route::delete('projects/{project}/tasks/{task}', [ProjectController::class, 'destroyTask'])->name('projects.tasks.destroy');

    // Phase 5 — Shipments & Recurring Invoices
    Route::resource('shipments', ShipmentController::class);
    Route::resource('recurring-invoices', RecurringInvoiceController::class);

    // Phase 6 — System: Modules & Error Logs
    Route::get('system/modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::post('system/modules/{module}/toggle', [ModuleController::class, 'toggle'])->name('modules.toggle');
    Route::patch('system/modules/{module}', [ModuleController::class, 'update'])->name('modules.update');
    Route::get('system/error-logs', [ErrorLogController::class, 'index'])->name('error-logs.index');
    Route::delete('system/error-logs/{errorLog}', [ErrorLogController::class, 'destroy'])->name('error-logs.destroy');
    Route::post('system/error-logs/clear', [ErrorLogController::class, 'clear'])->name('error-logs.clear');
});
