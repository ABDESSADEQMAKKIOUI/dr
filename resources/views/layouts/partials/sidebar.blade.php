@php
function sideLink(string $route, bool $active): string {
    return 'block px-4 py-2 rounded text-sm ' . ($active ? 'text-white bg-gray-700' : 'text-gray-400 hover:text-white hover:bg-gray-800');
}
@endphp

<aside id="sidebar" class="fixed left-0 top-16 h-full w-64 bg-gray-900 text-gray-100 overflow-y-auto transition-transform duration-300 transform -translate-x-full lg:translate-x-0 z-40">
    <nav class="p-4 space-y-1">

        {{-- Dashboard --}}
        @can('dashboard.view')
        <a href="{{ route('dashboard') }}"
           class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            {{ __('app.dashboard') }}
        </a>
        @endcan

        {{-- Products --}}
        @can('products.view')
        <div x-data="{ open: {{ request()->routeIs('products.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full px-4 py-3 rounded-lg {{ request()->routeIs('products.*') ? 'text-white' : 'text-gray-300' }} hover:bg-gray-800">
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    {{ __('app.products') }}
                </span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="pl-12 space-y-1 mt-1">
                <a href="{{ route('products.index') }}"
                   class="{{ sideLink('', request()->routeIs('products.index') || request()->routeIs('products.show') || request()->routeIs('products.create') || request()->routeIs('products.edit')) }}">
                    {{ __('app.all') }} {{ __('app.products') }}
                </a>
                <a href="{{ route('products.categories.index') }}"
                   class="{{ sideLink('', request()->routeIs('products.categories.*')) }}">
                    {{ __('app.categories') }}
                </a>
                <a href="{{ route('products.brands.index') }}"
                   class="{{ sideLink('', request()->routeIs('products.brands.*')) }}">
                    {{ __('app.brands') }}
                </a>
                <a href="{{ route('products.units.index') }}"
                   class="{{ sideLink('', request()->routeIs('products.units.*')) }}">
                    {{ __('app.units') }}
                </a>
            </div>
        </div>
        @endcan

        {{-- Stock --}}
        @can('stock.view')
        <div x-data="{ open: {{ request()->routeIs('stock.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full px-4 py-3 rounded-lg {{ request()->routeIs('stock.*') ? 'text-white' : 'text-gray-300' }} hover:bg-gray-800">
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                    {{ __('app.stock') }}
                </span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="pl-12 space-y-1 mt-1">
                <a href="{{ route('stock.warehouses.index') }}"
                   class="{{ sideLink('', request()->routeIs('stock.warehouses.*')) }}">
                    {{ __('app.warehouses') }}
                </a>
                @can('stock.adjust')
                <a href="{{ route('stock.adjustments.index') }}"
                   class="{{ sideLink('', request()->routeIs('stock.adjustments.*')) }}">
                    {{ __('app.adjustments') }}
                </a>
                @endcan
                @can('stock.transfer')
                <a href="{{ route('stock.transfers.index') }}"
                   class="{{ sideLink('', request()->routeIs('stock.transfers.*')) }}">
                    {{ __('app.transfers') }}
                </a>
                @endcan
                <a href="{{ route('stock.inventory.index') }}"
                   class="{{ sideLink('', request()->routeIs('stock.inventory.index')) }}">
                    {{ __('app.inventory') }}
                </a>
                <a href="{{ route('stock.inventory.counts') }}"
                   class="{{ sideLink('', request()->routeIs('stock.inventory.counts') || request()->routeIs('stock.inventory.count*')) }}">
                    {{ __('app.stock_counts') ?? 'Stock Counts' }}
                </a>
                <a href="{{ route('stock.alerts.index') }}"
                   class="{{ sideLink('', request()->routeIs('stock.alerts.*')) }}">
                    {{ __('app.alerts') }}
                </a>
            </div>
        </div>
        @endcan

        {{-- Purchases --}}
        @can('purchases.view')
        <div x-data="{ open: {{ request()->routeIs('purchases.*') || request()->routeIs('suppliers.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full px-4 py-3 rounded-lg {{ request()->routeIs('purchases.*') || request()->routeIs('suppliers.*') ? 'text-white' : 'text-gray-300' }} hover:bg-gray-800">
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    {{ __('app.purchases') }}
                </span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="pl-12 space-y-1 mt-1">
                <a href="{{ route('purchases.index') }}"
                   class="{{ sideLink('', request()->routeIs('purchases.index') || request()->routeIs('purchases.show') || request()->routeIs('purchases.create') || request()->routeIs('purchases.edit')) }}">
                    {{ __('app.purchase_orders') }}
                </a>
                @can('suppliers.view')
                <a href="{{ route('suppliers.index') }}"
                   class="{{ sideLink('', request()->routeIs('suppliers.index') || request()->routeIs('suppliers.show') || request()->routeIs('suppliers.create') || request()->routeIs('suppliers.edit')) }}">
                    {{ __('app.suppliers') }}
                </a>
                <a href="{{ route('suppliers.due') }}"
                   class="{{ sideLink('', request()->routeIs('suppliers.due')) }}">
                    {{ __('app.supplier_due') }}
                </a>
                @endcan
                <a href="{{ route('purchases.returns.index') }}"
                   class="{{ sideLink('', request()->routeIs('purchases.returns.*')) }}">
                    {{ __('app.returns') }}
                </a>
            </div>
        </div>
        @endcan

        {{-- Sales --}}
        @can('sales.view')
        <div x-data="{ open: {{ request()->routeIs('sales.*') || request()->routeIs('customers.*') || request()->routeIs('quotations.*') || request()->routeIs('invoices.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full px-4 py-3 rounded-lg {{ request()->routeIs('sales.*') || request()->routeIs('customers.*') || request()->routeIs('quotations.*') || request()->routeIs('invoices.*') ? 'text-white' : 'text-gray-300' }} hover:bg-gray-800">
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('app.sales') }}
                </span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="pl-12 space-y-1 mt-1">
                <a href="{{ route('sales.index') }}"
                   class="{{ sideLink('', request()->routeIs('sales.index') || request()->routeIs('sales.show') || request()->routeIs('sales.create') || request()->routeIs('sales.edit')) }}">
                    {{ __('app.sales_orders') }}
                </a>
                @can('sales.pos')
                <a href="{{ route('sales.pos') }}"
                   class="{{ sideLink('', request()->routeIs('sales.pos')) }}">
                    {{ __('app.pos') }}
                </a>
                @endcan
                @can('customers.view')
                <a href="{{ route('customers.index') }}"
                   class="{{ sideLink('', request()->routeIs('customers.index') || request()->routeIs('customers.show') || request()->routeIs('customers.create') || request()->routeIs('customers.edit')) }}">
                    {{ __('app.customers') }}
                </a>
                <a href="{{ route('customers.due') }}"
                   class="{{ sideLink('', request()->routeIs('customers.due')) }}">
                    {{ __('app.customer_due') }}
                </a>
                @endcan
                @can('quotations.view')
                <a href="{{ route('quotations.index') }}"
                   class="{{ sideLink('', request()->routeIs('quotations.*')) }}">
                    {{ __('app.quotations') }}
                </a>
                @endcan
                @can('invoices.view')
                <a href="{{ route('invoices.index') }}"
                   class="{{ sideLink('', request()->routeIs('invoices.*')) }}">
                    {{ __('app.invoices') }}
                </a>
                @endcan
                <a href="{{ route('sales.returns.index') }}"
                   class="{{ sideLink('', request()->routeIs('sales.returns.*')) }}">
                    {{ __('app.returns') }}
                </a>
            </div>
        </div>
        @endcan

        {{-- Payments --}}
        @can('payments.view')
        <a href="{{ route('payments.index') }}"
           class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('payments.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            {{ __('app.payments') }}
        </a>
        @endcan

        {{-- Expenses --}}
        @can('expenses.view')
        <div x-data="{ open: {{ request()->routeIs('expenses.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full px-4 py-3 rounded-lg {{ request()->routeIs('expenses.*') ? 'text-white' : 'text-gray-300' }} hover:bg-gray-800">
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    {{ __('app.expenses') }}
                </span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="pl-12 space-y-1 mt-1">
                <a href="{{ route('expenses.index') }}"
                   class="{{ sideLink('', request()->routeIs('expenses.index') || request()->routeIs('expenses.show') || request()->routeIs('expenses.create') || request()->routeIs('expenses.edit')) }}">
                    {{ __('app.all') }} {{ __('app.expenses') }}
                </a>
                <a href="{{ route('expenses.categories.index') }}"
                   class="{{ sideLink('', request()->routeIs('expenses.categories.*')) }}">
                    {{ __('app.categories') }}
                </a>
            </div>
        </div>
        @endcan

        {{-- Reports --}}
        @can('reports.view')
        <div x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full px-4 py-3 rounded-lg {{ request()->routeIs('reports.*') ? 'text-white' : 'text-gray-300' }} hover:bg-gray-800">
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    {{ __('app.reports') }}
                </span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="pl-12 space-y-1 mt-1">
                <a href="{{ route('reports.sales') }}"              class="{{ sideLink('', request()->routeIs('reports.sales')) }}">{{ __('app.sales') }} {{ __('app.reports') }}</a>
                <a href="{{ route('reports.purchases') }}"          class="{{ sideLink('', request()->routeIs('reports.purchases')) }}">{{ __('app.purchases') }} {{ __('app.reports') }}</a>
                <a href="{{ route('reports.stock') }}"              class="{{ sideLink('', request()->routeIs('reports.stock')) }}">{{ __('app.stock') }} {{ __('app.reports') }}</a>
                <a href="{{ route('reports.financial') }}"          class="{{ sideLink('', request()->routeIs('reports.financial')) }}">{{ __('app.financial_reports') }}</a>
                <a href="{{ route('reports.customers') }}"          class="{{ sideLink('', request()->routeIs('reports.customers')) }}">{{ __('app.customers') }} {{ __('app.reports') }}</a>
                <a href="{{ route('reports.top-products') }}"       class="{{ sideLink('', request()->routeIs('reports.top-products')) }}">{{ __('app.top_products') }}</a>
                <a href="{{ route('reports.top-customers') }}"      class="{{ sideLink('', request()->routeIs('reports.top-customers')) }}">{{ __('app.top_customers') }}</a>
                <a href="{{ route('reports.sales-by-category') }}"  class="{{ sideLink('', request()->routeIs('reports.sales-by-category')) }}">{{ __('app.sales_by_category') }}</a>
                <a href="{{ route('reports.sales-by-brand') }}"     class="{{ sideLink('', request()->routeIs('reports.sales-by-brand')) }}">{{ __('app.sales_by_brand') }}</a>
                <a href="{{ route('reports.sales-by-warehouse') }}" class="{{ sideLink('', request()->routeIs('reports.sales-by-warehouse')) }}">{{ __('app.sales_by_warehouse') }}</a>
                <a href="{{ route('reports.sales-by-user') }}"      class="{{ sideLink('', request()->routeIs('reports.sales-by-user')) }}">{{ __('app.sales_by_user') }}</a>
                <a href="{{ route('reports.purchases-by-warehouse') }}" class="{{ sideLink('', request()->routeIs('reports.purchases-by-warehouse')) }}">{{ __('app.purchases_by_warehouse') }}</a>
                <a href="{{ route('reports.purchases-by-user') }}"  class="{{ sideLink('', request()->routeIs('reports.purchases-by-user')) }}">{{ __('app.purchases_by_user') }}</a>
                <a href="{{ route('reports.stock-by-warehouse') }}" class="{{ sideLink('', request()->routeIs('reports.stock-by-warehouse')) }}">{{ __('app.stock_by_warehouse') }}</a>
                <a href="{{ route('reports.inventory-valuation') }}" class="{{ sideLink('', request()->routeIs('reports.inventory-valuation')) }}">{{ __('app.inventory_valuation') }}</a>
                <a href="{{ route('reports.payment-transactions') }}" class="{{ sideLink('', request()->routeIs('reports.payment-transactions')) }}">{{ __('app.payment_transactions') }}</a>
                <a href="{{ route('reports.sms-logs') }}"           class="{{ sideLink('', request()->routeIs('reports.sms-logs')) }}">{{ __('app.sms_delivery_log') }}</a>
                <a href="{{ route('reports.email-logs') }}"         class="{{ sideLink('', request()->routeIs('reports.email-logs')) }}">{{ __('app.email_delivery_log') }}</a>
            </div>
        </div>
        @endcan

        {{-- Accounting --}}
        @can('accounting.view')
        <div x-data="{ open: {{ request()->routeIs('accounting.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full px-4 py-3 rounded-lg {{ request()->routeIs('accounting.*') ? 'text-white' : 'text-gray-300' }} hover:bg-gray-800">
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    {{ __('app.accounting') }}
                </span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="pl-12 space-y-1 mt-1">
                <a href="{{ route('accounting.accounts.index') }}"
                   class="{{ sideLink('', request()->routeIs('accounting.accounts.*')) }}">
                    {{ __('app.accounts') }}
                </a>
                @can('accounting.transactions')
                <a href="{{ route('accounting.transactions.index') }}"
                   class="{{ sideLink('', request()->routeIs('accounting.transactions.*')) }}">
                    {{ __('app.transactions') }}
                </a>
                @endcan
                @can('accounting.journals')
                <a href="{{ route('accounting.journals.index') }}"
                   class="{{ sideLink('', request()->routeIs('accounting.journals.*')) }}">
                    {{ __('app.journals') }}
                </a>
                @endcan
                <a href="{{ route('accounting.tax.index') }}"
                   class="{{ sideLink('', request()->routeIs('accounting.tax.*')) }}">
                    {{ __('app.tax_vat') }}
                </a>
            </div>
        </div>
        @endcan

        {{-- Employees --}}
        @can('employees.view')
        <div x-data="{ open: {{ request()->routeIs('employees.*') || request()->routeIs('departments.*') || request()->routeIs('designations.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full px-4 py-3 rounded-lg {{ request()->routeIs('employees.*') || request()->routeIs('departments.*') || request()->routeIs('designations.*') ? 'text-white' : 'text-gray-300' }} hover:bg-gray-800">
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    {{ __('app.employees') }}
                </span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="pl-12 space-y-1 mt-1">
                <a href="{{ route('employees.index') }}"
                   class="{{ sideLink('', request()->routeIs('employees.index') || request()->routeIs('employees.show') || request()->routeIs('employees.create') || request()->routeIs('employees.edit')) }}">
                    {{ __('app.all') }} {{ __('app.employees') }}
                </a>
                <a href="{{ route('departments.index') }}"
                   class="{{ sideLink('', request()->routeIs('departments.*')) }}">
                    {{ __('app.departments') ?? 'Departments' }}
                </a>
                <a href="{{ route('designations.index') }}"
                   class="{{ sideLink('', request()->routeIs('designations.*')) }}">
                    {{ __('app.designations') ?? 'Designations' }}
                </a>
                <a href="{{ route('employees.attendance.index') }}"
                   class="{{ sideLink('', request()->routeIs('employees.attendance.*')) }}">
                    {{ __('app.attendance') ?? 'Attendance' }}
                </a>
                <a href="{{ route('employees.payroll.index') }}"
                   class="{{ sideLink('', request()->routeIs('employees.payroll.*')) }}">
                    {{ __('app.payroll') ?? 'Payroll' }}
                </a>
                <a href="{{ route('employees.leaves.index') }}"
                   class="{{ sideLink('', request()->routeIs('employees.leaves.*')) }}">
                    {{ __('app.leaves') ?? 'Leaves' }}
                </a>
            </div>
        </div>
        @endcan

        {{-- CRM --}}
        @can('crm.leads')
        <div x-data="{ open: {{ request()->routeIs('crm.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full px-4 py-3 rounded-lg {{ request()->routeIs('crm.*') ? 'text-white' : 'text-gray-300' }} hover:bg-gray-800">
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ __('app.crm') }}
                </span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="pl-12 space-y-1 mt-1">
                <a href="{{ route('crm.leads.index') }}"
                   class="{{ sideLink('', request()->routeIs('crm.leads.*')) }}">
                    {{ __('app.leads') }}
                </a>
                @can('crm.opportunities')
                <a href="{{ route('crm.opportunities.index') }}"
                   class="{{ sideLink('', request()->routeIs('crm.opportunities.*')) }}">
                    {{ __('app.opportunities') }}
                </a>
                @endcan
                @can('crm.activities')
                <a href="{{ route('crm.activities.index') }}"
                   class="{{ sideLink('', request()->routeIs('crm.activities.*')) }}">
                    {{ __('app.activities') }}
                </a>
                @endcan
            </div>
        </div>
        @endcan

        {{-- Users & Roles --}}
        @can('users.view')
        <div x-data="{ open: {{ request()->routeIs('users.*') || request()->routeIs('roles.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full px-4 py-3 rounded-lg {{ request()->routeIs('users.*') || request()->routeIs('roles.*') ? 'text-white' : 'text-gray-300' }} hover:bg-gray-800">
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    {{ __('app.users') }}
                </span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="pl-12 space-y-1 mt-1">
                <a href="{{ route('users.index') }}"
                   class="{{ sideLink('', request()->routeIs('users.index') || request()->routeIs('users.show') || request()->routeIs('users.create') || request()->routeIs('users.edit')) }}">
                    {{ __('app.all') }} {{ __('app.users') }}
                </a>
                @can('roles.view')
                <a href="{{ route('roles.index') }}"
                   class="{{ sideLink('', request()->routeIs('roles.*')) }}">
                    {{ __('app.roles_permissions') }}
                </a>
                @endcan
            </div>
        </div>
        @endcan

        {{-- Settings --}}
        @can('settings.view')
        <div x-data="{ open: {{ request()->routeIs('settings.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full px-4 py-3 rounded-lg {{ request()->routeIs('settings.*') ? 'text-white' : 'text-gray-300' }} hover:bg-gray-800">
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ __('app.settings') }}
                </span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="pl-12 space-y-1 mt-1">
                <a href="{{ route('settings.general') }}"        class="{{ sideLink('', request()->routeIs('settings.general')) }}">{{ __('app.general') }}</a>
                <a href="{{ route('settings.invoice') }}"        class="{{ sideLink('', request()->routeIs('settings.invoice')) }}">{{ __('app.invoice_settings') }}</a>
                <a href="{{ route('settings.tax') }}"            class="{{ sideLink('', request()->routeIs('settings.tax')) }}">{{ __('app.tax_settings') }}</a>
                <a href="{{ route('settings.notifications') }}"  class="{{ sideLink('', request()->routeIs('settings.notifications')) }}">{{ __('app.notifications') }}</a>
                <a href="{{ route('settings.email-smtp') }}"     class="{{ sideLink('', request()->routeIs('settings.email-smtp')) }}">Email SMTP</a>
                <a href="{{ route('settings.pos') }}"            class="{{ sideLink('', request()->routeIs('settings.pos')) }}">{{ __('app.pos') }}</a>
                <a href="{{ route('settings.sms.index') }}"      class="{{ sideLink('', request()->routeIs('settings.sms.index')) }}">{{ __('app.sms_whatsapp') }}</a>
                <a href="{{ route('settings.sms.templates') }}"  class="{{ sideLink('', request()->routeIs('settings.sms.templates')) }}">{{ __('app.sms_templates') }}</a>
                <a href="{{ route('settings.backup') }}"         class="{{ sideLink('', request()->routeIs('settings.backup')) }}">{{ __('app.backup') }}</a>
            </div>
        </div>
        @endcan

        {{-- Documentation --}}
        <a href="{{ route('docs') }}"
           class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('docs') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            {{ __('app.documentation') ?? 'Documentation' }}
        </a>

        {{-- Tenants (SaaS) --}}
        @can('tenants.view')
        <a href="{{ route('tenants.index') }}"
           class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('tenants.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            {{ __('app.tenants') }}
        </a>
        @endcan

    </nav>
</aside>

<!-- Overlay for mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>
