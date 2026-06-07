@extends('layouts.app')
@section('title', __('app.currency_settings'))
@php
$pageTitle  = __('app.currency_settings');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.settings'),'url'=>'#'],['label'=>__('app.currencies'),'url'=>'']];
$currencies = $currencies ?? [];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.currency_settings') }}</p><p class="pg-hero-sub">Configure your base currency and exchange rates.</p></div>
</div>

@if(session('success'))<div class="flash-ok"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>{{ session('success') }}</div>@endif

<form method="POST" action="{{ route('settings.currencies.update') }}">
@csrf

<div class="mc">
  <div class="mc-head">
    <div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg></div>
    <div><p class="mc-head-title">{{ __('app.base_currency') }}</p></div>
  </div>
  <div class="mc-body-lg grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="fi-group">
      <label class="fi-label">{{ __('app.base_currency') }} *</label>
      <select name="base_currency" class="fi" required>
        <option value="MAD" {{ ($settings['base_currency']??'MAD')=='MAD'?'selected':'' }}>MAD — Moroccan Dirham</option>
        <option value="USD" {{ ($settings['base_currency']??'')=='USD'?'selected':'' }}>USD — US Dollar</option>
        <option value="EUR" {{ ($settings['base_currency']??'')=='EUR'?'selected':'' }}>EUR — Euro</option>
        <option value="GBP" {{ ($settings['base_currency']??'')=='GBP'?'selected':'' }}>GBP — British Pound</option>
      </select>
    </div>
    <div class="fi-group">
      <label class="fi-label">{{ __('app.currency_position') }}</label>
      <select name="currency_position" class="fi">
        <option value="before" {{ ($settings['currency_position']??'after')=='before'?'selected':'' }}>{{ __('app.before_amount') }}</option>
        <option value="after" {{ ($settings['currency_position']??'after')=='after'?'selected':'' }}>{{ __('app.after_amount') }}</option>
      </select>
    </div>
  </div>
</div>

<div class="mc">
  <div class="mc-head" style="justify-content:space-between">
    <div style="display:flex;align-items:center;gap:.75rem">
      <div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg></div>
      <div><p class="mc-head-title">{{ __('app.exchange_rates') }}</p><p class="mc-head-sub">Relative to {{ $settings['base_currency']??'MAD' }}</p></div>
    </div>
    <button type="button" onclick="addCurrency()" class="btn-ac" style="padding:.5rem 1rem;font-size:.8125rem">+ {{ __('app.add_currency') }}</button>
  </div>
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>{{ __('app.currency') }}</th><th>{{ __('app.code') }}</th><th>{{ __('app.symbol') }}</th><th>{{ __('app.exchange_rate') }}</th><th>{{ __('app.active') }}</th><th></th></tr></thead>
      <tbody id="currencies-tbody">
      @foreach($currencies as $i => $c)
      <tr>
        <td><input type="text" name="currencies[{{ $i }}][name]" value="{{ $c['name'] }}" class="fi" style="min-width:120px" required></td>
        <td><input type="text" name="currencies[{{ $i }}][code]" value="{{ $c['code'] }}" class="fi" style="width:70px" required maxlength="3"></td>
        <td><input type="text" name="currencies[{{ $i }}][symbol]" value="{{ $c['symbol'] }}" class="fi" style="width:60px" required></td>
        <td><input type="number" step="0.0001" name="currencies[{{ $i }}][rate]" value="{{ $c['rate'] }}" class="fi" style="width:100px" required></td>
        <td><label class="ts"><input type="checkbox" name="currencies[{{ $i }}][active]" value="1" {{ $c['active']?'checked':'' }}><span class="ts-slider"></span></label></td>
        <td><button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 text-lg font-bold">×</button></td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>

<div class="mc">
  <div class="mc-head"><div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg></div><div><p class="mc-head-title">{{ __('app.currency_options') }}</p></div></div>
  <div class="divide-y divide-gray-50">
    <div class="tr"><div><p class="tr-title">{{ __('app.auto_update_rates') }}</p></div><label class="ts"><input type="checkbox" name="auto_update_rates" value="1" {{ !empty($settings['auto_update_rates'])?'checked':'' }}><span class="ts-slider"></span></label></div>
    <div class="tr"><div><p class="tr-title">{{ __('app.show_currency_selector') }}</p></div><label class="ts"><input type="checkbox" name="show_currency_selector" value="1" {{ !empty($settings['show_currency_selector'])?'checked':'' }}><span class="ts-slider"></span></label></div>
  </div>
</div>

<div class="flex justify-end"><button type="submit" class="btn-ac">{{ __('app.save_settings') }}</button></div>
</form>
</div>

@push('scripts')
<script>
let currencyCount={{ count($currencies) }};
function addCurrency(){document.getElementById('currencies-tbody').insertAdjacentHTML('beforeend',`<tr><td><input type="text" name="currencies[${currencyCount}][name]" class="fi" style="min-width:120px" required></td><td><input type="text" name="currencies[${currencyCount}][code]" class="fi" style="width:70px" required maxlength="3"></td><td><input type="text" name="currencies[${currencyCount}][symbol]" class="fi" style="width:60px" required></td><td><input type="number" step="0.0001" name="currencies[${currencyCount}][rate]" class="fi" style="width:100px" required value="1.0000"></td><td><label class="ts"><input type="checkbox" name="currencies[${currencyCount}][active]" value="1" checked><span class="ts-slider"></span></label></td><td><button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 text-lg font-bold">×</button></td></tr>`);currencyCount++;}
</script>
@endpush
@endsection
