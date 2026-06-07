@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<h2 class="text-2xl font-bold text-gray-800 mb-2 text-center">Forgot Password?</h2>
<p class="text-gray-600 text-sm mb-6 text-center">No worries, well send you reset instructions.</p>

@if(session('status'))
<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r">
    <p class="text-sm">{{ session('status') }}</p>
</div>
@endif

<form method="POST" action="{{ route('password.email') }}" data-validate>
    @csrf
    
    <!-- Email -->
    <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" 
               id="email" 
               name="email" 
               value="{{ old('email') }}"
               class="form-control @error('email') is-invalid @enderror" 
               placeholder="Enter your email"
               required 
               autofocus>
        @error('email')
            <span class="form-error">{{ $message }}</span>
        @enderror
        <span class="form-help">We'll send a password reset link to this email</span>
    </div>
    
    <!-- Submit Button -->
    <button type="submit" class="w-full btn btn-primary">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
        </svg>
        Send Reset Link
    </button>
</form>

<!-- Back to Login -->
<div class="mt-6 text-center">
    <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center justify-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Login
    </a>
</div>
@endsection
