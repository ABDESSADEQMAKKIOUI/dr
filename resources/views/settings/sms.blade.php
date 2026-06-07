@extends('layouts.app')
@section('title', 'SMS & WhatsApp Settings')
@php
$pageTitle  = 'SMS & WhatsApp Settings';
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.settings'),'url'=>'#'],['label'=>'SMS & WhatsApp','url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}" x-data="{ gateway: '{{ old('gateway', $settings->gateway ?? 'twilio') }}' }">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg></div>
  <div><p class="pg-hero-title">SMS & WhatsApp Settings</p><p class="pg-hero-sub">Configure gateways and notification triggers for SMS and WhatsApp messages.</p></div>
</div>

@if(session('success'))<div class="flash-ok"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>{{ session('success') }}</div>@endif
@if(session('error'))<div class="flash-err"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>{{ session('error') }}</div>@endif

<form method="POST" action="{{ route('settings.sms.update') }}">
@csrf

{{-- Enable channels --}}
<div class="mc">
  <div class="mc-head"><div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5"/></svg></div><div><p class="mc-head-title">Enable Channels</p><p class="mc-head-sub">Toggle the communication channels you want to use.</p></div></div>
  <div class="divide-y divide-gray-50">
    <div class="tr"><div><p class="tr-title">SMS Notifications</p><p class="tr-sub">Send transactional SMS via the selected gateway</p></div><label class="ts"><input type="checkbox" name="sms_enabled" value="1" {{ old('sms_enabled',$settings->sms_enabled??false)?'checked':'' }}><span class="ts-slider"></span></label></div>
    <div class="tr"><div><p class="tr-title">WhatsApp Notifications</p><p class="tr-sub">Send messages via Meta WhatsApp Cloud API</p></div><label class="ts"><input type="checkbox" name="whatsapp_enabled" value="1" {{ old('whatsapp_enabled',$settings->whatsapp_enabled??false)?'checked':'' }}><span class="ts-slider"></span></label></div>
  </div>
</div>

{{-- Gateway selector --}}
<div class="mc">
  <div class="mc-head"><div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 6 0m-6 0H3m16.5 0a3 3 0 0 0 3-3m-3 3a3 3 0 1 1-6 0m6 0h1.5m-7.5-3a3 3 0 0 1 3-3m-3 3H3m13.5-3H21M3 11.25h1.5m0 0a3 3 0 0 1 3-3m0 0a3 3 0 0 1 3 3m-6 0h6"/></svg></div><div><p class="mc-head-title">SMS Gateway</p><p class="mc-head-sub">Select your SMS provider.</p></div></div>
  <div class="mc-body-lg">
    <select name="gateway" x-model="gateway" class="fi" style="max-width:280px">
      <option value="twilio">Twilio</option>
      <option value="nexmo">Nexmo / Vonage</option>
      <option value="infobip">InfoBip</option>
      <option value="termii">Termii</option>
      <option value="whatsapp">WhatsApp Only</option>
    </select>
  </div>
</div>

{{-- Gateway credentials --}}
<div class="mc" x-show="gateway==='twilio'" x-cloak>
  <div class="mc-head"><div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg></div><div><p class="mc-head-title">Twilio Credentials</p></div></div>
  <div class="mc-body-lg grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="fi-group"><label class="fi-label">Account SID</label><input type="text" name="twilio_sid" value="{{ old('twilio_sid',$settings->twilio_sid??'') }}" class="fi" placeholder="ACxxxxxxxxx"></div>
    <div class="fi-group"><label class="fi-label">Auth Token</label><input type="password" name="twilio_token" value="{{ old('twilio_token',$settings->twilio_token??'') }}" class="fi"></div>
    <div class="fi-group"><label class="fi-label">From Number</label><input type="text" name="twilio_from" value="{{ old('twilio_from',$settings->twilio_from??'') }}" class="fi" placeholder="+1234567890"></div>
  </div>
</div>

<div class="mc" x-show="gateway==='nexmo'" x-cloak>
  <div class="mc-head"><div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg></div><div><p class="mc-head-title">Nexmo / Vonage Credentials</p></div></div>
  <div class="mc-body-lg grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="fi-group"><label class="fi-label">API Key</label><input type="text" name="nexmo_key" value="{{ old('nexmo_key',$settings->nexmo_key??'') }}" class="fi"></div>
    <div class="fi-group"><label class="fi-label">API Secret</label><input type="password" name="nexmo_secret" value="{{ old('nexmo_secret',$settings->nexmo_secret??'') }}" class="fi"></div>
    <div class="fi-group"><label class="fi-label">From Name / Number</label><input type="text" name="nexmo_from" value="{{ old('nexmo_from',$settings->nexmo_from??'') }}" class="fi" placeholder="MyBrand"></div>
  </div>
