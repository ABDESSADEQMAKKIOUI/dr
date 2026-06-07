@extends('layouts.app')
@section('title', 'Two-Factor Authentication')
@php
$pageTitle  = 'Security';
$breadcrumbs = [['label'=>'Dashboard','url'=>route('dashboard')],['label'=>'Settings','url'=>'#'],['label'=>'Security / 2FA','url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg></div>
  <div><p class="pg-hero-title">Security — Two-Factor Authentication</p><p class="pg-hero-sub">Add an extra layer of security to your account with 2FA.</p></div>
</div>

@if(session('success'))<div class="flash-ok"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>{{ session('success') }}</div>@endif

<div class="mc" style="max-width:640px">
  <div class="mc-head" style="justify-content:space-between">
    <div style="display:flex;align-items:center;gap:.75rem">
      <div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3m-3 4.5h3M3.75 6.75h.75m-.75 3.75h.75m-.75 3.75h.75"/></svg></div>
      <p class="mc-head-title">Two-Factor Authentication (2FA)</p>
    </div>
    @if($enabled)<span class="badge-ok">Enabled</span>@else<span class="badge-danger">Disabled</span>@endif
  </div>

  <div class="mc-body-lg">
    <p class="text-sm text-gray-500 mb-5 leading-relaxed">When enabled, you will be prompted for a 6-digit code from your authenticator app each time you log in.</p>

    @if(!$enabled)
    <div class="space-y-4">
      <p class="text-sm font-medium text-gray-700">1. Install an authenticator app (Google Authenticator, Authy, etc.)</p>
      <p class="text-sm font-medium text-gray-700">2. Scan the QR code with your app:</p>
      <div class="flex justify-center my-4">
        <div class="p-3 rounded-2xl border-2" style="border-color:{{ $accent }}1a">
          <img src="{{ $qrUrl }}" alt="QR Code" class="w-44 h-44">
        </div>
      </div>
      <div class="bg-gray-50 rounded-xl px-4 py-3 text-center">
        <p class="text-xs text-gray-400 mb-1">Can't scan? Enter this secret manually:</p>
        <code class="font-mono text-sm font-bold tracking-widest" style="color:{{ $accent }}">{{ $secret }}</code>
      </div>
      <p class="text-sm font-medium text-gray-700 mt-4">3. Enter the 6-digit code to confirm setup:</p>
      <form action="{{ route('2fa.enable') }}" method="POST" class="flex gap-3 items-end">
        @csrf
        <div class="flex-1">
          <input type="text" name="code" inputmode="numeric" maxlength="6" placeholder="000000"
            class="fi text-center text-xl tracking-widest font-mono @error('code') border-red-500 @enderror" autofocus autocomplete="one-time-code">
          @error('code')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn-ac">Enable 2FA</button>
      </form>
    </div>

    @else
    <div class="space-y-5">
      <div class="flex items-start gap-3 p-4 rounded-xl" style="background:#d1fae5">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#065f46"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
        <p class="text-sm font-medium" style="color:#065f46">2FA is active — you will be prompted for a code every login.</p>
      </div>

      @if(!empty($recCodes))
      <div>
        <p class="text-sm font-semibold text-gray-700 mb-1">Recovery Codes</p>
        <p class="text-xs text-gray-400 mb-3">Store these securely. Each can be used once if you lose your authenticator.</p>
        <div class="grid grid-cols-2 gap-2 font-mono text-sm p-4 bg-gray-50 border border-gray-100 rounded-xl">
          @foreach($recCodes as $code)<span class="text-gray-700">{{ $code }}</span>@endforeach
        </div>
      </div>
      @endif

      <form action="{{ route('2fa.disable') }}" method="POST" onsubmit="return confirm('Disable 2FA? Your account will be less secure.')">
        @csrf
        <div class="fi-group">
          <label class="fi-label">Confirm your password to disable 2FA:</label>
          <input type="password" name="password" class="fi @error('password') border-red-500 @enderror" placeholder="Current password">
          @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-500 text-white font-semibold text-sm hover:bg-red-600 transition">Disable 2FA</button>
      </form>
    </div>
    @endif
  </div>
</div>
</div>
@endsection
