@extends('layouts.app')
@section('title', __('app.customer_detail_report'))
@php
$pageTitle = __('app.customer').': '.$customer->name;
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>__('app.customer_detail'),'url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg></div>
  <div><p class="pg-hero-title">{{ $customer->name }}</p><p class="pg-hero-sub">{{ __('app.customer_detail_report') }}</p></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_sales') }}</p><p class="kpi-value sm">{{ number_format($totalSales,2) }} DH</p></div><div class="kpi-icon" style="background:#dbeafe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_paid') }}</p><p class="kpi-value sm">{{ number_format($totalPaid,2) }} DH</p></div><div class="kpi-icon" style="background:#d1fae5"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.outstanding_due') }}</p><p class="kpi-value sm">{{ number_format($totalDue,2) }} DH</p></div><div class="kpi-icon" style="background:#fee2e2"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ef4444"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_orders') }}</p><p class="kpi-value">{{ $totalOrders }}</p></div><div class="kpi-icon" style="background:#ede9fe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8b5cf6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/></svg></div></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="mc">
    <div class="mc-head"><div class="mc-icon" style="background:color-mix(in srgb,var(--accent) 12%,#fff)"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="var(--accent)" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg></div><div><p class="mc-head-title">{{ __('app.customer_info') }}</p></div></div>
    <div class="mc-body" style="gap:.625rem">
      <div style="display:flex;justify-content:space-between;font-size:.875rem"><span class="text-gray-500">{{ __('app.name') }}</span><span class="font-medium">{{ $customer->name }}</span></div>
      <div style="display:flex;justify-content:space-between;font-size:.875rem"><span class="text-gray-500">{{ __('app.email') }}</span><span>{{ $customer->email?:'—' }}</span></div>
      <div style="display:flex;justify-content:space-between;font-size:.875rem"><span class="text-gray-500">{{ __('app.phone') }}</span><span>{{ $customer->phone?:'—' }}</span></div>
      <div style="display:flex;justify-content:space-between;font-size:.875rem"><span class="text-gray-500">{{ __('app.city') }}</span><span>{{ $customer->city?:'—' }}</span></div>
      <div style="display:flex;justify-content:space-between;font-size:.875rem"><span class="text-gray-500">{{ __('app.customer_since') }}</span><span>{{ $customer->created_at->format('d/m/Y') }}</span></div>
    </div>
  </div>

  <div class="lg:col-span-2 mt-wrap" style="margin-bottom:0">
    <div style="padding:.75rem 1.25rem;background:linear-gradient(135deg,var(--accent),color-mix(in srgb,var(--accent) 70%,#000));border-radius:.75rem .75rem 0 0"><span style="color:#fff;font-weight:600">{{ __('app.recent_sales') }}</span></div>
    <div class="overflow-x-auto">
      <table class="mt" style="border-radius:0">
        <thead><tr><th>{{ __('app.reference') }}</th><th>{{ __('app.date') }}</th><th>{{ __('app.total') }}</th><th>{{ __('app.paid') }}</th><th>{{ __('app.due') }}</th><th>{{ __('app.status') }}</th></tr></thead>
        <tbody>
        @forelse($recentSales as $sale)
        <tr>
          <td><a href="{{ route('sales.show',$sale) }}" class="font-mono text-sm" style="color:var(--accent)">{{ $sale->reference }}</a></td>
          <td class="text-sm">{{ \Carbon\Carbon::parse($sale->date)->format('d/m/Y') }}</td>
          <td>{{ number_format($sale->total_amount,2) }} DH</td>
          <td class="text-green-600">{{ number_format($sale->paid_amount,2) }} DH</td>
          <td class="{{ ($sale->total_amount-$sale->paid_amount)>0?'text-red-500':'text-gray-400' }}">{{ number_format($sale->total_amount-$sale->paid_amount,2) }} DH</td>
          <td>@if($sale->payment_status==='paid')<span class="badge-ok">{{ __('app.paid') }}</span>@elseif($sale->payment_status==='partial')<span class="badge-warn">{{ __('app.partial') }}</span>@else<span class="badge-danger">{{ __('app.unpaid') }}</span>@endif</td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center py-10 text-gray-400">{{ __('app.no_sales_found') }}</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>
@endsection
