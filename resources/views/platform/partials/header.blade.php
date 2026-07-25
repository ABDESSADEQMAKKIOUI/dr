@php
    $operator = auth()->guard('platform')->user();
@endphp
<header class="bg-white dark:bg-gray-900 border-b border-slate-200 dark:border-slate-700 shadow-sm fixed w-full top-0 z-50">
    <div class="flex items-center justify-between px-6 py-3">

        <!-- Mobile Menu Button & Brand -->
        <div class="flex items-center">
            <button id="sidebar-toggle" class="lg:hidden text-slate-600 dark:text-slate-300 hover:text-slate-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <a href="{{ route('platform.dashboard') }}" class="flex items-center">
                <div class="pf-brand mr-3">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                </div>
                <div class="hidden sm:block">
                    <div class="text-base font-bold text-slate-800 dark:text-slate-100 leading-tight">{{ config('app.name', 'SAFM') }}</div>
                    <div class="pf-meta">{{ __('app.platform_console') }}</div>
                </div>
            </a>
        </div>

        <!-- Right Side -->
        <div class="flex items-center space-x-4">

            <span class="pf-chip hidden md:flex">
                <span class="pf-dot" style="background:currentColor"></span>
                {{ config('tenancy.admin_domain') }}
            </span>

            <!-- Dark Mode Toggle -->
            <button id="dark-mode-toggle" onclick="toggleDarkMode()"
                    class="text-slate-500 dark:text-slate-400 hover:text-slate-800 p-2 rounded-lg hover:bg-slate-100 transition"
                    title="{{ __('app.dark_mode') }}">
                <svg id="icon-sun" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <svg id="icon-moon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            <!-- Operator Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                        class="flex items-center gap-2 text-slate-700 dark:text-slate-300 hover:text-slate-900 px-2 py-1.5 rounded-lg hover:bg-slate-100 transition">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-semibold text-sm"
                         style="background:var(--accent)">
                        {{ strtoupper(substr($operator->name ?? 'O', 0, 1)) }}
                    </div>
                    <span class="hidden lg:block text-sm font-medium">{{ $operator->name ?? '—' }}</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open"
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg py-1 z-50"
                     style="display: none;">

                    <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $operator->name ?? '—' }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ $operator->email ?? '' }}</p>
                        <span class="badge badge-secondary mt-2">{{ __('app.role_'.($operator->role ?? 'support')) }}</span>
                    </div>

                    @if($operator && $operator->last_login_at)
                    <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-700">
                        <p class="pf-meta">{{ __('app.last_login') }} : {{ \Illuminate\Support\Carbon::parse($operator->last_login_at)->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('platform.logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 gap-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            {{ __('app.logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
