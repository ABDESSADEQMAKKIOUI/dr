@extends('layouts.app')
@section('title', 'Payment Transactions')
@php
$pageTitle  = 'Payment Transactions';
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>'Payment Transactions','url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg></div>
  <div><p class="pg-hero-title">Payment Transactions</p><p class="pg-hero-sub">All incoming and outgoing payments for the selected period.</p></div>
</div>

<div class="rf">
  <div class="rf-head">
    <span class="rf-title">Filter Transactions</span>
    <a href="{{ route('reports.export-csv', ['type'=>'payment-transactions','from'=>$from,'to'=>$to]) }}" class="btn-out" style="padding:.5rem 1rem;font-size:.8125rem">Export CSV</a>
  </div>
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div><label class="fi-label">From</label><input type="date" name="from" value="{{ $from }}" class="fi"></div>
    <div><label class="fi-label">To</label><input type="date" name="to" value="{{ $to }}" class="fi"></div>
    <button type="submit" class="btn-ac">Filter</button>
  </form>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="kpi"><div><p class="kpi-label">Total In (Sales)</p><p class="kpi-value sm text-green-600">{{ number_format($transactions->where('type','Sale')->sum('amount'),2) }} DH</p></div><div class="kpi-icon" style="background:#d1fae5"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">Total Out (Purchases)</p><p class="kpi-value sm text-red-500">{{ number_format($transactions->where('type','Purchase')->sum('amount'),2) }} DH</p></div><div class="kpi-icon" style="background:#fee2e2"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ef4444"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">Net Cash Flow</p><p class="kpi-value sm" style="color:{{ $accent }}">{{ number_format($transactions->where('type','Sale')->sum('amount')-$transactions->where('type','Purchase')->sum('amount'),2) }} DH</p></div><div class="kpi-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">Total Transactions</p><p class="kpi-value">{{ $transactions->count() }}</p></div><div class="kpi-icon" style="background:#f3f4f6"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#6b7280"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/></svg></div></div>
</div>

<div class="mt-wrap">
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>Date</th><th>Type</th><th>Reference</th><th>Party</th><th>Amount</th><th>Method</th></tr></thead>
      <tbody>
      @forelse($transactions as $tx)
      <tr>
        <td class="text-sm">{{ \Carbon\Carbon::parse($tx->date)->format('d/m/Y') }}</td>
        <td><span class="{{ $tx->type==='Sale'?'badge-ok':'badge-danger' }}">{{ $tx->type }}</span></td>
        <td class="font-mono text-sm">{{ $tx->ref }}</td>
        <td>{{ $tx->party }}</td>
        <td class="font-semibold {{ $tx->type==='Sale'?'text-green-600':'text-red-500' }}">{{ number_format($tx->amount,2) }} DH</td>
        <td class="capitalize text-sm text-gray-500">{{ $tx->method }}</td>
      </tr>
      @empty
      <tr><td colspan="6" class="text-center py-10 text-gray-400">No transactions found for this period.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  @if(method_exists($transactions,'hasPages') && $transactions->hasPages())
  <div class="p-4 border-t border-gray-100">{{ $transactions->appends(request()->query())->links() }}</div>
  @endif
</div>
</div>
@endsection
