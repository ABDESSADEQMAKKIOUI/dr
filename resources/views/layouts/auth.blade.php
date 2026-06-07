<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Login') - {{ config('app.name', 'ERP SaaS') }}</title>
    
    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 min-h-screen flex items-center justify-center font-sans antialiased">
    <div class="w-full max-w-md">
        <!-- Alerts -->
        @include('layouts.partials.alerts')
        
        <!-- Auth Card -->
        <div class="bg-white shadow-2xl rounded-lg overflow-hidden">
            <!-- Logo -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 text-center">
                <div class="flex justify-center mb-2">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-white">{{ config('app.name', 'ERP SaaS') }}</h1>
                <p class="text-blue-100 text-sm mt-1">Stock & Facturation Management</p>
            </div>
            
            <!-- Content -->
            <div class="p-8">
                @yield('content')
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-6 text-white text-sm">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
