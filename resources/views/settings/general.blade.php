@extends('layouts.app')
@section('title', __('app.general_settings'))
@php
$pageTitle  = __('app.general_settings');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.settings'),'url'=>'#'],['label'=>__('app.general'),'url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.general_settings') }}</p><p class="pg-hero-sub">Configure your application name, company profile, and regional preferences.</p></div>
</div>

@if(session('success'))<div class="flash-ok"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>{{ session('success') }}</div>@endif

<form method="POST" action="{{ route('settings.general.update') }}" enctype="multipart/form-data">
@csrf

{{-- Company Identity --}}
<div class="mc">
  <div class="mc-head">
    <div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/></svg></div>
    <div><p class="mc-head-title">Company Identity</p><p class="mc-head-sub">Your brand name, logo, and contact information.</p></div>
  </div>
  <div class="mc-body-lg grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="fi-group">
      <label class="fi-label">{{ __('app.app_name') }} *</label>
      <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name'] ?? 'ERP SaaS') }}" class="fi" required>
    </div>
    <div class="fi-group">
      <label class="fi-label">{{ __('app.company') }} *</label>
      <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? '') }}" class="fi" required>
    </div>
    <div class="fi-group">
      <label class="fi-label">{{ __('app.email') }}</label>
      <input type="email" name="company_email" value="{{ old('company_email', $settings['company_email'] ?? '') }}" class="fi">
    </div>
    <div class="fi-group">
      <label class="fi-label">{{ __('app.phone') }}</label>
      <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone'] ?? '') }}" class="fi">
    </div>
    <div class="fi-group md:col-span-2">
      <label class="fi-label">{{ __('app.address') }}</label>
      <textarea name="company_address" rows="2" class="fi">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
    </div>
    <div class="md:col-span-2">
      <label class="fi-label">{{ __('app.logo') }}</label>
      @if(!empty($settings['company_logo']))
      <div class="mb-3 flex items-center gap-3">
        <img src="{{ asset('storage/'.$settings['company_logo']) }}" alt="Logo" class="h-14 w-auto rounded-lg border border-gray-200 p-1">
        <span class="text-xs text-gray-400">Current logo</span>
      </div>
      @endif
      <input type="file" name="logo" id="logo-input" class="fi" accept="image/*" onchange="previewLogo(event)">
      <div id="logo-preview" class="mt-3 hidden">
        <p class="text-xs text-gray-400 mb-1">Preview:</p>
        <img id="logo-preview-img" src="" alt="Preview" class="h-14 w-auto rounded-lg border border-gray-200 p-1">
      </div>
    </div>
  </div>
</div>

{{-- Regional --}}
<div class="mc">
  <div class="mc-head">
    <div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253M3 12a8.959 8.959 0 0 1 .284-2.253"/></svg></div>
    <div><p class="mc-head-title">Regional Settings</p><p class="mc-head-sub">Timezone and date format preferences.</p></div>
  </div>
  <div class="mc-body-lg grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="fi-group">
      <label class="fi-label">{{ __('app.timezone') }}</label>
      <select name="timezone" class="fi">
        <option value="UTC" {{ ($settings['timezone']??'')=='UTC'?'selected':'' }}>UTC</option>
        <option value="Africa/Casablanca" {{ ($settings['timezone']??'')=='Africa/Casablanca'?'selected':'' }}>Africa/Casablanca</option>
        <option value="Europe/Paris" {{ ($settings['timezone']??'')=='Europe/Paris'?'selected':'' }}>Europe/Paris</option>
        <option value="America/New_York" {{ ($settings['timezone']??'')=='America/New_York'?'selected':'' }}>America/New_York</option>
      </select>
    </div>
    <div class="fi-group">
      <label class="fi-label">{{ __('app.date_format') }}</label>
      <select name="date_format" class="fi">
        <option value="Y-m-d" {{ ($settings['date_format']??'')=='Y-m-d'?'selected':'' }}>YYYY-MM-DD</option>
        <option value="d/m/Y" {{ ($settings['date_format']??'')=='d/m/Y'?'selected':'' }}>DD/MM/YYYY</option>
        <option value="m/d/Y" {{ ($settings['date_format']??'')=='m/d/Y'?'selected':'' }}>MM/DD/YYYY</option>
      </select>
    </div>
  </div>
</div>

<div class="flex justify-end"><button type="submit" class="btn-ac">{{ __('app.save') }}</button></div>
</form>
</div>

@push('scripts')
<script>
function previewLogo(e){const f=e.target.files[0];if(f){const r=new FileReader();r.onload=function(ev){document.getElementById('logo-preview').classList.remove('hidden');document.getElementById('logo-preview-img').src=ev.target.result};r.readAsDataURL(f)}}
</script>
@endpush
@endsection
