@extends('layouts.app')
@section('title', __('app.tax_settings'))
@php
$pageTitle  = __('app.tax_settings');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.settings'),'url'=>'#'],['label'=>__('app.tax'),'url'=>'']];
$taxRates = $taxRates ?? [];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.tax_settings') }}</p><p class="pg-hero-sub">Manage tax rates, calculation methods, and tax identifiers.</p></div>
</div>

@if(session('success'))<div class="flash-ok"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>{{ session('success') }}</div>@endif

<form method="POST" action="{{ route('settings.tax.update') }}">
@csrf

{{-- Default config --}}
<div class="mc">
  <div class="mc-head">
    <div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/></svg></div>
    <div><p class="mc-head-title">{{ __('app.default_tax_config') }}</p><p class="mc-head-sub">Default rate applied when no specific tax is selected.</p></div>
  </div>
  <div class="mc-body-lg grid grid-cols-1 md:grid-cols-3 gap-5">
    <div class="fi-group">
      <label class="fi-label">{{ __('app.default_tax_rate') }} (%)</label>
      <input type="number" step="0.01" name="default_tax_rate" value="{{ old('default_tax_rate', $settings['default_tax_rate'] ?? 20) }}" class="fi">
    </div>
    <div class="fi-group">
      <label class="fi-label">{{ __('app.tax_calculation_method') }}</label>
      <select name="tax_calculation_method" class="fi">
        <option value="inclusive" {{ ($settings['tax_calculation_method']??'exclusive')=='inclusive'?'selected':'' }}>{{ __('app.tax_inclusive') }}</option>
        <option value="exclusive" {{ ($settings['tax_calculation_method']??'exclusive')=='exclusive'?'selected':'' }}>{{ __('app.tax_exclusive') }}</option>
      </select>
    </div>
    <div class="fi-group">
      <label class="fi-label">{{ __('app.tax_number_label') }}</label>
      <input type="text" name="tax_number_label" value="{{ old('tax_number_label', $settings['tax_number_label'] ?? 'VAT Number') }}" class="fi">
    </div>
  </div>
</div>

{{-- Tax rates table --}}
<div class="mc">
  <div class="mc-head" style="justify-content:space-between">
    <div style="display:flex;align-items:center;gap:.75rem">
      <div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-1.5-3.75V8.25m0 0h-7.5"/></svg></div>
      <div><p class="mc-head-title">{{ __('app.tax_rates') }}</p><p class="mc-head-sub">Define specific tax rates for products or services.</p></div>
    </div>
    <button type="button" onclick="addTaxRate()" class="btn-ac" style="padding:.5rem 1rem;font-size:.8125rem">+ {{ __('app.add_tax_rate') }}</button>
  </div>
  <div class="mc-body-lg overflow-x-auto">
    <table class="mt"><thead><tr><th>{{ __('app.name') }}</th><th>{{ __('app.rate') }} (%)</th><th>{{ __('app.description') }}</th><th>{{ __('app.active') }}</th><th></th></tr></thead>
    <tbody id="tax-rates-tbody">
    @foreach($taxRates as $i => $rate)
    <tr>
      <td><input type="text" name="tax_rates[{{ $i }}][name]" value="{{ $rate['name'] }}" class="fi" style="min-width:120px" required></td>
      <td><input type="number" step="0.01" name="tax_rates[{{ $i }}][rate]" value="{{ $rate['rate'] }}" class="fi" style="width:90px" required></td>
      <td><input type="text" name="tax_rates[{{ $i }}][description]" value="{{ $rate['description']??'' }}" class="fi" style="min-width:160px"></td>
      <td><label class="ts"><input type="checkbox" name="tax_rates[{{ $i }}][active]" value="1" {{ $rate['active']?'checked':'' }}><span class="ts-slider"></span></label></td>
      <td><button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 text-lg font-bold">×</button></td>
    </tr>
    @endforeach
    </tbody></table>
  </div>
</div>

{{-- Options --}}
<div class="mc">
  <div class="mc-head">
    <div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg></div>
    <div><p class="mc-head-title">{{ __('app.tax_options') }}</p></div>
  </div>
  <div class="divide-y divide-gray-50">
    @foreach(['enable_tax_exemptions'=>__('app.enable_tax_exemptions'),'show_tax_breakdown'=>__('app.show_tax_breakdown'),'enable_reverse_charge'=>__('app.enable_reverse_charge')] as $k=>$label)
    <div class="tr">
      <div><p class="tr-title">{{ $label }}</p></div>
      <label class="ts"><input type="checkbox" name="{{ $k }}" value="1" {{ !empty($settings[$k])?'checked':'' }}><span class="ts-slider"></span></label>
    </div>
    @endforeach
  </div>
</div>

<div class="flex justify-end"><button type="submit" class="btn-ac">{{ __('app.save_settings') }}</button></div>
</form>
</div>

@push('scripts')
<script>
let taxRateCount={{ count($taxRates) }};
function addTaxRate(){const t=document.getElementById('tax-rates-tbody');t.insertAdjacentHTML('beforeend',`<tr><td><input type="text" name="tax_rates[${taxRateCount}][name]" class="fi" style="min-width:120px" required></td><td><input type="number" step="0.01" name="tax_rates[${taxRateCount}][rate]" class="fi" style="width:90px" required></td><td><input type="text" name="tax_rates[${taxRateCount}][description]" class="fi" style="min-width:160px"></td><td><label class="ts"><input type="checkbox" name="tax_rates[${taxRateCount}][active]" value="1" checked><span class="ts-slider"></span></label></td><td><button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 text-lg font-bold">×</button></td></tr>`);taxRateCount++;}
</script>
@endpush
@endsection
