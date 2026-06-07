@extends('layouts.app')
@section('title', __('app.sms_delivery_log'))
@php
$pageTitle  = __('app.sms_delivery_log');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>__('app.sms_log'),'url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.sms_delivery_log') }}</p><p class="pg-hero-sub">{{ $logs->total() }} {{ __('app.total_messages') }} recorded.</p></div>
</div>

<div class="rf">
  <form method="GET" class="grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
    <div><label class="fi-label">{{ __('app.gateway') }}</label><select name="gateway" class="fi"><option value="">{{ __('app.all_gateways') }}</option>@foreach(['twilio','nexmo','infobip','termii','whatsapp'] as $gw)<option value="{{ $gw }}" {{ request('gateway')===$gw?'selected':'' }}>{{ ucfirst($gw) }}</option>@endforeach</select></div>
    <div><label class="fi-label">{{ __('app.status') }}</label><select name="status" class="fi"><option value="">{{ __('app.all_status') }}</option><option value="sent" {{ request('status')==='sent'?'selected':'' }}>{{ __('app.sent') }}</option><option value="failed" {{ request('status')==='failed'?'selected':'' }}>{{ __('app.failed') }}</option></select></div>
    <div><label class="fi-label">{{ __('app.from_date') }}</label><input type="date" name="from" value="{{ request('from') }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.to_date') }}</label><input type="date" name="to" value="{{ request('to') }}" class="fi"></div>
    <div class="flex gap-2"><button type="submit" class="btn-ac flex-1">{{ __('app.filter') }}</button><a href="{{ route('reports.sms-logs') }}" class="btn-out">{{ __('app.reset') }}</a></div>
  </form>
</div>

<div class="mt-wrap">
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>{{ __('app.to') }}</th><th>{{ __('app.event') }}</th><th>{{ __('app.gateway') }}</th><th>{{ __('app.status') }}</th><th>{{ __('app.message') }}</th><th>{{ __('app.error') }}</th><th>{{ __('app.sent_at') }}</th></tr></thead>
      <tbody>
      @forelse($logs as $log)
      <tr>
        <td class="font-mono text-sm">{{ $log->to }}</td>
        <td><span class="badge-info">{{ $log->event?:'—' }}</span></td>
        <td class="capitalize text-sm">{{ $log->gateway }}</td>
        <td>@if($log->status==='sent')<span class="badge-ok">{{ __('app.sent') }}</span>@else<span class="badge-danger">{{ __('app.failed') }}</span>@endif</td>
        <td class="text-sm text-gray-600 max-w-xs truncate" style="max-width:200px" title="{{ $log->message }}">{{ $log->message }}</td>
        <td class="text-xs text-red-400 max-w-xs truncate" style="max-width:150px" title="{{ $log->error }}">{{ $log->error?:'—' }}</td>
        <td class="text-sm text-gray-400">{{ $log->sent_at?$log->sent_at->format('d/m/Y H:i'):$log->created_at->format('d/m/Y H:i') }}</td>
      </tr>
      @empty
      <tr><td colspan="7" class="text-center py-10 text-gray-400">{{ __('app.no_sms_logs_found') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  @if($logs->hasPages())
  <div class="p-4 border-t border-gray-100">{{ $logs->appends(request()->query())->links() }}</div>
  @endif
</div>
</div>
@endsection
