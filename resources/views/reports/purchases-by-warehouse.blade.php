@extends('layouts.app')
@section('title', 'Purchases by Warehouse')
@php
$pageTitle = 'Purchases by Warehouse';
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>'Purchases by Warehouse','url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016 2.993 2.993 0 0 0 2.25-1.016 3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/></svg></div>
  <div><p class="pg-hero-title">Purchases by Warehouse</p><p class="pg-hero-sub">Purchase orders and payment status per warehouse location.</p></div>
</div>

<div class="rf">
  <div class="rf-head"><span class="rf-title">{{ __('app.filter') }}</span><a href="{{ route('reports.export-csv',['type'=>'purchases-by-warehouse','from'=>$from,'to'=>$to]) }}" class="btn-out" style="padding:.5rem 1rem;font-size:.8125rem">{{ __('app.export_csv') }}</a></div>
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div><label class="fi-label">{{ __('app.from') }}</label><input type="date" name="from" value="{{ $from }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.to') }}</label><input type="date" name="to" value="{{ $to }}" class="fi"></div>
    <button type="submit" class="btn-ac">{{ __('app.filter') }}</button>
  </form>
</div>

<div class="mt-wrap">
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>{{ __('app.warehouse') }}</th><th>{{ __('app.orders') }}</th><th>{{ __('app.total') }}</th><th>{{ __('app.paid') }}</th><th>{{ __('app.due') }}</th></tr></thead>
      <tbody>
      @forelse($rows as $row)
      <tr>
        <td class="font-medium">{{ $row->warehouse }}</td>
        <td><span class="badge-info">{{ $row->orders }}</span></td>
        <td class="font-bold" style="color:{{ $accent }}">{{ number_format($row->total,2) }} DH</td>
        <td class="font-semibold text-green-600">{{ number_format($row->paid,2) }} DH</td>
        <td class="font-semibold {{ ($row->total-$row->paid)>0?'text-red-500':'text-gray-400' }}">{{ number_format($row->total-$row->paid,2) }} DH</td>
      </tr>
      @empty
      <tr><td colspan="5" class="text-center py-10 text-gray-400">No data.</td></tr>
      @endforelse
      </tbody>
      @if($rows->count())
      <tfoot style="background:#f8fafc">
        <tr>
          <td class="font-bold text-gray-700">{{ __('app.total') }}</td>
          <td class="font-bold">{{ $rows->sum('orders') }}</td>
          <td class="font-bold" style="color:{{ $accent }}">{{ number_format($rows->sum('total'),2) }} DH</td>
          <td class="font-bold text-green-600">{{ number_format($rows->sum('paid'),2) }} DH</td>
          <td class="font-bold text-red-500">{{ number_format($rows->sum('total')-$rows->sum('paid'),2) }} DH</td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
</div>
@endsection
