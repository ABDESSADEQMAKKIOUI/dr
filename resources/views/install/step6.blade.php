@extends('install.layout')
@section('title', 'Installation Complete')
@section('content')
<div class="text-center py-6">
    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Installation Complete!</h2>
    <p class="text-gray-500 mb-8">Your application has been successfully installed and configured. You can now log in with the admin account you created.</p>

    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800 mb-8">
        <strong>Security tip:</strong> Delete or restrict access to the <code>/install</code> route after logging in for the first time.
    </div>

    <a href="{{ route('login') }}" class="inline-block py-3 px-10 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition text-lg">
        Go to Login →
    </a>
</div>
@endsection
