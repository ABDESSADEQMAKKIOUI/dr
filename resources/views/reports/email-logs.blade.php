@extends('layouts.app')
@section('title', __('app.email_delivery_log'))
@php
$pageTitle  = __('app.email_delivery_log');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>__('app.email_log'),'url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.email_delivery_log') }}</p><p class="pg-hero-sub">{{ $logs->total() }} {{ __('app.total_emails') }} recorded.</p></div>
</div>

<div class="rf">
  <form method="GET" class="grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
    <div><label class="fi-label">{{ __('app.event') }}</label><select name="event" class="fi"><option value="">{{ __('app.all_events') }}</option>@foreach(['invoice','sale_created','payment_received','quotation_created'] as $ev)<option value="{{ $ev }}" {{ request('event')===$ev?'selected':'' }}>{{ ucwords(str_replace('_',' ',$ev)) }}</option>@endforeach</select></div>
    <div><label class="fi-label">{{ __('app.status') }}</label><select name="status" class="fi"><option value="">{{ __('app.all_status') }}</option><option value="sent" {{ request('status')==='sent'?'selected':'' }}>{{ __('app.sent') }}</option><option value="failed" {{ request('status')==='failed'?'selected':'' }}>{{ __('app.failed') }}</option></select></div>
    <div><label class="fi-label">{{ __('app.from_date') }}</label><input type="date" name="from" value="{{ request('from') }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.to_date') }}</label><input type="date" name="to" value="{{ request('to') }}" class="fi"></div>
    <div class="flex gap-2">
      <button type="submit" class="btn-ac flex-1">{{ __('app.filter') }}</button>
      <a href="{{ route('reports.email-logs') }}" class="btn-out">{{ __('app.reset') }}</a>
    </div>
  </form>
</div>

<div class="mt-wrap">
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>{{ __('app.to') }}</th><th>{{ __('app.subject') }}</th><th>{{ __('app.event') }}</th><th>{{ __('app.status') }}</th><th>{{ __('app.error') }}</th><th>{{ __('app.sent_at') }}</th></tr></thead>
      <tbody>
      @forelse($logs as $log)
      <tr>
        <td class="text-sm">{{ $log->to }}</td>
        <td class="text-sm max-w-xs truncate" style="max-width:200px" title="{{ $log->subject }}">{{ $log->subject }}</td>
        <td><span class="badge-info">{{ $log->event?:'—' }}</span></td>
        <td>@if($log->status==='sent')<span class="badge-ok">{{ __('app.sent') }}</span>@else<span class="badge-danger">{{ __('app.failed') }}</span>@endif</td>
        <td class="text-xs text-red-400 max-w-xs truncate" style="max-width:180px" title="{{ $log->error }}">{{ $log->error?:'—' }}</td>
        <td class="text-sm text-gray-400">{{ $log->sent_at?$log->sent_at->format('d/m/Y H:i'):$log->created_at->format('d/m/Y H:i') }}</td>
      </tr>
      @empty
      <tr><td colspan="6" class="text-center py-10 text-gray-400">{{ __('app.no_email_logs_found') }}</td></tr>
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
