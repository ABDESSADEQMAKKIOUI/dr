@extends('layouts.app')
@section('title', 'Quotations by Warehouse')
@php
$pageTitle = 'Quotations by Warehouse';
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>'Quotations by Warehouse','url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg></div>
  <div><p class="pg-hero-title">Quotations by Warehouse</p><p class="pg-hero-sub">Quotation volume, value, and conversion breakdown per warehouse.</p></div>
</div>

<div class="rf">
  <div class="rf-head"><span class="rf-title">{{ __('app.filter') }}</span><a href="{{ route('reports.export-csv',['type'=>'quotations-by-warehouse','from'=>$from,'to'=>$to]) }}" class="btn-out" style="padding:.5rem 1rem;font-size:.8125rem">{{ __('app.export_csv') }}</a></div>
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div><label class="fi-label">{{ __('app.from') }}</label><input type="date" name="from" value="{{ $from }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.to') }}</label><input type="date" name="to" value="{{ $to }}" class="fi"></div>
    <button type="submit" class="btn-ac">{{ __('app.filter') }}</button>
  </form>
</div>

<div class="mt-wrap">
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>{{ __('app.warehouse') }}</th><th>Quotations</th><th>Total Value</th><th>Converted to Sale</th></tr></thead>
      <tbody>
      @forelse($rows as $row)
      <tr>
        <td class="font-medium">{{ $row->warehouse }}</td>
        <td><span class="badge-info">{{ $row->count }}</span></td>
        <td class="font-bold" style="color:{{ $accent }}">{{ number_format($row->total,2) }} DH</td>
        <td><span class="badge-ok">{{ $row->converted }}</span></td>
      </tr>
      @empty
      <tr><td colspan="4" class="text-center py-10 text-gray-400">No quotation data for selected period.</td></tr>
      @endforelse
      </tbody>
      @if($rows->count())
      <tfoot style="background:#f8fafc">
        <tr>
          <td class="font-bold text-gray-700">{{ __('app.total') }}</td>
          <td class="font-bold">{{ $rows->sum('count') }}</td>
          <td class="font-bold" style="color:{{ $accent }}">{{ number_format($rows->sum('total'),2) }} DH</td>
          <td class="font-bold">{{ $rows->sum('converted') }}</td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
</div>
@endsection
