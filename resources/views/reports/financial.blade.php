@extends('layouts.app')
@section('title', __('app.financial_report'))
@php
$pageTitle  = __('app.financial_report');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>__('app.financial'),'url'=>'']];
$stats = $stats ?? ['revenue'=>0,'expenses'=>0,'sales_revenue'=>0,'purchase_costs'=>0,'operating_expenses'=>0];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.financial_report') }}</p><p class="pg-hero-sub">Revenue, expenses, profit margin, and cash flow overview.</p></div>
</div>

<div class="rf">
  <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
    <div><label class="fi-label">{{ __('app.from_date') }}</label><input type="date" name="from_date" value="{{ request('from_date',date('Y-m-01')) }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.to_date') }}</label><input type="date" name="to_date" value="{{ request('to_date',date('Y-m-d')) }}" class="fi"></div>
    <div class="md:col-span-2 flex gap-3">
      <button type="submit" class="btn-ac">{{ __('app.generate_report') }}</button>
      <button type="button" onclick="window.print()" class="btn-out">{{ __('app.export_pdf') }}</button>
    </div>
  </form>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_revenue') }}</p><p class="kpi-value sm">{{ number_format($stats['revenue']??0,2) }} DH</p></div><div class="kpi-icon" style="background:#d1fae5"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_expenses') }}</p><p class="kpi-value sm">{{ number_format($stats['expenses']??0,2) }} DH</p></div><div class="kpi-icon" style="background:#fee2e2"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ef4444"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.net_profit') }}</p><p class="kpi-value sm">{{ number_format(($stats['revenue']??0)-($stats['expenses']??0),2) }} DH</p></div><div class="kpi-icon" style="background:#dbeafe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.profit_margin') }}</p><p class="kpi-value">{{ ($stats['revenue']??0)>0?number_format(((($stats['revenue']??0)-($stats['expenses']??0))/($stats['revenue']??1))*100,1):0 }}%</p></div><div class="kpi-icon" style="background:#ede9fe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8b5cf6"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z"/></svg></div></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"><h4 class="font-semibold text-gray-800 mb-4">{{ __('app.revenue_vs_expenses') }}</h4><canvas id="revenueExpensesChart" height="180"></canvas></div>
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"><h4 class="font-semibold text-gray-800 mb-4">{{ __('app.profit_trend') }}</h4><canvas id="profitChart" height="180"></canvas></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
    <h4 class="font-semibold text-gray-800 mb-4">{{ __('app.income_breakdown') }}</h4>
    <div class="space-y-2">
      @foreach($incomeBreakdown??[] as $item)
      <div class="flex justify-between items-center py-2 border-b border-gray-50 last:border-0">
        <span class="text-sm text-gray-600">{{ $item['name'] }}</span>
        <span class="font-semibold text-green-600 text-sm">{{ number_format($item['amount'],2) }} DH</span>
      </div>
      @endforeach
    </div>
  </div>
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
    <h4 class="font-semibold text-gray-800 mb-4">{{ __('app.expense_breakdown') }}</h4>
    <div class="space-y-2">
      @foreach($expenseBreakdown??[] as $item)
      <div class="flex justify-between items-center py-2 border-b border-gray-50 last:border-0">
        <span class="text-sm text-gray-600">{{ $item['name'] }}</span>
        <span class="font-semibold text-red-500 text-sm">{{ number_format($item['amount'],2) }} DH</span>
      </div>
      @endforeach
    </div>
  </div>
</div>

<div class="mt-wrap">
  <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9"><p class="font-semibold text-gray-800">{{ __('app.cash_flow_summary') }}</p></div>
  <table class="mt">
    <thead><tr><th>{{ __('app.description') }}</th><th>{{ __('app.amount') }}</th></tr></thead>
    <tbody>
      <tr><td class="font-medium">{{ __('app.sales_revenue') }}</td><td class="font-semibold text-green-600">{{ number_format($stats['sales_revenue']??0,2) }} DH</td></tr>
      <tr><td class="font-medium">{{ __('app.purchase_costs') }}</td><td class="text-red-500">-{{ number_format($stats['purchase_costs']??0,2) }} DH</td></tr>
      <tr><td class="font-medium">{{ __('app.operating_expenses') }}</td><td class="text-red-500">-{{ number_format($stats['operating_expenses']??0,2) }} DH</td></tr>
      <tr style="background:#f8fafc"><td class="font-bold">{{ __('app.net_cash_flow') }}</td><td class="font-bold" style="color:{{ $accent }}">{{ number_format(($stats['sales_revenue']??0)-($stats['purchase_costs']??0)-($stats['operating_expenses']??0),2) }} DH</td></tr>
    </tbody>
  </table>
</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('revenueExpensesChart'),{type:'bar',data:{labels:{!! json_encode($chartData['months']??[]) !!},datasets:[{label:'{{ __('app.revenue') }}',data:{!! json_encode($chartData['revenue']??[]) !!},backgroundColor:'#10B981'},{label:'{{ __('app.expenses') }}',data:{!! json_encode($chartData['expenses']??[]) !!},backgroundColor:'#EF4444'}]},options:{responsive:true,maintainAspectRatio:true,scales:{y:{beginAtZero:true}}}});
new Chart(document.getElementById('profitChart'),{type:'line',data:{labels:{!! json_encode($chartData['months']??[]) !!},datasets:[{label:'{{ __('app.net_profit') }}',data:{!! json_encode($chartData['profit']??[]) !!},borderColor:'{{ $accent }}',backgroundColor:'{{ $accent }}22',tension:.4,fill:true}]},options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
</script>
@endpush
@endsection
