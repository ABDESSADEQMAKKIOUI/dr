@extends('layouts.app')
@section('title', 'Product Purchases Report')
@php
$pageTitle = 'Product Purchases Report';
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>'Product Purchases','url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg></div>
  <div><p class="pg-hero-title">Product Purchases Detail</p><p class="pg-hero-sub">Quantity received and cost breakdown per product from suppliers.</p></div>
</div>

<div class="rf">
  <div class="rf-head"><span class="rf-title">{{ __('app.filter') }}</span><a href="{{ route('reports.export-csv',['type'=>'product-purchases','from'=>$from,'to'=>$to]) }}" class="btn-out" style="padding:.5rem 1rem;font-size:.8125rem">{{ __('app.export_csv') }}</a></div>
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div><label class="fi-label">{{ __('app.from') }}</label><input type="date" name="from" value="{{ $from }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.to') }}</label><input type="date" name="to" value="{{ $to }}" class="fi"></div>
    <div><label class="fi-label">Supplier</label><select name="supplier_id" class="fi" style="width:200px"><option value="">All Suppliers</option>@foreach($suppliers as $s)<option value="{{ $s->id }}" {{ request('supplier_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach</select></div>
    <button type="submit" class="btn-ac">{{ __('app.filter') }}</button>
  </form>
</div>

<div class="mt-wrap">
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>Product</th><th>{{ __('app.sku') }}</th><th>{{ __('app.category') }}</th><th>Qty Received</th><th>Avg Cost</th><th>Total Cost</th></tr></thead>
      <tbody>
      @forelse($rows as $row)
      <tr>
        <td class="font-medium">{{ $row->product_name }}</td>
        <td><code class="text-xs bg-gray-100 px-1 rounded">{{ $row->sku }}</code></td>
        <td class="text-sm text-gray-500">{{ $row->category??'—' }}</td>
        <td class="font-semibold">{{ number_format($row->qty_received) }}</td>
        <td>{{ number_format($row->avg_cost,2) }} DH</td>
        <td class="font-bold" style="color:{{ $accent }}">{{ number_format($row->total_cost,2) }} DH</td>
      </tr>
      @empty
      <tr><td colspan="6" class="text-center py-10 text-gray-400">No data for the selected period.</td></tr>
      @endforelse
      </tbody>
      @if($rows->count())
      <tfoot style="background:#f8fafc">
        <tr>
          <td colspan="3" class="font-bold text-gray-700">{{ __('app.total') }}</td>
          <td class="font-bold">{{ number_format($rows->sum('qty_received')) }}</td>
          <td></td>
          <td class="font-bold" style="color:{{ $accent }}">{{ number_format($rows->sum('total_cost'),2) }} DH</td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
</div>
@endsection
