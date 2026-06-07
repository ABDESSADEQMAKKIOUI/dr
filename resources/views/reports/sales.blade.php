@extends('layouts.app')
@section('title', __('app.sales_report'))
@php
$pageTitle  = __('app.sales_report');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>__('app.sales'),'url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.sales_report') }}</p><p class="pg-hero-sub">Analyse revenue, profit, and top products over a date range.</p></div>
</div>

<div class="rf">
  <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
    <div><label class="fi-label">{{ __('app.start_date') }}</label><input type="date" name="from_date" value="{{ request('from_date',date('Y-m-01')) }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.end_date') }}</label><input type="date" name="to_date" value="{{ request('to_date',date('Y-m-d')) }}" class="fi"></div>
    <div class="md:col-span-2 flex gap-3 items-end">
      <button type="submit" class="btn-ac">{{ __('app.generate_report') }}</button>
      <button type="button" onclick="window.print()" class="btn-out">{{ __('app.export_pdf') }}</button>
    </div>
  </form>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_sales') }}</p><p class="kpi-value sm">{{ number_format($totalSales??0,2) }} DH</p></div><div class="kpi-icon" style="background:#dbeafe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.profit') }}</p><p class="kpi-value sm">{{ number_format($totalProfit??0,2) }} DH</p></div><div class="kpi-icon" style="background:#d1fae5"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_orders') }}</p><p class="kpi-value">{{ $totalOrders??0 }}</p></div><div class="kpi-icon" style="background:#ede9fe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8b5cf6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.average_order_value') }}</p><p class="kpi-value sm">{{ number_format($avgOrderValue??0,2) }} DH</p></div><div class="kpi-icon" style="background:#fef3c7"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#f59e0b"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185Z"/></svg></div></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"><h4 class="font-semibold text-gray-800 mb-4">{{ __('app.sales_chart') }}</h4><canvas id="salesTrendChart" height="120"></canvas></div>
  <div class="mt-wrap">
    <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9"><p class="font-semibold text-gray-800">{{ __('app.top_products') }}</p></div>
    <table class="mt">
      <thead><tr><th>{{ __('app.product') }}</th><th>{{ __('app.quantity') }}</th><th>{{ __('app.revenue') }}</th></tr></thead>
      <tbody>
      @foreach($topProducts??[] as $prod)
      <tr><td class="font-medium">{{ $prod->name }}</td><td>{{ $prod->quantity_sold }}</td><td class="font-semibold" style="color:{{ $accent }}">{{ number_format($prod->revenue,2) }} DH</td></tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('salesTrendChart'),{type:'line',data:{labels:@json($labels??[]),datasets:[{label:'{{ __('app.sales') }}',data:@json($salesData??[]),borderColor:'{{ $accent }}',backgroundColor:'{{ $accent }}22',tension:.4,fill:true}]},options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
</script>
@endpush
@endsection
