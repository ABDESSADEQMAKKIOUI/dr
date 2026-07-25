@extends('layouts.platform-auth')

@section('title', __('app.login'))

@section('content')
<h2 class="text-xl font-bold text-gray-800 mb-1 text-center">{{ __('app.operator_sign_in') }}</h2>
<p class="text-sm text-slate-500 mb-6 text-center">{{ __('app.operator_sign_in_hint') }}</p>

<form method="POST" action="{{ route('platform.login.attempt') }}">
    @csrf

    <div class="form-group">
        <label for="email" class="form-label">{{ __('app.email') }}</label>
        <input type="email"
               id="email"
               name="email"
               value="{{ old('email') }}"
               class="form-control @error('email') is-invalid @enderror"
               placeholder="operateur@exemple.ma"
               required
               autofocus
               autocomplete="username">
        @error('email')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group">
        <label for="password" class="form-label">{{ __('app.password') }}</label>
        <input type="password"
               id="password"
               name="password"
               class="form-control @error('password') is-invalid @enderror"
               placeholder="••••••••"
               required
               autocomplete="current-password">
        @error('password')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center mb-6">
        <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
        <label for="remember" class="ml-2 text-sm text-gray-700">{{ __('app.remember_me') }}</label>
    </div>

    <button type="submit" class="btn btn-primary btn-md w-full" style="background:var(--accent);border-color:var(--accent)">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
        </svg>
        {{ __('app.login') }}
    </button>
</form>

<p class="mt-6 text-center text-xs text-slate-400">{{ __('app.restricted_access_notice') }}</p>
@endsection
