@extends('layouts.app')
@section('title', __('app.notification_settings'))
@php
$pageTitle  = __('app.notification_settings');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.settings'),  'url' => '#'],
    ['label' => __('app.notifications'), 'url' => ''],
];
@endphp
@section('content')
@php
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp

<style>
.toggle-switch{position:relative;display:inline-block;width:48px;height:26px}
.toggle-switch input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;cursor:pointer;inset:0;background:#cbd5e1;border-radius:26px;transition:.3s}
.toggle-slider:before{position:absolute;content:"";height:20px;width:20px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.3s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
input:checked+.toggle-slider{background:var(--accent)}
input:checked+.toggle-slider:before{transform:translateX(22px)}
</style>

<div class="max-w-5xl mx-auto space-y-6" style="--accent:{{ $accent }}">

  {{-- Header --}}
  <div class="flex items-center gap-4 mb-2">
    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-xl" style="background:{{ $accent }}">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
    </div>
    <div>
      <h1 class="text-2xl font-bold text-gray-900">{{ __('app.notification_settings') }}</h1>
      <p class="text-gray-500 text-sm">Manage when and how you receive alerts and emails.</p>
    </div>
  </div>

  @if(session('success'))
  <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium" style="background:#d1fae5;color:#065f46">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
    {{ session('success') }}
  </div>
  @endif

  <form method="POST" action="{{ route('settings.notifications.update') }}">
    @csrf

    {{-- Auto-Send Documents --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:{{ $accent }}1a">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
        </div>
        <div>
          <h3 class="font-semibold text-gray-800">Auto-Send to Clients</h3>
          <p class="text-xs text-gray-400">Automatically email documents to the client when created.</p>
        </div>
      </div>
      <div class="divide-y divide-gray-50">

        <div class="flex items-center justify-between px-6 py-4">
          <div>
            <p class="font-medium text-gray-700">Send Invoice on Creation</p>
            <p class="text-sm text-gray-400">Email the invoice PDF to the client immediately after it is created.</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="notify_invoice_created" value="1" {{ !empty($settings['notify_invoice_created']) && $settings['notify_invoice_created'] == '1' ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="flex items-center justify-between px-6 py-4">
          <div>
            <p class="font-medium text-gray-700">Send Quotation on Creation</p>
            <p class="text-sm text-gray-400">Email the quotation to the client immediately after it is created.</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="notify_quotation_created" value="1" {{ !empty($settings['notify_quotation_created']) && $settings['notify_quotation_created'] == '1' ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>

      </div>
    </div>

    {{-- Email Notifications --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:{{ $accent }}1a">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
        </div>
        <div>
          <h3 class="font-semibold text-gray-800">Email Notifications</h3>
          <p class="text-xs text-gray-400">Internal admin alerts sent to your team.</p>
        </div>
      </div>
      <div class="divide-y divide-gray-50">

        <div class="flex items-center justify-between px-6 py-4">
          <div>
            <p class="font-medium text-gray-700">{{ __('app.new_order_received') }}</p>
            <p class="text-sm text-gray-400">{{ __('app.get_notified_new_order') }}</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="notify_new_order" value="1" {{ !empty($settings['notify_new_order']) ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="flex items-center justify-between px-6 py-4">
          <div>
            <p class="font-medium text-gray-700">{{ __('app.low_stock_alert') }}</p>
            <p class="text-sm text-gray-400">{{ __('app.alert_low_stock') }}</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="notify_low_stock" value="1" {{ !empty($settings['notify_low_stock']) ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="flex items-center justify-between px-6 py-4">
          <div>
            <p class="font-medium text-gray-700">{{ __('app.new_customer_registration') }}</p>
            <p class="text-sm text-gray-400">{{ __('app.get_notified_new_customer') }}</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="notify_new_customer" value="1" {{ !empty($settings['notify_new_customer']) ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="flex items-center justify-between px-6 py-4">
          <div>
            <p class="font-medium text-gray-700">{{ __('app.payment_received') }}</p>
            <p class="text-sm text-gray-400">{{ __('app.notification_payment_received') }}</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="notify_payment" value="1" {{ !empty($settings['notify_payment']) ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="flex items-center justify-between px-6 py-4">
          <div>
            <p class="font-medium text-gray-700">{{ __('app.overdue_invoices') }}</p>
            <p class="text-sm text-gray-400">{{ __('app.alert_overdue_invoices') }}</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="notify_overdue" value="1" {{ !empty($settings['notify_overdue']) ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>

      </div>
    </div>

    {{-- System Notifications --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:{{ $accent }}1a">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3" /></svg>
        </div>
        <div>
          <h3 class="font-semibold text-gray-800">{{ __('app.system_notifications') }}</h3>
          <p class="text-xs text-gray-400">Technical and infrastructure alerts.</p>
        </div>
      </div>
      <div class="divide-y divide-gray-50">

        <div class="flex items-center justify-between px-6 py-4">
          <div>
            <p class="font-medium text-gray-700">{{ __('app.backup_completed') }}</p>
            <p class="text-sm text-gray-400">{{ __('app.notification_backup_complete') }}</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="notify_backup" value="1" {{ !empty($settings['notify_backup']) ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="flex items-center justify-between px-6 py-4">
          <div>
            <p class="font-medium text-gray-700">{{ __('app.system_updates') }}</p>
            <p class="text-sm text-gray-400">{{ __('app.get_notified_updates') }}</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="notify_updates" value="1" {{ !empty($settings['notify_updates']) ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="flex items-center justify-between px-6 py-4">
          <div>
            <p class="font-medium text-gray-700">{{ __('app.error_alerts') }}</p>
            <p class="text-sm text-gray-400">{{ __('app.critical_error_notifications') }}</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="notify_errors" value="1" {{ !empty($settings['notify_errors']) ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>

      </div>
    </div>

    {{-- Notification Recipients --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:{{ $accent }}1a">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
        </div>
        <div>
          <h3 class="font-semibold text-gray-800">{{ __('app.notification_recipients') }}</h3>
          <p class="text-xs text-gray-400">Admin email addresses that receive internal notifications.</p>
        </div>
      </div>
      <div class="px-6 py-5">
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.admin_email_addresses') }}</label>
        <textarea name="admin_emails" rows="3" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:border-transparent" style="--tw-ring-color:{{ $accent }};focus:ring-color:{{ $accent }}" placeholder="{{ __('app.enter_email_per_line') }}">{{ old('admin_emails', $settings['admin_emails'] ?? '') }}</textarea>
        <p class="text-xs text-gray-400 mt-1">{{ __('app.enter_email_per_line') }}</p>
      </div>
    </div>

    <div class="flex justify-end">
      <button type="submit" class="px-6 py-2.5 rounded-xl text-white font-semibold text-sm shadow-sm hover:opacity-90 transition" style="background:{{ $accent }}">
        {{ __('app.save_settings') }}
      </button>
    </div>
  </form>
</div>
@endsection
