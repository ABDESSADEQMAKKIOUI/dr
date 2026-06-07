@extends('layouts.auth')

@section('title', __('app.register'))

@section('content')
<h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">{{ __('app.create_account') }}</h2>

<form method="POST" action="{{ route('register') }}" data-validate>
    @csrf
    
    <!-- Name -->
    <div class="form-group">
        <label for="name" class="form-label">{{ __('app.full_name') }}</label>
        <input type="text" 
               id="name" 
               name="name" 
               value="{{ old('name') }}"
               class="form-control @error('name') is-invalid @enderror" 
               placeholder="{{ __('app.enter_full_name') }}"
               required 
               autofocus>
        @error('name')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>
    
    <!-- Email -->
    <div class="form-group">
        <label for="email" class="form-label">{{ __('app.email') }}</label>
        <input type="email" 
               id="email" 
               name="email" 
               value="{{ old('email') }}"
               class="form-control @error('email') is-invalid @enderror" 
               placeholder="{{ __('app.enter_email') }}"
               required>
        @error('email')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>
    
    <!-- Username -->
    <div class="form-group">
        <label for="username" class="form-label">{{ __('app.username') }}</label>
        <input type="text" 
               id="username" 
               name="username" 
               value="{{ old('username') }}"
               class="form-control @error('username') is-invalid @enderror" 
               placeholder="{{ __('app.choose_username') }}"
               required>
        @error('username')
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
               placeholder="{{ __('app.create_strong_password') }}"
               required>
        @error('password')
            <span class="form-error">{{ $message }}</span>
        @enderror
        <span class="form-help">{{ __('app.min_8_characters') }}</span>
    </div>
    
    <!-- Confirm Password -->
    <div class="form-group">
        <label for="password_confirmation" class="form-label">{{ __('app.confirm_password') }}</label>
        <input type="password" 
               id="password_confirmation" 
               name="password_confirmation" 
               class="form-control" 
               placeholder="{{ __('app.confirm_password_placeholder') }}"
               required>
    </div>
    
    <!-- Terms & Conditions -->
    <div class="flex items-start mb-6">
        <input type="checkbox" 
               id="terms" 
               name="terms" 
               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-1"
               required>
        <label for="terms" class="ml-2 text-sm text-gray-700">
            {{ __('app.agree_to_terms') }} <a href="#" class="text-blue-600 hover:text-blue-800">{{ __('app.terms_and_conditions') }}</a>
        </label>
    </div>
    
    <!-- Submit Button -->
    <button type="submit" class="w-full btn btn-primary">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
        </svg>
        {{ __('app.create_account') }}
    </button>
</form>

<!-- Login Link -->
<div class="mt-6 text-center">
    <p class="text-sm text-gray-600">
        {{ __('app.already_have_account') }} 
        <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-medium">
            {{ __('app.sign_in') }}
        </a>
    </p>
</div>
@endsection
