@extends('layouts.app')
@section('title', __('app.purchases_report'))
@php
$pageTitle  = __('app.purchases_report');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>__('app.purchases'),'url'=>'']];
$stats = $stats ?? ['count'=>0,'total'=>0,'paid'=>0,'due'=>0];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.purchases_report') }}</p><p class="pg-hero-sub">Track all purchase orders, paid amounts, and outstanding dues.</p></div>
</div>

<div class="rf">
  <form method="GET" class="grid grid-cols-2 md:grid-cols-4 gap-4 items-end">
    <div><label class="fi-label">{{ __('app.from_date') }}</label><input type="date" name="from_date" value="{{ request('from_date',date('Y-m-01')) }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.to_date') }}</label><input type="date" name="to_date" value="{{ request('to_date',date('Y-m-d')) }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.supplier') }}</label><select name="supplier_id" class="fi"><option value="">{{ __('app.all_suppliers') }}</option>@foreach($suppliers??[] as $s)<option value="{{ $s->id }}" {{ request('supplier_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach</select></div>
    <div class="flex gap-3">
      <button type="submit" class="btn-ac flex-1">{{ __('app.generate_report') }}</button>
      <button type="button" onclick="window.print()" class="btn-out">PDF</button>
    </div>
  </form>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_purchases') }}</p><p class="kpi-value">{{ $stats['count']??0 }}</p></div><div class="kpi-icon" style="background:#dbeafe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_amount') }}</p><p class="kpi-value sm">{{ number_format($stats['total']??0,2) }} DH</p></div><div class="kpi-icon" style="background:#d1fae5"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.paid_amount') }}</p><p class="kpi-value sm">{{ number_format($stats['paid']??0,2) }} DH</p></div><div class="kpi-icon" style="background:#fef3c7"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#f59e0b"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.due_amount') }}</p><p class="kpi-value sm">{{ number_format($stats['due']??0,2) }} DH</p></div><div class="kpi-icon" style="background:#fee2e2"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ef4444"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg></div></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"><h4 class="font-semibold text-gray-800 mb-4">{{ __('app.purchases_by_supplier') }}</h4><canvas id="supplierChart" height="180"></canvas></div>
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"><h4 class="font-semibold text-gray-800 mb-4">{{ __('app.monthly_trend') }}</h4><canvas id="trendChart" height="180"></canvas></div>
</div>

<div class="mt-wrap">
  <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9"><p class="font-semibold text-gray-800">{{ __('app.detailed_purchases') }}</p></div>
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>{{ __('app.date') }}</th><th>{{ __('app.reference') }}</th><th>{{ __('app.supplier') }}</th><th>{{ __('app.items') }}</th><th>{{ __('app.total') }}</th><th>{{ __('app.paid') }}</th><th>{{ __('app.due_amount') }}</th><th>{{ __('app.status') }}</th></tr></thead>
      <tbody>
      @forelse($purchases??[] as $purchase)
      <tr>
        <td class="text-sm">{{ $purchase->created_at->format('M d, Y') }}</td>
        <td class="font-semibold font-mono text-sm">{{ $purchase->reference }}</td>
        <td>{{ $purchase->supplier->name }}</td>
        <td>{{ $purchase->items_count??0 }}</td>
        <td class="font-semibold">{{ number_format($purchase->total,2) }} DH</td>
        <td class="text-green-600">{{ number_format($purchase->paid,2) }} DH</td>
        <td class="text-red-500">{{ number_format($purchase->total-$purchase->paid,2) }} DH</td>
        <td><span class="badge-ok">{{ __('app.'.$purchase->status) }}</span></td>
      </tr>
      @empty
      <tr><td colspan="8" class="text-center py-10 text-gray-400">{{ __('app.no_purchases_found_period') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const supplierCtx=document.getElementById('supplierChart').getContext('2d');
new Chart(supplierCtx,{type:'bar',data:{labels:{!! json_encode($chartData['suppliers']['labels']??[]) !!},datasets:[{label:'{{ __('app.total') }}',data:{!! json_encode($chartData['suppliers']['data']??[]) !!},backgroundColor:'{{ $accent }}'}]},options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
const trendCtx=document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx,{type:'line',data:{labels:{!! json_encode($chartData['months']??[]) !!},datasets:[{label:'{{ __('app.purchases') }}',data:{!! json_encode($chartData['monthly']??[]) !!},borderColor:'{{ $accent }}',backgroundColor:'{{ $accent }}22',tension:.4,fill:true}]},options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
</script>
@endpush
@endsection
