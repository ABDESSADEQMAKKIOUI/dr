@extends('layouts.app')
@section('title', 'SMS Templates')
@php
$pageTitle  = 'SMS Templates';
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.settings'),'url'=>'#'],['label'=>'SMS Templates','url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg></div>
  <div><p class="pg-hero-title">SMS Templates</p><p class="pg-hero-sub">Customise the SMS message sent for each event trigger.</p></div>
</div>

@if(session('success'))<div class="flash-ok"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>{{ session('success') }}</div>@endif

@forelse($templates as $template)
<div class="mc">
  <div class="mc-head" style="justify-content:space-between">
    <div style="display:flex;align-items:center;gap:.75rem">
      <div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg></div>
      <div>
        <p class="mc-head-title">{{ $template->label }}</p>
        <p class="mc-head-sub">Event: <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">{{ $template->event }}</code></p>
      </div>
    </div>
    <span class="{{ $template->is_active ? 'badge-ok' : 'badge-gray' }}">{{ $template->is_active ? 'Active' : 'Inactive' }}</span>
  </div>
  <form method="POST" action="{{ route('settings.sms.templates.update', $template) }}" class="mc-body-lg space-y-4">
    @csrf @method('PATCH')
    <div class="fi-group">
      <label class="fi-label">Message Body</label>
      <textarea name="body" rows="3" class="fi font-mono text-xs" maxlength="1000">{{ old('body_'.$template->id, $template->body) }}</textarea>
      <p class="fi-hint">Variables: <code class="text-xs bg-gray-100 px-1 rounded">{{ $template->variables_hint }}</code></p>
    </div>
    <div class="flex items-center justify-between">
      <label class="ts"><input type="checkbox" name="is_active" value="1" {{ $template->is_active?'checked':'' }}><span class="ts-slider"></span></label>
      <span class="text-xs text-gray-400 ml-2">Enable this template</span>
      <button type="submit" class="btn-ac ml-auto" style="padding:.5rem 1.25rem;font-size:.8125rem">Save</button>
    </div>
  </form>
</div>
@empty
<div class="mc">
  <div class="mc-body-lg text-center py-8">
    <p class="text-gray-400 mb-2">No SMS templates found.</p>
    <a href="{{ route('settings.sms.index') }}" class="text-sm font-medium" style="color:{{ $accent }}">Check gateway settings →</a>
  </div>
</div>
@endforelse
</div>
@endsection
