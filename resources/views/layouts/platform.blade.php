<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('app.platform')) - {{ __('app.platform_console') }}</title>

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Shared inline design system (Layer B) --}}
    @include('layouts.partials.design-system')

    {{-- Operator console skin: re-points the single --accent variable and nothing else. --}}
    <style>
    :root{--accent:#0d9488}
    #sidebar{background:#0b1622}
    .sidebar-link.active{background:var(--accent);color:#fff}
    .pf-brand{width:2.5rem;height:2.5rem;border-radius:.75rem;display:flex;align-items:center;justify-content:center;background:var(--accent);flex-shrink:0}
    .pf-brand svg{width:1.5rem;height:1.5rem;color:#fff}
    .pf-chip{display:inline-flex;align-items:center;gap:.35rem;padding:.15rem .55rem;border-radius:20px;font-size:.6875rem;font-weight:700;letter-spacing:.03em;text-transform:uppercase;background:color-mix(in srgb,var(--accent) 14%,transparent);color:var(--accent)}
    .pf-dot{width:.5rem;height:.5rem;border-radius:50%;flex-shrink:0}
    .pf-meta{font-size:.75rem;color:#94a3b8}
    .pf-kv{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:.625rem 0;border-bottom:1px solid #f1f5f9}
    .pf-kv:last-child{border-bottom:none}
    .pf-kv-k{font-size:.8125rem;color:#64748b;flex-shrink:0}
    .pf-kv-v{font-size:.8125rem;font-weight:600;color:#1e293b;text-align:right;word-break:break-word}
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen">
        <!-- Header -->
        @include('platform.partials.header')

        <div class="flex">
            <!-- Sidebar -->
            @include('platform.partials.sidebar')

            <!-- Main Content -->
            <main class="flex-1 p-4 lg:p-6 lg:ml-64 mt-16">
                <div class="max-w-7xl mx-auto space-y-6">
                    <!-- Alerts -->
                    @include('platform.partials.alerts')

                    <!-- Page Header -->
                    @if(isset($pageTitle))
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-800">{{ $pageTitle }}</h1>
                        @if(isset($breadcrumbs))
                        <nav class="text-sm text-gray-600 mt-2">
                            @foreach($breadcrumbs as $breadcrumb)
                                @if(!$loop->last)
                                    <a href="{{ $breadcrumb['url'] }}" class="hover:text-blue-600">{{ $breadcrumb['label'] }}</a>
                                    <span class="mx-2">/</span>
                                @else
                                    <span>{{ $breadcrumb['label'] }}</span>
                                @endif
                            @endforeach
                        </nav>
                        @endif
                    </div>
                    @endif

                    <!-- Page Content -->
                    <div class="space-y-6">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-4 px-6 lg:ml-64 mt-8">
            <div class="flex flex-col md:flex-row justify-between items-center text-sm text-slate-500">
                <p>&copy; {{ date('Y') }} <strong class="text-slate-700">{{ config('app.name', 'SAFM') }}</strong> — {{ __('app.platform_console') }}</p>
                <p class="mt-2 md:mt-0">{{ __('app.signed_in_as') }} {{ auth()->guard('platform')->user()?->email }}</p>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}"></script>
    @stack('scripts')

    <!-- Dark Mode -->
    <script>
        (function () {
            const saved = localStorage.getItem('theme');
            if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.getElementById('html-root').classList.add('dark');
            }
        })();
        function toggleDarkMode() {
            const root = document.getElementById('html-root');
            const isDark = root.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            document.getElementById('icon-sun').classList.toggle('hidden', !isDark);
            document.getElementById('icon-moon').classList.toggle('hidden', isDark);
        }
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.getElementById('html-root').classList.contains('dark');
            document.getElementById('icon-sun').classList.toggle('hidden', !isDark);
            document.getElementById('icon-moon').classList.toggle('hidden', isDark);
        });
    </script>
</body>
</html>
