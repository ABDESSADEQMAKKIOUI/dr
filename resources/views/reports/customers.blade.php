@extends('layouts.app')
@section('title', __('app.customers_report'))
@php
$pageTitle  = __('app.customers_report');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>__('app.customers'),'url'=>'']];
$stats = $stats ?? ['total_customers'=>0,'total_sales'=>0,'avg_order'=>0,'outstanding'=>0];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.customers_report') }}</p><p class="pg-hero-sub">Customer sales, acquisition, and outstanding balance overview.</p></div>
</div>

<div class="rf">
  <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
    <div><label class="fi-label">{{ __('app.from_date') }}</label><input type="date" name="from_date" value="{{ request('from_date',date('Y-m-01')) }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.to_date') }}</label><input type="date" name="to_date" value="{{ request('to_date',date('Y-m-d')) }}" class="fi"></div>
    <div class="md:col-span-2 flex gap-3">
      <button type="submit" class="btn-ac">{{ __('app.generate_report') }}</button>
      <button type="button" onclick="window.print()" class="btn-out">PDF</button>
    </div>
  </form>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_customers') }}</p><p class="kpi-value">{{ $stats['total_customers']??0 }}</p></div><div class="kpi-icon" style="background:#dbeafe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_sales') }}</p><p class="kpi-value sm">{{ number_format($stats['total_sales']??0,2) }} DH</p></div><div class="kpi-icon" style="background:#d1fae5"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.avg_order_value') }}</p><p class="kpi-value sm">{{ number_format($stats['avg_order']??0,2) }} DH</p></div><div class="kpi-icon" style="background:#ede9fe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8b5cf6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.outstanding') }}</p><p class="kpi-value sm">{{ number_format($stats['outstanding']??0,2) }} DH</p></div><div class="kpi-icon" style="background:#fee2e2"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ef4444"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg></div></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"><h4 class="font-semibold text-gray-800 mb-4">{{ __('app.top_customers_by_sales') }}</h4><canvas id="topCustomersChart" height="200"></canvas></div>
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"><h4 class="font-semibold text-gray-800 mb-4">{{ __('app.customer_acquisition') }}</h4><canvas id="acquisitionChart" height="200"></canvas></div>
</div>

<div class="mt-wrap">
  <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9"><p class="font-semibold text-gray-800">{{ __('app.customer_details') }}</p></div>
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>{{ __('app.customer') }}</th><th>{{ __('app.email') }}</th><th>{{ __('app.phone') }}</th><th>{{ __('app.total_orders') }}</th><th>{{ __('app.total_sales') }}</th><th>{{ __('app.paid') }}</th><th>{{ __('app.outstanding') }}</th><th>{{ __('app.last_order') }}</th></tr></thead>
      <tbody>
      @forelse($customers??[] as $customer)
      <tr>
        <td class="font-medium">{{ $customer->name }}</td>
        <td class="text-sm">{{ $customer->email }}</td>
        <td class="text-sm">{{ $customer->phone }}</td>
        <td><span class="badge-info">{{ $customer->orders_count??0 }}</span></td>
        <td class="font-semibold text-green-600">{{ number_format($customer->total_sales??0,2) }} DH</td>
        <td class="text-green-600">{{ number_format($customer->paid??0,2) }} DH</td>
        <td class="font-semibold text-red-500">{{ number_format(($customer->total_sales??0)-($customer->paid??0),2) }} DH</td>
        <td class="text-sm text-gray-500">{{ $customer->last_order_date?$customer->last_order_date->format('M d, Y'):'-' }}</td>
      </tr>
      @empty
      <tr><td colspan="8" class="text-center py-10 text-gray-400">{{ __('app.no_customer_data') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('topCustomersChart'),{type:'bar',data:{labels:{!! json_encode($chartData['top_customers']['labels']??[]) !!},datasets:[{label:'{{ __('app.sales') }} (DH)',data:{!! json_encode($chartData['top_customers']['data']??[]) !!},backgroundColor:'{{ $accent }}'}]},options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
new Chart(document.getElementById('acquisitionChart'),{type:'line',data:{labels:{!! json_encode($chartData['acquisition']['labels']??[]) !!},datasets:[{label:'New Customers',data:{!! json_encode($chartData['acquisition']['data']??[]) !!},borderColor:'{{ $accent }}',backgroundColor:'{{ $accent }}22',tension:.4,fill:true}]},options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
</script>
@endpush
@endsection
