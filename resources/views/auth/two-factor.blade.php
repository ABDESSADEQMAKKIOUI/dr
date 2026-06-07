@extends('layouts.auth')
@section('title', 'Two-Factor Verification')
@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-lg rounded-2xl p-8">

            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Two-Factor Verification</h2>
                <p class="text-sm text-gray-500 mt-2">
                    Enter the 6-digit code from your authenticator app, or one of your recovery codes.
                </p>
            </div>

            @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-700">{{ $errors->first() }}</p>
            </div>
            @endif

            <form action="{{ route('2fa.verify') }}" method="POST">
                @csrf
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Verification Code</label>
                    <input type="text" name="code" inputmode="numeric" maxlength="20"
                        placeholder="000000 or XXXXXX-XXXXXX"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-center text-xl tracking-widest font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        autofocus autocomplete="one-time-code">
                </div>
                <button type="submit"
                    class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition duration-200">
                    Verify &amp; Continue
                </button>
            </form>

            <div class="mt-6 text-center">
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 underline">
                        Sign out and use a different account
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
