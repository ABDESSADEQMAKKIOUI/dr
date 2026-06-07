@extends('layouts.app')
@section('title', 'Email SMTP Settings')
@php
$pageTitle  = 'Email SMTP Settings';
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.settings'),  'url' => '#'],
    ['label' => 'Email SMTP',        'url' => ''],
];
@endphp
@section('content')
@php
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp

<div class="max-w-4xl mx-auto space-y-6">

  {{-- Header --}}
  <div class="flex items-center gap-4 mb-2">
    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-xl" style="background:{{ $accent }}">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
    </div>
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Email SMTP Settings</h1>
      <p class="text-gray-500 text-sm">Configure the outgoing mail server used to send invoices and quotations.</p>
    </div>
  </div>

  @if(session('success'))
  <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium" style="background:#d1fae5;color:#065f46">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
    {{ session('success') }}
  </div>
  @endif
  @if(session('test_success'))
  <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium" style="background:#d1fae5;color:#065f46">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
    Test email sent successfully!
  </div>
  @endif
  @if(session('test_error'))
  <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium" style="background:#fee2e2;color:#991b1b">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
    Test failed: {{ session('test_error') }}
  </div>
  @endif

  <form method="POST" action="{{ route('settings.email-smtp.update') }}">
    @csrf

    {{-- Server Settings --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:{{ $accent }}1a">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 6 0m-6 0H3m16.5 0a3 3 0 0 0 3-3m-3 3a3 3 0 1 1-6 0m6 0h1.5m-7.5-3a3 3 0 0 1 3-3m-3 3H3m13.5-3H21M3 11.25h1.5m0 0a3 3 0 0 1 3-3m0 0a3 3 0 0 1 3 3m-6 0h6" /></svg>
        </div>
        <div>
          <h3 class="font-semibold text-gray-800">Server Configuration</h3>
          <p class="text-xs text-gray-400">SMTP host, port and encryption settings.</p>
        </div>
      </div>
      <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-5">

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">SMTP Host</label>
          <input type="text" name="smtp_host" value="{{ old('smtp_host', $settings['smtp_host'] ?? '') }}"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
            placeholder="smtp.gmail.com">
          @error('smtp_host')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">SMTP Port</label>
          <input type="number" name="smtp_port" value="{{ old('smtp_port', $settings['smtp_port'] ?? '587') }}"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
            placeholder="587">
          @error('smtp_port')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Encryption</label>
          <select name="smtp_encryption" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 bg-white">
            @foreach(['tls' => 'TLS (recommended)', 'ssl' => 'SSL', '' => 'None'] as $val => $label)
            <option value="{{ $val }}" {{ ($settings['smtp_encryption'] ?? 'tls') == $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>

      </div>
    </div>

    {{-- Authentication --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:{{ $accent }}1a">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
        </div>
        <div>
          <h3 class="font-semibold text-gray-800">Authentication</h3>
          <p class="text-xs text-gray-400">SMTP username and password (app password for Gmail).</p>
        </div>
      </div>
      <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-5">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Username / Email</label>
          <input type="text" name="smtp_username" value="{{ old('smtp_username', $settings['smtp_username'] ?? '') }}"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
            placeholder="you@example.com">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input type="password" name="smtp_password" value="{{ old('smtp_password', $settings['smtp_password'] ?? '') }}"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
            placeholder="••••••••••••">
          <p class="text-xs text-gray-400 mt-1">Leave blank to keep current password.</p>
        </div>

      </div>
    </div>

    {{-- From Address --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:{{ $accent }}1a">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
        </div>
        <div>
          <h3 class="font-semibold text-gray-800">Sender Identity</h3>
          <p class="text-xs text-gray-400">The name and address that appears in the "From" field.</p>
        </div>
      </div>
      <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-5">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">From Name</label>
          <input type="text" name="smtp_from_name" value="{{ old('smtp_from_name', $settings['smtp_from_name'] ?? ($settings['company_name'] ?? '')) }}"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
            placeholder="Your Company Name">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">From Address</label>
          <input type="email" name="smtp_from_address" value="{{ old('smtp_from_address', $settings['smtp_from_address'] ?? '') }}"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
            placeholder="noreply@example.com">
        </div>

      </div>
    </div>

    <div class="flex items-center justify-between">
      {{-- Test Send --}}
      <button type="button" onclick="sendTest()" class="px-5 py-2.5 rounded-xl border-2 font-semibold text-sm transition hover:opacity-80" style="border-color:{{ $accent }};color:{{ $accent }}">
        Send Test Email
      </button>
      <button type="submit" class="px-6 py-2.5 rounded-xl text-white font-semibold text-sm shadow-sm hover:opacity-90 transition" style="background:{{ $accent }}">
        Save Settings
      </button>
    </div>
  </form>
</div>

@push('scripts')
<script>
function sendTest() {
  const email = prompt('Enter email address to send test to:', '{{ auth()->user()->email ?? "" }}');
  if (!email) return;
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '{{ route("settings.email-smtp.test") }}';
  const csrf = document.createElement('input');
  csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
  const emailInput = document.createElement('input');
  emailInput.type = 'hidden'; emailInput.name = 'email'; emailInput.value = email;
  form.appendChild(csrf); form.appendChild(emailInput);
  document.body.appendChild(form); form.submit();
}
</script>
@endpush
@endsection
