@extends('layouts.app')
@section('title', __('app.inventory_valuation'))
@php
$pageTitle  = __('app.inventory_valuation_summary');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>__('app.inventory_valuation'),'url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.inventory_valuation') }}</p><p class="pg-hero-sub">Stock cost and sell-value summary across all products.</p></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_products') }}</p><p class="kpi-value">{{ $products->count() }}</p></div><div class="kpi-icon" style="background:#dbeafe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m16.5 0H3.375"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.cost_value') }}</p><p class="kpi-value sm">{{ number_format($totalCostValue,2) }} DH</p></div><div class="kpi-icon" style="background:#fef3c7"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#f59e0b"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.sell_value_potential') }}</p><p class="kpi-value sm">{{ number_format($totalSellValue,2) }} DH</p></div><div class="kpi-icon" style="background:#d1fae5"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg></div></div>
</div>

<div class="mt-wrap">
  <div class="rf" style="border-radius:0;margin-bottom:0;border:none;border-bottom:1px solid #f1f5f9">
    <div class="rf-head"><span class="rf-title">Filter</span><a href="{{ route('reports.export-csv',['type'=>'inventory-valuation']) }}" class="btn-out" style="padding:.5rem 1rem;font-size:.8125rem">{{ __('app.export_csv') }}</a></div>
    <form method="GET" class="flex gap-4 items-end">
      <div><label class="fi-label">{{ __('app.warehouse') }}</label><select name="warehouse_id" class="fi" style="width:240px"><option value="">{{ __('app.all_warehouses') }}</option>@foreach($warehouses as $wh)<option value="{{ $wh->id }}" {{ $warehouseId==$wh->id?'selected':'' }}>{{ $wh->name }}</option>@endforeach</select></div>
      <button type="submit" class="btn-ac">{{ __('app.filter') }}</button>
    </form>
  </div>
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>{{ __('app.product') }}</th><th>{{ __('app.sku') }}</th><th>{{ __('app.category') }}</th><th>{{ __('app.qty') }}</th><th>{{ __('app.cost_price') }}</th><th>{{ __('app.sell_price') }}</th><th>{{ __('app.cost_value') }}</th><th>{{ __('app.sell_value') }}</th></tr></thead>
      <tbody>
      @forelse($products as $p)
      <tr>
        <td class="font-medium">{{ $p->name }}</td>
        <td><code class="text-xs bg-gray-100 px-1 rounded">{{ $p->sku }}</code></td>
        <td class="text-sm">{{ $p->category->name??'—' }}</td>
        <td class="{{ $p->stock_qty<=0?'text-red-500 font-bold':'' }}">{{ $p->stock_qty }}</td>
        <td>{{ number_format($p->cost_price,2) }} DH</td>
        <td>{{ number_format($p->price,2) }} DH</td>
        <td class="font-semibold text-amber-600">{{ number_format($p->stock_qty*$p->cost_price,2) }} DH</td>
        <td class="font-semibold text-green-600">{{ number_format($p->stock_qty*$p->price,2) }} DH</td>
      </tr>
      @empty
      <tr><td colspan="8" class="text-center py-10 text-gray-400">No inventory data found.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
</div>
@endsection
