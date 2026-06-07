@extends('layouts.app')
@section('title', __('app.language_settings'))
@php
$pageTitle  = __('app.language_settings');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.settings'),'url'=>'#'],['label'=>__('app.languages'),'url'=>'']];
$languages = $languages ?? [];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.language_settings') }}</p><p class="pg-hero-sub">Set the application language and text direction.</p></div>
</div>

@if(session('success'))<div class="flash-ok"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>{{ session('success') }}</div>@endif

<form method="POST" action="{{ route('settings.languages.update') }}">
@csrf

<div class="mc">
  <div class="mc-head">
    <div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253M3 12a8.959 8.959 0 0 1 .284-2.253"/></svg></div>
    <div><p class="mc-head-title">{{ __('app.default_language') }}</p><p class="mc-head-sub">Application display language and direction.</p></div>
  </div>
  <div class="mc-body-lg grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="fi-group">
      <label class="fi-label">{{ __('app.application_language') }} *</label>
      <select name="default_language" class="fi" required>
        <option value="en" {{ ($settings['default_language']??'en')=='en'?'selected':'' }}>English</option>
        <option value="fr" {{ ($settings['default_language']??'')=='fr'?'selected':'' }}>Français</option>
        <option value="ar" {{ ($settings['default_language']??'')=='ar'?'selected':'' }}>العربية</option>
        <option value="es" {{ ($settings['default_language']??'')=='es'?'selected':'' }}>Español</option>
      </select>
    </div>
    <div class="fi-group">
      <label class="fi-label">{{ __('app.text_direction') }}</label>
      <select name="text_direction" class="fi">
        <option value="ltr" {{ ($settings['text_direction']??'ltr')=='ltr'?'selected':'' }}>{{ __('app.ltr') }}</option>
        <option value="rtl" {{ ($settings['text_direction']??'')=='rtl'?'selected':'' }}>{{ __('app.rtl') }}</option>
      </select>
    </div>
  </div>
</div>

@if(count($languages))
<div class="mc">
  <div class="mc-head">
    <div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/></svg></div>
    <div><p class="mc-head-title">{{ __('app.available_languages') }}</p></div>
  </div>
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>{{ __('app.language') }}</th><th>{{ __('app.code') }}</th><th>Flag</th><th>Direction</th><th>{{ __('app.active') }}</th></tr></thead>
      <tbody>
      @php $activeLanguages = array_filter(explode(',', $settings['active_languages'] ?? '')); @endphp
      @foreach($languages as $code => $lang)
      <tr>
        <td class="font-medium">{{ $lang['name'] }}</td>
        <td><code class="text-xs bg-gray-100 px-2 py-0.5 rounded">{{ $code }}</code></td>
        <td class="text-xl">{{ $lang['flag'] ?? '' }}</td>
        <td><span class="badge-info">{{ $lang['rtl'] ? 'RTL' : 'LTR' }}</span></td>
        <td><label class="ts"><input type="checkbox" name="active_languages[]" value="{{ $code }}" {{ in_array($code, $activeLanguages) || empty($activeLanguages) ? 'checked' : '' }}><span class="ts-slider"></span></label></td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

<div class="mc">
  <div class="mc-head">
    <div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg></div>
    <div><p class="mc-head-title">{{ __('app.language_options') }}</p></div>
  </div>
  <div class="divide-y divide-gray-50">
    <div class="tr"><div><p class="tr-title">{{ __('app.allow_user_language') }}</p></div><label class="ts"><input type="checkbox" name="allow_user_language" value="1" {{ !empty($settings['allow_user_language'])?'checked':'' }}><span class="ts-slider"></span></label></div>
    <div class="tr"><div><p class="tr-title">{{ __('app.auto_detect_language') }}</p></div><label class="ts"><input type="checkbox" name="auto_detect_language" value="1" {{ !empty($settings['auto_detect_language'])?'checked':'' }}><span class="ts-slider"></span></label></div>
    <div class="tr"><div><p class="tr-title">{{ __('app.translate_emails') }}</p></div><label class="ts"><input type="checkbox" name="translate_emails" value="1" {{ !empty($settings['translate_emails'])?'checked':'' }}><span class="ts-slider"></span></label></div>
  </div>
</div>

<div class="flex justify-end"><button type="submit" class="btn-ac">{{ __('app.save_settings') }}</button></div>
</form>
</div>
@endsection
