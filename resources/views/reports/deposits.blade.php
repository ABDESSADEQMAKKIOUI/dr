@extends('layouts.app')
@section('title', 'Deposits Report')
@php
$pageTitle = 'Deposits Report';
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>'Deposits','url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div>
  <div><p class="pg-hero-title">Deposits Report</p><p class="pg-hero-sub">All deposit transactions recorded across accounts and categories.</p></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
  <div class="kpi"><div><p class="kpi-label">Total Deposits</p><p class="kpi-value sm">{{ number_format($total,2) }} DH</p></div><div class="kpi-icon" style="background:#d1fae5"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">Number of Deposits</p><p class="kpi-value">{{ $count }}</p></div><div class="kpi-icon" style="background:#dbeafe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">Average Deposit</p><p class="kpi-value sm">{{ $count>0?number_format($total/$count,2):'0.00' }} DH</p></div><div class="kpi-icon" style="background:#ede9fe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8b5cf6"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z"/></svg></div></div>
</div>

<div class="rf">
  <div class="rf-head"><span class="rf-title">{{ __('app.filter') }}</span><a href="{{ route('reports.export-csv',['type'=>'deposits','from'=>$from,'to'=>$to]) }}" class="btn-out" style="padding:.5rem 1rem;font-size:.8125rem">{{ __('app.export_csv') }}</a></div>
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div><label class="fi-label">{{ __('app.from') }}</label><input type="date" name="from" value="{{ $from }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.to') }}</label><input type="date" name="to" value="{{ $to }}" class="fi"></div>
    <button type="submit" class="btn-ac">{{ __('app.filter') }}</button>
  </form>
</div>

<div class="mt-wrap">
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>Date</th><th>Reference</th><th>Category</th><th>Account</th><th>Amount</th><th>Notes</th></tr></thead>
      <tbody>
      @forelse($deposits as $deposit)
      <tr>
        <td class="text-sm">{{ \Carbon\Carbon::parse($deposit->date)->format('d/m/Y') }}</td>
        <td><code class="text-xs bg-gray-100 px-1 rounded">{{ $deposit->reference??'—' }}</code></td>
        <td class="text-sm">{{ $deposit->category->name??'—' }}</td>
        <td class="text-sm">{{ $deposit->account->name??'—' }}</td>
        <td class="font-bold text-green-600">{{ number_format($deposit->amount,2) }} DH</td>
        <td class="text-xs text-gray-400">{{ $deposit->notes??'' }}</td>
      </tr>
      @empty
      <tr><td colspan="6" class="text-center py-10 text-gray-400">No deposits for selected period.</td></tr>
      @endforelse
      </tbody>
      @if($deposits->count())
      <tfoot style="background:#f8fafc">
        <tr>
          <td colspan="4" class="font-bold text-gray-700">{{ __('app.total') }}</td>
          <td class="font-bold text-green-600">{{ number_format($deposits->sum('amount'),2) }} DH</td>
          <td></td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
</div>
@endsection
