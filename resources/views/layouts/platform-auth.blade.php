<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('app.login')) - {{ __('app.platform_console') }}</title>

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    {{-- Deliberately NOT the ERP gradient: an operator must never confuse the two login screens. --}}
    <style>
    :root{--accent:#0d9488}
    .pf-auth{background:radial-gradient(1200px 600px at 50% -10%,#134e4a 0%,#0f172a 55%,#020617 100%)}
    .pf-auth-card{background:#fff;border-radius:1rem;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,.45)}
    .pf-auth-head{background:linear-gradient(135deg,#0f172a 0%,#134e4a 100%);padding:1.75rem 1.5rem;text-align:center}
    .pf-auth-mark{width:4rem;height:4rem;border-radius:1rem;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;margin:0 auto .75rem}
    .pf-auth-mark svg{width:2rem;height:2rem;color:#5eead4}
    .pf-auth-title{font-size:1.375rem;font-weight:800;color:#fff;margin:0}
    .pf-auth-sub{font-size:.8125rem;color:#99f6e4;margin:.25rem 0 0}
    .pf-auth-foot{text-align:center;margin-top:1.5rem;font-size:.8125rem;color:#94a3b8}
    </style>

    @stack('styles')
</head>
<body class="pf-auth min-h-screen flex items-center justify-center font-sans antialiased p-4">
    <div class="w-full max-w-md">

        @if(session('error'))
        <div class="alert alert-error mb-4">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if(session('status'))
        <div class="alert alert-info mb-4">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
        @endif

        <div class="pf-auth-card">
            <div class="pf-auth-head">
                <div class="pf-auth-mark">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                </div>
                <p class="pf-auth-title">{{ __('app.platform_console') }}</p>
                <p class="pf-auth-sub">{{ config('app.name', 'SAFM') }} — {{ __('app.operators') }}</p>
            </div>

            <div class="p-8">
                @yield('content')
            </div>
        </div>

        <div class="pf-auth-foot">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'SAFM') }}. {{ __('app.restricted_access') }}</p>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
