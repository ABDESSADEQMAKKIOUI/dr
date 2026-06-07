@extends('layouts.auth')

@section('title', __('app.login'))

@section('content')
<h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">{{ __('app.welcome_back') }}</h2>

<form method="POST" action="{{ route('login') }}" data-validate>
    @csrf
    
    <!-- Email/Username -->
    <div class="form-group">
        <label for="email" class="form-label">{{ __('app.email') }}</label>
        <input type="text" 
               id="email" 
               name="email" 
               value="{{ old('email') }}"
               class="form-control @error('email') is-invalid @enderror" 
               placeholder="{{ __('app.enter_email') }}"
               required 
               autofocus>
        @error('email')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>
    
    <!-- Password -->
    <div class="form-group">
        <label for="password" class="form-label">{{ __('app.password') }}</label>
        <input type="password" 
               id="password" 
               name="password" 
               class="form-control @error('password') is-invalid @enderror" 
               placeholder="{{ __('app.enter_password') }}"
               required>
        @error('password')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>
    
    <!-- Remember Me & Forgot Password -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <input type="checkbox" 
                   id="remember" 
                   name="remember" 
                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
            <label for="remember" class="ml-2 text-sm text-gray-700">{{ __('app.remember_me') }}</label>
        </div>
        <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-800">
            {{ __('app.forgot_password') }}
        </a>
    </div>
    
    <!-- Submit Button -->
    <button type="submit" class="w-full btn btn-primary">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
        </svg>
        {{ __('app.login') }}
    </button>
</form>

<!-- Register Link -->
<div class="mt-6 text-center">
    <p class="text-sm text-gray-600">
        {{ __('app.no_account') }} 
        <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800 font-medium">
            {{ __('app.register') }}
        </a>
    </p>
</div>
@endsection
