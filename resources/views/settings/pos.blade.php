@extends('layouts.app')
@section('title', 'POS Settings')
@php
$pageTitle  = 'POS Settings';
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.settings'),'url'=>'#'],['label'=>'POS','url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg></div>
  <div><p class="pg-hero-title">POS Settings</p><p class="pg-hero-sub">Configure point-of-sale receipt, defaults, and behaviour.</p></div>
</div>

@if(session('success'))<div class="flash-ok"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>{{ session('success') }}</div>@endif

<form method="POST" action="{{ route('settings.pos.update') }}">
@csrf

{{-- Receipt --}}
<div class="mc">
  <div class="mc-head">
    <div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185ZM9.75 9h.008v.008H9.75V9Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008V13.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg></div>
    <div><p class="mc-head-title">Receipt</p><p class="mc-head-sub">Paper size and print options.</p></div>
  </div>
  <div class="mc-body-lg">
    <div class="fi-group" style="max-width:300px">
      <label class="fi-label">Receipt Paper Size</label>
      <select name="pos_receipt_size" class="fi">
        <option value="a4" {{ ($settings['pos_receipt_size']??'a4')=='a4'?'selected':'' }}>A4 (full page)</option>
        <option value="80mm" {{ ($settings['pos_receipt_size']??'')=='80mm'?'selected':'' }}>80mm Thermal</option>
        <option value="58mm" {{ ($settings['pos_receipt_size']??'')=='58mm'?'selected':'' }}>58mm Thermal</option>
      </select>
    </div>
  </div>
  <div class="divide-y divide-gray-50">
    <div class="tr">
      <div><p class="tr-title">Auto-print receipt after sale</p><p class="tr-sub">Automatically send to printer when a sale is completed.</p></div>
      <label class="ts"><input type="hidden" name="pos_auto_print" value="0"><input type="checkbox" name="pos_auto_print" value="1" {{ ($settings['pos_auto_print']??'false')==='true'?'checked':'' }}><span class="ts-slider"></span></label>
    </div>
    <div class="tr">
      <div><p class="tr-title">Show company logo on receipt</p></div>
      <label class="ts"><input type="hidden" name="pos_show_logo" value="0"><input type="checkbox" name="pos_show_logo" value="1" {{ ($settings['pos_show_logo']??'true')!=='false'?'checked':'' }}><span class="ts-slider"></span></label>
    </div>
    <div class="tr">
      <div><p class="tr-title">Show warehouse name on receipt</p></div>
      <label class="ts"><input type="hidden" name="pos_show_warehouse" value="0"><input type="checkbox" name="pos_show_warehouse" value="1" {{ ($settings['pos_show_warehouse']??'true')!=='false'?'checked':'' }}><span class="ts-slider"></span></label>
    </div>
  </div>
</div>

{{-- Defaults --}}
<div class="mc">
  <div class="mc-head">
    <div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/></svg></div>
    <div><p class="mc-head-title">Defaults</p><p class="mc-head-sub">Pre-selected values on the POS screen.</p></div>
  </div>
  <div class="mc-body-lg grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="fi-group">
      <label class="fi-label">Default Warehouse</label>
      <select name="pos_default_warehouse" class="fi">
        <option value="">— Select —</option>
        @foreach($warehouses??[] as $w)
        <option value="{{ $w->id }}" {{ ($settings['pos_default_warehouse']??'')==$w->id?'selected':'' }}>{{ $w->name }}</option>
        @endforeach
      </select>
      <p class="fi-hint">Pre-selected warehouse on the POS page.</p>
    </div>
    <div class="fi-group">
      <label class="fi-label">Default Customer</label>
      <select name="pos_default_customer" class="fi">
        <option value="">Walk-in Customer</option>
        @foreach($customers??[] as $c)
        <option value="{{ $c->id }}" {{ ($settings['pos_default_customer']??'')==$c->id?'selected':'' }}>{{ $c->name }}</option>
        @endforeach
      </select>
      <p class="fi-hint">Pre-selected customer on the POS page.</p>
    </div>
  </div>
</div>

<div class="flex justify-end"><button type="submit" class="btn-ac">{{ __('app.save') }}</button></div>
</form>
</div>
@endsection
