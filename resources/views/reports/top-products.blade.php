@extends('layouts.app')
@section('title', __('app.top_selling_products'))
@php
$pageTitle  = __('app.top_selling_products');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>__('app.top_products'),'url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.040.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.top_selling_products') }}</p><p class="pg-hero-sub">Rank products by quantity sold and revenue generated.</p></div>
</div>

<div class="rf">
  <div class="rf-head">
    <span class="rf-title">Filter</span>
    <a href="{{ route('reports.export-csv',['type'=>'top-products','from'=>$from,'to'=>$to]) }}" class="btn-out" style="padding:.5rem 1rem;font-size:.8125rem">{{ __('app.export_csv') }}</a>
  </div>
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div><label class="fi-label">{{ __('app.from') }}</label><input type="date" name="from" value="{{ $from }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.to') }}</label><input type="date" name="to" value="{{ $to }}" class="fi"></div>
    <div>
      <label class="fi-label">{{ __('app.show_top') }}</label>
      <select name="limit" class="fi" style="width:auto">
        @foreach([10,20,50,100] as $l)
        <option value="{{ $l }}" {{ $limit==$l?'selected':'' }}>Top {{ $l }}</option>
        @endforeach
      </select>
    </div>
    <button type="submit" class="btn-ac">{{ __('app.filter') }}</button>
  </form>
</div>

<div class="mt-wrap">
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>#</th><th>{{ __('app.product') }}</th><th>{{ __('app.sku') }}</th><th>{{ __('app.qty_sold') }}</th><th>{{ __('app.revenue') }}</th></tr></thead>
      <tbody>
      @forelse($rows as $i => $row)
      <tr>
        <td class="text-gray-400 font-semibold">{{ $i+1 }}</td>
        <td class="font-medium">{{ $row->name }}</td>
        <td><code class="text-xs bg-gray-100 px-1 rounded">{{ $row->sku }}</code></td>
        <td class="font-semibold">{{ number_format($row->qty_sold) }}</td>
        <td class="font-bold" style="color:{{ $accent }}">{{ number_format($row->revenue,2) }} DH</td>
      </tr>
      @empty
      <tr><td colspan="5" class="text-center py-10 text-gray-400">{{ __('app.no_sales_data') }}</td></tr>
      @endforelse
      </tbody>
      @if($rows->count())
      <tfoot style="background:#f8fafc">
        <tr>
          <td></td>
          <td class="font-bold text-gray-700">{{ __('app.total') }} ({{ $rows->count() }} {{ __('app.products') }})</td>
          <td></td>
          <td class="font-bold">{{ number_format($rows->sum('qty_sold')) }}</td>
          <td class="font-bold" style="color:{{ $accent }}">{{ number_format($rows->sum('revenue'),2) }} DH</td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
</div>
@endsection
