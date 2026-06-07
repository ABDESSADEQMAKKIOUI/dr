@extends('layouts.app')
@section('title', 'Money Transfers Report')
@php
$pageTitle = 'Money Transfers Report';
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>'Money Transfers','url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg></div>
  <div><p class="pg-hero-title">Money Transfers Report</p><p class="pg-hero-sub">Inter-account transfers with fees and net amounts.</p></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
  <div class="kpi"><div><p class="kpi-label">Total Transferred</p><p class="kpi-value sm">{{ number_format($totalAmount,2) }} DH</p></div><div class="kpi-icon" style="background:#dbeafe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">Transfers</p><p class="kpi-value">{{ $count }}</p></div><div class="kpi-icon" style="background:#d1fae5"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">Total Fees</p><p class="kpi-value sm">{{ number_format($totalFees,2) }} DH</p></div><div class="kpi-icon" style="background:#fee2e2"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ef4444"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg></div></div>
</div>

<div class="rf">
  <div class="rf-head"><span class="rf-title">{{ __('app.filter') }}</span><a href="{{ route('reports.export-csv',['type'=>'money-transfers','from'=>$from,'to'=>$to]) }}" class="btn-out" style="padding:.5rem 1rem;font-size:.8125rem">{{ __('app.export_csv') }}</a></div>
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div><label class="fi-label">{{ __('app.from') }}</label><input type="date" name="from" value="{{ $from }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.to') }}</label><input type="date" name="to" value="{{ $to }}" class="fi"></div>
    <button type="submit" class="btn-ac">{{ __('app.filter') }}</button>
  </form>
</div>

<div class="mt-wrap">
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>Date</th><th>Reference</th><th>From Account</th><th>To Account</th><th>Amount</th><th>Fee</th><th>Net</th></tr></thead>
      <tbody>
      @forelse($transfers as $transfer)
      <tr>
        <td class="text-sm">{{ \Carbon\Carbon::parse($transfer->date)->format('d/m/Y') }}</td>
        <td><code class="text-xs bg-gray-100 px-1 rounded">{{ $transfer->reference??'—' }}</code></td>
        <td class="text-sm">{{ $transfer->fromAccount->name??'—' }}</td>
        <td class="text-sm">{{ $transfer->toAccount->name??'—' }}</td>
        <td class="font-bold" style="color:{{ $accent }}">{{ number_format($transfer->amount,2) }} DH</td>
        <td class="text-red-500 text-sm">{{ number_format($transfer->fee??0,2) }} DH</td>
        <td class="font-semibold">{{ number_format($transfer->amount-($transfer->fee??0),2) }} DH</td>
      </tr>
      @empty
      <tr><td colspan="7" class="text-center py-10 text-gray-400">No transfers for selected period.</td></tr>
      @endforelse
      </tbody>
      @if($transfers->count())
      <tfoot style="background:#f8fafc">
        <tr>
          <td colspan="4" class="font-bold text-gray-700">{{ __('app.total') }}</td>
          <td class="font-bold" style="color:{{ $accent }}">{{ number_format($transfers->sum('amount'),2) }} DH</td>
          <td class="font-bold text-red-500">{{ number_format($transfers->sum('fee'),2) }} DH</td>
          <td class="font-bold">{{ number_format($transfers->sum('amount')-$transfers->sum('fee'),2) }} DH</td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
</div>
@endsection
