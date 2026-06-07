@extends('layouts.app')
@section('title', __('app.stock_report'))
@php
$pageTitle  = __('app.stock_report');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>__('app.stock'),'url'=>'']];
$stats = $stats ?? ['total_products'=>0,'stock_value'=>0,'low_stock'=>0,'out_of_stock'=>0];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.stock_report') }}</p><p class="pg-hero-sub">Inventory levels, stock value, and low/out-of-stock alerts.</p></div>
</div>

<div class="rf">
  <form method="GET" class="grid grid-cols-2 md:grid-cols-4 gap-4 items-end">
    <div><label class="fi-label">{{ __('app.warehouse') }}</label><select name="warehouse_id" class="fi"><option value="">{{ __('app.all_warehouses') }}</option>@foreach($warehouses??[] as $w)<option value="{{ $w->id }}" {{ request('warehouse_id')==$w->id?'selected':'' }}>{{ $w->name }}</option>@endforeach</select></div>
    <div><label class="fi-label">{{ __('app.category') }}</label><select name="category_id" class="fi"><option value="">{{ __('app.all_categories') }}</option>@foreach($categories??[] as $c)<option value="{{ $c->id }}" {{ request('category_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select></div>
    <div><label class="fi-label">{{ __('app.status') }}</label><select name="status" class="fi"><option value="">{{ __('app.all_status') }}</option><option value="normal" {{ request('status')=='normal'?'selected':'' }}>{{ __('app.normal_stock') }}</option><option value="low" {{ request('status')=='low'?'selected':'' }}>{{ __('app.low_stock') }}</option><option value="out" {{ request('status')=='out'?'selected':'' }}>{{ __('app.out_of_stock') }}</option></select></div>
    <div class="flex gap-3">
      <button type="submit" class="btn-ac flex-1">{{ __('app.generate_report') }}</button>
      <button type="button" onclick="window.print()" class="btn-out">PDF</button>
    </div>
  </form>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_products') }}</p><p class="kpi-value">{{ $stats['total_products']??0 }}</p></div><div class="kpi-icon" style="background:#dbeafe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m16.5 0H3.375"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.stock_value') }}</p><p class="kpi-value sm">{{ number_format($stats['stock_value']??0,2) }} DH</p></div><div class="kpi-icon" style="background:#d1fae5"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.low_stock_items') }}</p><p class="kpi-value">{{ $stats['low_stock']??0 }}</p></div><div class="kpi-icon" style="background:#fef3c7"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#f59e0b"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.out_of_stock') }}</p><p class="kpi-value">{{ $stats['out_of_stock']??0 }}</p></div><div class="kpi-icon" style="background:#fee2e2"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ef4444"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></div></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"><h4 class="font-semibold text-gray-800 mb-4">{{ __('app.stock_by_category') }}</h4><canvas id="categoryChart" height="180"></canvas></div>
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"><h4 class="font-semibold text-gray-800 mb-4">{{ __('app.stock_status_distribution') }}</h4><canvas id="statusChart" height="180"></canvas></div>
</div>

<div class="mt-wrap">
  <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9"><p class="font-semibold text-gray-800">{{ __('app.inventory_details') }}</p></div>
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>{{ __('app.product') }}</th><th>{{ __('app.sku') }}</th><th>{{ __('app.category') }}</th><th>{{ __('app.warehouse') }}</th><th>{{ __('app.in_stock') }}</th><th>{{ __('app.min_stock') }}</th><th>{{ __('app.unit_price') }}</th><th>{{ __('app.stock_value') }}</th><th>{{ __('app.status') }}</th></tr></thead>
      <tbody>
      @forelse($inventory??[] as $item)
      <tr>
        <td class="font-medium">{{ $item->product->name }}</td>
        <td><code class="text-xs bg-gray-100 px-1 rounded">{{ $item->product->sku }}</code></td>
        <td>{{ $item->product->category->name??'N/A' }}</td>
        <td>{{ $item->warehouse->name }}</td>
        <td class="font-semibold">{{ $item->quantity }}</td>
        <td class="text-gray-500">{{ $item->product->stock_min }}</td>
        <td>{{ number_format($item->product->price,2) }} DH</td>
        <td class="font-semibold text-green-600">{{ number_format($item->quantity*$item->product->price,2) }} DH</td>
        <td>@if($item->quantity==0)<span class="badge-danger">{{ __('app.out_of_stock') }}</span>@elseif($item->quantity<=$item->product->stock_min)<span class="badge-warn">{{ __('app.low_stock') }}</span>@else<span class="badge-ok">OK</span>@endif</td>
      </tr>
      @empty
      <tr><td colspan="9" class="text-center py-10 text-gray-400">{{ __('app.no_inventory_data') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('categoryChart'),{type:'bar',data:{labels:{!! json_encode($chartData['categories']['labels']??[]) !!},datasets:[{label:'{{ __('app.products') }}',data:{!! json_encode($chartData['categories']['data']??[]) !!},backgroundColor:'{{ $accent }}'}]},options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
new Chart(document.getElementById('statusChart'),{type:'pie',data:{labels:['{{ __('app.normal_stock') }}','{{ __('app.low_stock') }}','{{ __('app.out_of_stock') }}'],datasets:[{data:{!! json_encode($chartData['status']['data']??[0,0,0]) !!},backgroundColor:['#10B981','#F59E0B','#EF4444']}]},options:{responsive:true,maintainAspectRatio:true}});
</script>
@endpush
@endsection
