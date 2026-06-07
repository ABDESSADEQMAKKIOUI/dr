@extends('layouts.app')
@section('title', __('app.supplier_detail_report'))
@php
$pageTitle = __('app.supplier').': '.$supplier->name;
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>__('app.supplier_detail'),'url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg></div>
  <div><p class="pg-hero-title">{{ $supplier->name }}</p><p class="pg-hero-sub">{{ __('app.supplier_detail_report') }}</p></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_purchases') }}</p><p class="kpi-value sm">{{ number_format($totalPurchases,2) }} DH</p></div><div class="kpi-icon" style="background:#dbeafe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_paid') }}</p><p class="kpi-value sm">{{ number_format($totalPaid,2) }} DH</p></div><div class="kpi-icon" style="background:#d1fae5"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.outstanding_due') }}</p><p class="kpi-value sm">{{ number_format($totalDue,2) }} DH</p></div><div class="kpi-icon" style="background:#fee2e2"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ef4444"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_orders') }}</p><p class="kpi-value">{{ $totalOrders }}</p></div><div class="kpi-icon" style="background:#ede9fe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8b5cf6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/></svg></div></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="mc">
    <div class="mc-head"><div class="mc-icon" style="background:color-mix(in srgb,var(--accent) 12%,#fff)"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="var(--accent)" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg></div><div><p class="mc-head-title">{{ __('app.supplier_info') }}</p></div></div>
    <div class="mc-body" style="gap:.625rem">
      <div style="display:flex;justify-content:space-between;font-size:.875rem"><span class="text-gray-500">{{ __('app.name') }}</span><span class="font-medium">{{ $supplier->name }}</span></div>
      <div style="display:flex;justify-content:space-between;font-size:.875rem"><span class="text-gray-500">{{ __('app.email') }}</span><span>{{ $supplier->email?:'—' }}</span></div>
      <div style="display:flex;justify-content:space-between;font-size:.875rem"><span class="text-gray-500">{{ __('app.phone') }}</span><span>{{ $supplier->phone?:'—' }}</span></div>
      <div style="display:flex;justify-content:space-between;font-size:.875rem"><span class="text-gray-500">{{ __('app.city') }}</span><span>{{ $supplier->city?:'—' }}</span></div>
      <div style="display:flex;justify-content:space-between;font-size:.875rem"><span class="text-gray-500">{{ __('app.partner_since') }}</span><span>{{ $supplier->created_at->format('d/m/Y') }}</span></div>
    </div>
  </div>

  <div class="lg:col-span-2 mt-wrap" style="margin-bottom:0">
    <div style="padding:.75rem 1.25rem;background:linear-gradient(135deg,var(--accent),color-mix(in srgb,var(--accent) 70%,#000));border-radius:.75rem .75rem 0 0"><span style="color:#fff;font-weight:600">{{ __('app.recent_purchases') }}</span></div>
    <div class="overflow-x-auto">
      <table class="mt" style="border-radius:0">
        <thead><tr><th>{{ __('app.reference') }}</th><th>{{ __('app.date') }}</th><th>{{ __('app.total') }}</th><th>{{ __('app.paid') }}</th><th>{{ __('app.due') }}</th><th>{{ __('app.status') }}</th></tr></thead>
        <tbody>
        @forelse($recentPurchases as $purchase)
        <tr>
          <td><a href="{{ route('purchases.show',$purchase) }}" class="font-mono text-sm" style="color:var(--accent)">{{ $purchase->reference }}</a></td>
          <td class="text-sm">{{ \Carbon\Carbon::parse($purchase->date)->format('d/m/Y') }}</td>
          <td>{{ number_format($purchase->total_amount,2) }} DH</td>
          <td class="text-green-600">{{ number_format($purchase->paid_amount,2) }} DH</td>
          <td class="{{ ($purchase->total_amount-$purchase->paid_amount)>0?'text-red-500':'text-gray-400' }}">{{ number_format($purchase->total_amount-$purchase->paid_amount,2) }} DH</td>
          <td>@if($purchase->payment_status==='paid')<span class="badge-ok">{{ __('app.paid') }}</span>@elseif($purchase->payment_status==='partial')<span class="badge-warn">{{ __('app.partial') }}</span>@else<span class="badge-danger">{{ __('app.unpaid') }}</span>@endif</td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center py-10 text-gray-400">{{ __('app.no_purchases_found') }}</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>
@endsection
