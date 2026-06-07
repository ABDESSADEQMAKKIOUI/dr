@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Reset Password</h2>

<form method="POST" action="{{ route('password.update') }}" data-validate">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    
    <!-- Email -->
    <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" 
               id="email" 
               name="email" 
               value="{{ $email ?? old('email') }}"
               class="form-control @error('email') is-invalid @enderror" 
               placeholder="Enter your email"
               required 
               autofocus>
        @error('email')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>
    
    <!-- Password -->
    <div class="form-group">
        <label for="password" class="form-label">New Password</label>
        <input type="password" 
               id="password" 
               name="password" 
               class="form-control @error('password') is-invalid @enderror" 
               placeholder="Create a new password"
               required>
        @error('password')
            <span class="form-error">{{ $message }}</span>
        @enderror
        <span class="form-help">Minimum 8 characters</span>
    </div>
    
    <!-- Confirm Password -->
    <div class="form-group">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input type="password" 
               id="password_confirmation" 
               name="password_confirmation" 
               class="form-control" 
               placeholder="Re-enter your new password"
               required>
    </div>
    
    <!-- Submit Button -->
    <button type="submit" class="w-full btn btn-primary">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
        </svg>
        Reset Password
    </button>
</form>
@endsection
