{{--
    Platform operator console navigation.
    Deliberately independent of layouts/partials/sidebar.blade.php:
      - no @can (the Gate resolves the 'web' guard and would deny everything here)
      - no global sideLink() helper (that name is already taken by the ERP sidebar)
    Visibility is driven exclusively by @platformCan.
--}}
<aside id="sidebar" class="fixed left-0 top-16 h-full w-64 text-gray-100 overflow-y-auto transition-transform duration-300 transform -translate-x-full lg:translate-x-0 z-40">
    <nav class="p-4 space-y-1">

        <div class="sidebar-section">{{ __('app.general') }}</div>

        <a href="{{ route('platform.dashboard') }}"
           class="sidebar-link {{ request()->routeIs('platform.dashboard') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            {{ __('app.dashboard') }}
        </a>

        @platformCan('tenants.view')
        <a href="{{ route('platform.tenants.index') }}"
           class="sidebar-link {{ request()->routeIs('platform.tenants.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            {{ __('app.tenants') }}
        </a>
        @endplatformCan

        <div class="sidebar-section">{{ __('app.billing') }}</div>

        @platformCan('plans.view')
        <a href="{{ route('platform.plans.index') }}"
           class="sidebar-link {{ request()->routeIs('platform.plans.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            {{ __('app.plans') }}
        </a>
        @endplatformCan

        @platformCan('subscriptions.view')
        <a href="{{ route('platform.subscriptions.index') }}"
           class="sidebar-link {{ request()->routeIs('platform.subscriptions.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            {{ __('app.subscriptions') }}
        </a>
        @endplatformCan

        <div class="sidebar-section">{{ __('app.administration') }}</div>

        @platformCan('operators.manage')
        <a href="{{ route('platform.operators.index') }}"
           class="sidebar-link {{ request()->routeIs('platform.operators.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            {{ __('app.operators') }}
        </a>
        @endplatformCan

        @platformCan('audit.view')
        <a href="{{ route('platform.audit.index') }}"
           class="sidebar-link {{ request()->routeIs('platform.audit.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            {{ __('app.audit_log') }}
        </a>
        @endplatformCan

    </nav>
</aside>

<!-- Overlay for mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>
