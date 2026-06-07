@extends('layouts.app')
@section('title', __('app.stock_by_warehouse'))
@php
$pageTitle = __('app.stock_count_by_warehouse');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>__('app.stock_by_warehouse'),'url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.stock_count_by_warehouse') }}</p><p class="pg-hero-sub">Current stock levels and valuation grouped by warehouse.</p></div>
</div>

<div class="rf">
  <div class="rf-head"><span class="rf-title">{{ __('app.filter') }}</span></div>
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div><label class="fi-label">{{ __('app.warehouse') }}</label><select name="warehouse_id" class="fi" style="width:220px"><option value="">{{ __('app.all_warehouses') }}</option>@foreach($warehouses as $wh)<option value="{{ $wh->id }}" {{ $warehouseId==$wh->id?'selected':'' }}>{{ $wh->name }}</option>@endforeach</select></div>
    <button type="submit" class="btn-ac">{{ __('app.filter') }}</button>
  </form>
</div>

@if($rows->isEmpty())
<div class="mt-wrap"><p class="text-center py-10 text-gray-400">{{ __('app.no_stock_data_found') }}</p></div>
@else
@foreach($rows as $warehouseName => $items)
<div class="mt-wrap" style="margin-bottom:1.5rem">
  <div style="padding:.75rem 1.25rem;background:linear-gradient(135deg,var(--accent),color-mix(in srgb,var(--accent) 70%,#000));border-radius:.75rem .75rem 0 0">
    <span style="color:#fff;font-weight:600;font-size:.9375rem">{{ $warehouseName }}</span>
  </div>
  <div class="overflow-x-auto">
    <table class="mt" style="border-radius:0">
      <thead><tr><th>{{ __('app.product') }}</th><th>{{ __('app.sku') }}</th><th>{{ __('app.qty') }}</th><th>{{ __('app.unit_cost') }}</th><th>{{ __('app.stock_value') }}</th></tr></thead>
      <tbody>
      @foreach($items as $pw)
      @if($pw->product)
      <tr>
        <td class="font-medium">{{ $pw->product->name }}</td>
        <td><code class="text-xs bg-gray-100 px-1 rounded">{{ $pw->product->sku }}</code></td>
        <td class="{{ $pw->quantity<=0?'text-red-500 font-bold':'' }}">{{ $pw->quantity }}</td>
        <td>{{ number_format($pw->product->cost_price,2) }} DH</td>
        <td class="font-semibold" style="color:{{ $accent }}">{{ number_format($pw->quantity*$pw->product->cost_price,2) }} DH</td>
      </tr>
      @endif
      @endforeach
      </tbody>
      <tfoot style="background:#f8fafc">
        <tr>
          <td colspan="2" class="font-bold text-gray-700">{{ __('app.subtotal') }}</td>
          <td class="font-bold">{{ $items->where('product','!=',null)->sum('quantity') }}</td>
          <td></td>
          <td class="font-bold" style="color:{{ $accent }}">{{ number_format($items->filter(fn($pw)=>$pw->product)->sum(fn($pw)=>$pw->quantity*$pw->product->cost_price),2) }} DH</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
@endforeach
@endif

</div>
@endsection
