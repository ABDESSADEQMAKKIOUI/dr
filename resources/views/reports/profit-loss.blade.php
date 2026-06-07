@extends('layouts.app')
@section('title', 'Profit & Loss Report')
@php
$pageTitle  = 'Profit & Loss Report';
$breadcrumbs = [['label'=>'Dashboard','url'=>route('dashboard')],['label'=>'Reports','url'=>'#'],['label'=>'Profit & Loss','url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z"/></svg></div>
  <div><p class="pg-hero-title">Profit & Loss Report</p><p class="pg-hero-sub">Revenue, COGS, gross profit, expenses, and net profit.</p></div>
</div>

<div class="rf">
  <form method="GET" class="grid grid-cols-2 md:grid-cols-4 gap-4 items-end">
    <div><label class="fi-label">From</label><input type="date" name="from" value="{{ $from }}" class="fi"></div>
    <div><label class="fi-label">To</label><input type="date" name="to" value="{{ $to }}" class="fi"></div>
    <div>
      <label class="fi-label">Costing Method</label>
      <select name="method" class="fi">
        <option value="cogs" {{ $method==='cogs'?'selected':'' }}>Standard Cost</option>
        <option value="average" {{ $method==='average'?'selected':'' }}>Weighted Average</option>
        <option value="fifo" {{ $method==='fifo'?'selected':'' }}>FIFO</option>
      </select>
    </div>
    <div class="flex gap-3">
      <button type="submit" class="btn-ac flex-1">Generate</button>
      <button type="button" onclick="window.print()" class="btn-out">PDF</button>
    </div>
  </form>
  <p class="text-xs text-gray-400 mt-2">Costing method: <span class="font-semibold uppercase">{{ $method }}</span></p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
  <div class="kpi"><div><p class="kpi-label">Revenue</p><p class="kpi-value sm">{{ number_format($revenue,2) }} DH</p></div><div class="kpi-icon" style="background:#d1fae5"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">Gross Profit <span class="text-xs font-normal">(after COGS {{ number_format($cogs,2) }} DH)</span></p><p class="kpi-value sm">{{ number_format($gross_profit,2) }} DH</p></div><div class="kpi-icon" style="background:#dbeafe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div></div>
  <div class="kpi" style="{{ $net_profit>=0 ? '' : 'border-color:#fecaca' }}"><div><p class="kpi-label">Net Profit <span class="text-xs font-normal">Margin: {{ $margin }}%</span></p><p class="kpi-value sm {{ $net_profit>=0 ? '' : 'text-red-600' }}">{{ number_format($net_profit,2) }} DH</p></div><div class="kpi-icon" style="background:{{ $net_profit>=0 ? '#d1fae5' : '#fee2e2' }}"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $net_profit>=0 ? '#10b981' : '#ef4444' }}"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg></div></div>
</div>

<div class="mt-wrap">
  <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9"><p class="font-semibold text-gray-800">Income Statement</p></div>
  <table class="mt">
    <tbody>
      @foreach([['Revenue (Sales)',$revenue,'text-green-600'],['Cost of Goods Sold (COGS)','-'.$cogs,'text-red-500'],['Gross Profit',$gross_profit,'font-bold text-blue-600'],['Operating Expenses','',('' )]] as [$label,$val,$cls])
      @endforeach
    </tbody>
  </table>
  @if(!empty($rows))
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>Description</th><th style="text-align:right">Amount</th></tr></thead>
      <tbody>
        @foreach($rows??[] as $row)
        <tr {{ isset($row['bold']) ? 'style="background:#f8fafc"' : '' }}>
          <td class="{{ isset($row['bold']) ? 'font-bold' : 'font-medium' }} {{ isset($row['indent']) ? 'pl-8' : '' }}">{{ $row['label'] }}</td>
          <td style="text-align:right" class="{{ $row['class']??'' }} {{ isset($row['bold']) ? 'font-bold' : '' }}">{{ $row['value'] }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @else
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>Description</th><th style="text-align:right">Amount</th></tr></thead>
      <tbody>
        <tr><td class="font-medium">Revenue</td><td style="text-align:right" class="font-semibold text-green-600">{{ number_format($revenue,2) }} DH</td></tr>
        <tr><td class="font-medium pl-8">Cost of Goods Sold</td><td style="text-align:right" class="text-red-500">-{{ number_format($cogs,2) }} DH</td></tr>
        <tr style="background:#f8fafc"><td class="font-bold">Gross Profit</td><td style="text-align:right" class="font-bold text-blue-600">{{ number_format($gross_profit,2) }} DH</td></tr>
        <tr><td class="font-medium pl-8">Operating Expenses</td><td style="text-align:right" class="text-red-500">-{{ number_format($expenses??0,2) }} DH</td></tr>
        <tr style="background:#f8fafc"><td class="font-bold">Net Profit</td><td style="text-align:right" class="font-bold {{ $net_profit>=0 ? 'text-green-600' : 'text-red-500' }}">{{ number_format($net_profit,2) }} DH</td></tr>
      </tbody>
    </table>
  </div>
  @endif
</div>
</div>
@endsection
