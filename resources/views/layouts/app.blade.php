<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'SAFM') }}</title>
    
    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @include('layouts.partials.design-system')
    @stack('styles')
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen">
        <!-- Header -->
        @include('layouts.partials.header')
        
        <div class="flex">
            <!-- Sidebar -->
            @include('layouts.partials.sidebar')
            
            <!-- Main Content -->
            <main class="flex-1 p-4 lg:p-6 lg:ml-64 mt-16">
                <div class="max-w-7xl mx-auto space-y-6">
                    <!-- Alerts -->
                    @include('layouts.partials.alerts')
                    
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
        @include('layouts.partials.footer')
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
    @if(Request::is('pos') || Request::is('pos/*'))
    <script src="{{ mix('js/zxing.js') }}"></script>
    @endif
</body>
</html>