</div>

<div class="mc" x-show="gateway==='infobip'" x-cloak>
  <div class="mc-head"><div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg></div><div><p class="mc-head-title">InfoBip Credentials</p></div></div>
  <div class="mc-body-lg grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="fi-group"><label class="fi-label">API Key</label><input type="text" name="infobip_api_key" value="{{ old('infobip_api_key',$settings->infobip_api_key??'') }}" class="fi"></div>
    <div class="fi-group"><label class="fi-label">Base URL</label><input type="url" name="infobip_base_url" value="{{ old('infobip_base_url',$settings->infobip_base_url??'') }}" class="fi" placeholder="https://xxx.api.infobip.com"></div>
    <div class="fi-group"><label class="fi-label">From Number / Name</label><input type="text" name="infobip_from" value="{{ old('infobip_from',$settings->infobip_from??'') }}" class="fi"></div>
  </div>
</div>

<div class="mc" x-show="gateway==='whatsapp'" x-cloak>
  <div class="mc-head"><div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg></div><div><p class="mc-head-title">WhatsApp Cloud API</p></div></div>
  <div class="mc-body-lg grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="fi-group"><label class="fi-label">Access Token</label><input type="password" name="whatsapp_token" value="{{ old('whatsapp_token',$settings->whatsapp_token??'') }}" class="fi"></div>
    <div class="fi-group"><label class="fi-label">Phone Number ID</label><input type="text" name="whatsapp_phone_id" value="{{ old('whatsapp_phone_id',$settings->whatsapp_phone_id??'') }}" class="fi" placeholder="123456789012345"></div>
  </div>
</div>

{{-- SMS triggers --}}
<div class="mc">
  <div class="mc-head"><div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg></div><div><p class="mc-head-title">SMS Notification Triggers</p><p class="mc-head-sub">Choose which events send an SMS.</p></div></div>
  <div class="divide-y divide-gray-50">
    @foreach(['notify_sale'=>['Sale Created','SMS when a new sale is confirmed'],'notify_purchase'=>['Purchase Created','SMS when a purchase order is placed'],'notify_quotation'=>['Quotation Created','SMS when a quotation is issued'],'notify_payment'=>['Payment Received','SMS when a payment is recorded'],'notify_sale_return'=>['Sale Return','SMS when a sale return is processed'],'notify_purchase_return'=>['Purchase Return','SMS when a purchase return is processed']] as $field=>[$label,$desc])
    <div class="tr"><div><p class="tr-title">{{ $label }}</p><p class="tr-sub">{{ $desc }}</p></div><label class="ts"><input type="checkbox" name="{{ $field }}" value="1" {{ old($field,$settings->{$field}??false)?'checked':'' }}><span class="ts-slider"></span></label></div>
    @endforeach
  </div>
</div>

{{-- WhatsApp triggers --}}
<div class="mc">
  <div class="mc-head"><div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg></div><div><p class="mc-head-title">WhatsApp Notification Triggers</p></div></div>
  <div class="divide-y divide-gray-50">
    @foreach(['notify_whatsapp_sale'=>['Sale Notifications','WhatsApp when a sale is confirmed'],'notify_whatsapp_purchase'=>['Purchase Notifications','WhatsApp when a purchase is placed']] as $field=>[$label,$desc])
    <div class="tr"><div><p class="tr-title">{{ $label }}</p><p class="tr-sub">{{ $desc }}</p></div><label class="ts"><input type="checkbox" name="{{ $field }}" value="1" {{ old($field,$settings->{$field}??false)?'checked':'' }}><span class="ts-slider"></span></label></div>
    @endforeach
  </div>
</div>

<div class="flex justify-end"><button type="submit" class="btn-ac">Save SMS Settings</button></div>
</form>

{{-- Test SMS --}}
<div class="mc">
  <div class="mc-head"><div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg></div><div><p class="mc-head-title">Send Test SMS</p><p class="mc-head-sub">Verify your gateway credentials with a test message.</p></div></div>
  <form method="POST" action="{{ route('settings.sms.test') }}" class="mc-body-lg flex gap-4 items-end">
    @csrf
    <div class="fi-group mb-0 flex-1"><label class="fi-label">Phone Number</label><input type="text" name="test_number" class="fi" placeholder="+212600000000" required></div>
    <button type="submit" class="btn-out">Send Test</button>
  </form>
</div>

</div>
@endsection
