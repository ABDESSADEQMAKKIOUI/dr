@extends('layouts.app')
@section('title', __('app.dashboard'))
@php
$accent   = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
$currency = \App\Models\Setting::where('key','currency_symbol')->value('value') ?? 'DH';

$sales     = $stats['sales']     ?? [];
$purchases = $stats['purchases'] ?? [];
$revenue   = $stats['revenue']   ?? [];
$expenses  = $stats['expenses']  ?? [];
$profit    = $stats['profit']    ?? [];
$unpaid    = $stats['invoices_unpaid']    ?? [];
$lowStock  = $stats['low_stock_products'] ?? [];
$topProds  = $stats['top_products']       ?? [];

$growth = $revenue['growth_percentage'] ?? 0;
$growthUp = $growth >= 0;

$periods = ['today' => __('app.today'), 'week' => __('app.this_week'), 'month' => __('app.this_month'), 'year' => __('app.this_year')];
@endphp

@push('styles')
<style>
:root{--accent:{{ $accent }}}

/* ─── Period tabs ─── */
.period-tabs{display:flex;gap:.375rem;background:#f1f5f9;border-radius:.75rem;padding:.25rem}
.period-tab{padding:.375rem .875rem;border-radius:.625rem;font-size:.8125rem;font-weight:600;color:#64748b;cursor:pointer;border:none;background:transparent;transition:all .18s}
.period-tab.active,.period-tab:focus-visible{background:#fff;color:var(--accent);box-shadow:0 1px 4px rgba(0,0,0,.10);outline:none}
.period-tab:hover:not(.active){color:#334155;background:#e8edf3}

/* ─── KPI growth badge ─── */
.kpi-growth{display:inline-flex;align-items:center;gap:.2rem;font-size:.75rem;font-weight:700;border-radius:20px;padding:.15rem .55rem;margin-top:.4rem}
.kpi-growth.up{background:#d1fae5;color:#065f46}
.kpi-growth.down{background:#fee2e2;color:#991b1b}

/* ─── Chart card ─── */
.ch-card{background:#fff;border-radius:1rem;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #f1f5f9;overflow:hidden}
.ch-head{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;border-bottom:1px solid #f1f5f9}
.ch-title{font-size:.9375rem;font-weight:700;color:#0f172a}
.ch-body{padding:1.25rem 1.5rem}

/* ─── List rows ─── */
.list-row{display:flex;align-items:center;justify-content:space-between;padding:.75rem 1.5rem;border-bottom:1px solid #f8fafc}
.list-row:last-child{border-bottom:none}
.list-row:hover{background:#f8fafc}
.list-row-icon{width:2.25rem;height:2.25rem;border-radius:.625rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-right:.75rem}
.list-row-icon svg{width:1.1rem;height:1.1rem}

/* ─── Quick actions ─── */
.qa-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:.75rem}
.qa-btn{display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:1rem .75rem;border-radius:.875rem;background:#f8fafc;border:1px solid #e2e8f0;cursor:pointer;text-decoration:none;transition:all .18s;color:#374151;font-size:.8125rem;font-weight:600;text-align:center}
.qa-btn:hover{background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 4px 12px color-mix(in srgb,var(--accent) 30%,transparent);transform:translateY(-2px)}
.qa-btn svg{width:1.4rem;height:1.4rem;transition:color .18s}

/* ─── Overdue ring ─── */
.overdue-ring{display:inline-flex;align-items:center;justify-content:center;width:2rem;height:2rem;border-radius:50%;background:#fee2e2;color:#991b1b;font-size:.75rem;font-weight:800}
</style>
@endpush

@section('content')
<div style="--accent:{{ $accent }}">

{{-- ═══ HERO + PERIOD ═══ --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
  <div class="pg-hero" style="margin-bottom:0">
    <div class="pg-hero-icon">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
    </div>
    <div>
      <p class="pg-hero-title">{{ __('app.dashboard') }}</p>
      <p class="pg-hero-sub">{{ now()->format('l, d F Y') }}</p>
    </div>
  </div>

  {{-- Period selector --}}
  <form method="GET" action="{{ route('dashboard') }}" id="periodForm">
    <div class="period-tabs">
      @foreach($periods as $key => $label)
        <button type="submit" name="period" value="{{ $key }}"
                class="period-tab {{ $period === $key ? 'active' : '' }}">{{ $label }}</button>
      @endforeach
    </div>
  </form>
</div>

{{-- ═══ ROW 1 — PRIMARY KPIs ═══ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

  {{-- Revenue --}}
  <div class="kpi">
    <div>
      <p class="kpi-label">{{ __('app.total_revenue') }}</p>
      <p class="kpi-value">{{ number_format($revenue['total'] ?? 0, 0) }}</p>
      <p style="font-size:.7rem;color:#94a3b8;margin-top:.1rem">{{ $currency }}</p>
      <span class="kpi-growth {{ $growthUp ? 'up' : 'down' }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:.85rem;height:.85rem">
          <path stroke-linecap="round" stroke-linejoin="round" d="{{ $growthUp ? 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941' : 'M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181' }}"/>
        </svg>
        {{ number_format(abs($growth), 1) }}%
      </span>
    </div>
    <div class="kpi-icon" style="background:{{ $accent }}1a">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
    </div>
  </div>

  {{-- Sales --}}
  <div class="kpi">
    <div>
      <p class="kpi-label">{{ __('app.total_sales') }}</p>
      <p class="kpi-value">{{ number_format($sales['total'] ?? 0, 0) }}</p>
      <p style="font-size:.7rem;color:#94a3b8;margin-top:.1rem">{{ ($sales['count'] ?? 0) }} {{ __('app.orders') }}</p>
    </div>
    <div class="kpi-icon" style="background:#dbeafe">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
    </div>
  </div>

  {{-- Purchases --}}
  <div class="kpi">
    <div>
      <p class="kpi-label">{{ __('app.total_purchases') }}</p>
      <p class="kpi-value">{{ number_format($purchases['total'] ?? 0, 0) }}</p>
      <p style="font-size:.7rem;color:#94a3b8;margin-top:.1rem">{{ ($purchases['count'] ?? 0) }} {{ __('app.orders') }}</p>
    </div>
    <div class="kpi-icon" style="background:#d1fae5">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
    </div>
  </div>

  {{-- Net Profit --}}
  @php $profitVal = $profit['total'] ?? 0; $profitPos = $profitVal >= 0; @endphp
  <div class="kpi">
    <div>
      <p class="kpi-label">{{ __('app.net_profit') }}</p>
      <p class="kpi-value" style="color:{{ $profitPos ? '#059669' : '#dc2626' }}">{{ number_format(abs($profitVal), 0) }}</p>
      <p style="font-size:.7rem;color:#94a3b8;margin-top:.1rem">{{ number_format($profit['margin_percentage'] ?? 0, 1) }}% {{ __('app.profit_margin') }}</p>
    </div>
    <div class="kpi-icon" style="background:{{ $profitPos ? '#d1fae5' : '#fee2e2' }}">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $profitPos ? '#059669' : '#dc2626' }}"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
    </div>
  </div>
</div>

{{-- ═══ ROW 2 — SECONDARY KPIs ═══ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

  {{-- Expenses --}}
  <div class="kpi">
    <div>
      <p class="kpi-label">{{ __('app.expenses') }}</p>
      <p class="kpi-value sm">{{ number_format($expenses['total'] ?? 0, 0) }} <span style="font-size:.8rem;font-weight:500;color:#94a3b8">{{ $currency }}</span></p>
    </div>
    <div class="kpi-icon" style="background:#fef3c7">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#d97706"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
    </div>
  </div>

  {{-- Unpaid invoices --}}
  <div class="kpi">
    <div>
      <p class="kpi-label">{{ __('app.unpaid_invoices') }}</p>
      <p class="kpi-value sm">{{ $unpaid['count'] ?? 0 }} <span style="font-size:.8rem;font-weight:500;color:#94a3b8">invoices</span></p>
      <p style="font-size:.7rem;color:#94a3b8;margin-top:.1rem">{{ number_format($unpaid['total_amount'] ?? 0, 0) }} {{ $currency }}</p>
    </div>
    <div class="kpi-icon" style="background:#ede9fe">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#7c3aed"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
    </div>
  </div>

  {{-- Overdue --}}
  <div class="kpi">
    <div>
      <p class="kpi-label">{{ __('app.overdue') }}</p>
      <p class="kpi-value sm" style="color:#dc2626">{{ $unpaid['overdue_count'] ?? 0 }} <span style="font-size:.8rem;font-weight:500;color:#94a3b8">invoices</span></p>
      <p style="font-size:.7rem;color:#dc2626;margin-top:.1rem">{{ number_format($unpaid['overdue_amount'] ?? 0, 0) }} {{ $currency }}</p>
    </div>
    <div class="kpi-icon" style="background:#fee2e2">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#dc2626"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
    </div>
  </div>

  {{-- Low stock --}}
  <div class="kpi">
    <div>
      <p class="kpi-label">{{ __('app.low_stock_products') }}</p>
      <p class="kpi-value sm" style="{{ ($lowStock['count'] ?? 0) > 0 ? 'color:#d97706' : '' }}">{{ $lowStock['count'] ?? 0 }}</p>
      <p style="font-size:.7rem;color:#94a3b8;margin-top:.1rem">{{ __('app.products') }} {{ __('app.below_alert') }}</p>
    </div>
    <div class="kpi-icon" style="background:#fef3c7">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#d97706"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
    </div>
  </div>
</div>

{{-- ═══ CHARTS ROW ═══ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

  {{-- Sales trend chart (2/3 width) --}}
  <div class="ch-card lg:col-span-2">
    <div class="ch-head">
      <p class="ch-title">{{ __('app.sales_overview') }}</p>
      <span class="badge-info" id="chartPeriodLabel">{{ $periods[$period] }}</span>
    </div>
    <div class="ch-body">
      <div style="height:260px;position:relative">
        <canvas id="salesChart"></canvas>
      </div>
    </div>
  </div>

  {{-- Revenue vs Expenses doughnut (1/3 width) --}}
  <div class="ch-card">
    <div class="ch-head">
      <p class="ch-title">{{ __('app.revenue_vs_expenses') }}</p>
    </div>
    <div class="ch-body" style="display:flex;flex-direction:column;align-items:center">
      <div style="height:200px;width:200px;position:relative">
        <canvas id="donutChart"></canvas>
      </div>
      <div style="width:100%;margin-top:1rem;display:flex;flex-direction:column;gap:.5rem" id="donutLegend">
        <div style="display:flex;align-items:center;justify-content:space-between;font-size:.8125rem">
          <span style="display:flex;align-items:center;gap:.4rem"><span style="width:.65rem;height:.65rem;border-radius:50%;background:#10b981;display:inline-block"></span> {{ __('app.revenue') }}</span>
          <strong id="lg-revenue">—</strong>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;font-size:.8125rem">
          <span style="display:flex;align-items:center;gap:.4rem"><span style="width:.65rem;height:.65rem;border-radius:50%;background:#f59e0b;display:inline-block"></span> {{ __('app.purchases') }}</span>
          <strong id="lg-purchases">—</strong>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;font-size:.8125rem">
          <span style="display:flex;align-items:center;gap:.4rem"><span style="width:.65rem;height:.65rem;border-radius:50%;background:#ef4444;display:inline-block"></span> {{ __('app.expenses') }}</span>
          <strong id="lg-expenses">—</strong>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ═══ BOTTOM ROW ═══ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

  {{-- Top Products (2/3) --}}
  <div class="mt-wrap lg:col-span-2">
    <div class="ch-head" style="padding:1rem 1.5rem;border-bottom:1px solid #f1f5f9">
      <p class="ch-title">{{ __('app.top_selling_products') }}</p>
      <a href="{{ route('reports.top-products') }}" class="badge-info" style="text-decoration:none;font-size:.75rem">{{ __('app.view_all') }}</a>
    </div>
    <table class="mt">
      <thead>
        <tr>
          <th>#</th>
          <th>{{ __('app.product') }}</th>
          <th>{{ __('app.quantity') }}</th>
          <th>{{ __('app.revenue') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse($topProds as $i => $p)
        @php $prod = (object)$p; @endphp
        <tr>
          <td>
            <span style="width:1.6rem;height:1.6rem;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:800;
              background:{{ $i===0 ? '#fef3c7' : ($i===1 ? '#f1f5f9' : ($i===2 ? '#fde8d4' : '#f8fafc')) }};
              color:{{ $i===0 ? '#92400e' : ($i===1 ? '#475569' : ($i===2 ? '#9a3412' : '#64748b')) }}">
              {{ $i+1 }}
            </span>
          </td>
          <td class="font-medium">{{ $prod->name }}</td>
          <td><span class="badge-info">{{ number_format($prod->total_quantity, 0) }}</span></td>
          <td style="font-weight:700;color:#059669">{{ number_format($prod->total_revenue, 0) }} {{ $currency }}</td>
        </tr>
        @empty
        <tr><td colspan="4" class="text-center py-10" style="color:#94a3b8">{{ __('app.no_data') }}</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Low Stock Alert (1/3) --}}
  <div class="mt-wrap">
    <div class="ch-head" style="padding:1rem 1.5rem;border-bottom:1px solid #f1f5f9">
      <p class="ch-title" style="display:flex;align-items:center;gap:.5rem">
        {{ __('app.low_stock_alert') }}
        @if(($lowStock['count'] ?? 0) > 0)
          <span class="overdue-ring">{{ $lowStock['count'] }}</span>
        @endif
      </p>
      <a href="{{ route('stock.alerts.index') }}" class="badge-warn" style="text-decoration:none;font-size:.75rem">{{ __('app.view_all') }}</a>
    </div>
    @forelse($lowStock['products'] ?? [] as $lp)
    @php $lp = (object)$lp; @endphp
    <div class="list-row">
      <div style="display:flex;align-items:center">
        <div class="list-row-icon" style="background:#fef3c7">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#d97706"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
        </div>
        <div>
          <p style="font-size:.8125rem;font-weight:600;color:#1e293b">{{ $lp->name }}</p>
          <p style="font-size:.75rem;color:#94a3b8">Alert: {{ $lp->stock_alert }}</p>
        </div>
      </div>
      <span class="badge-danger">{{ $lp->stock_quantity }}</span>
    </div>
    @empty
    <div style="padding:2.5rem 1.5rem;text-align:center;color:#94a3b8;font-size:.875rem">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:2rem;height:2rem;margin:0 auto .5rem;display:block;color:#cbd5e1"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
      {{ __('app.all_stock_ok') }}
    </div>
    @endforelse
  </div>
</div>

{{-- ═══ QUICK ACTIONS ═══ --}}
<div class="mc">
  <div class="mc-head">
    <div class="mc-icon" style="background:{{ $accent }}1a">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/></svg>
    </div>
    <div><p class="mc-head-title">{{ __('app.quick_actions') }}</p><p class="mc-head-sub">{{ __('app.frequent_tasks_desc') ?? 'Frequent tasks at a glance' }}</p></div>
  </div>
  <div class="mc-body-lg">
    <div class="qa-grid">
      @can('sales.create')
      <a href="{{ route('sales.create') }}" class="qa-btn">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        {{ __('app.new_sale') }}
      </a>
      @endcan
      @can('purchases.create')
      <a href="{{ route('purchases.create') }}" class="qa-btn">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.244-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
        {{ __('app.new_purchase') }}
      </a>
      @endcan
      @can('invoices.create')
      <a href="{{ route('invoices.create') }}" class="qa-btn">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/></svg>
        {{ __('app.new_invoice') }}
      </a>
      @endcan
      @can('quotations.create')
      <a href="{{ route('quotations.create') }}" class="qa-btn">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/></svg>
        {{ __('app.new_quotation') }}
      </a>
      @endcan
      @can('customers.create')
      <a href="{{ route('customers.create') }}" class="qa-btn">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
        {{ __('app.new_customer') }}
      </a>
      @endcan
      @can('products.create')
      <a href="{{ route('products.create') }}" class="qa-btn">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
        {{ __('app.new_product') }}
      </a>
      @endcan
      @can('expenses.create')
      <a href="{{ route('expenses.create') }}" class="qa-btn">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
        {{ __('app.new_expense') }}
      </a>
      @endcan
      @can('sales.pos')
      <a href="{{ route('sales.pos') }}" class="qa-btn">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/></svg>
        {{ __('app.pos') }}
      </a>
      @endcan
    </div>
  </div>
</div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ACCENT = '{{ $accent }}';
const PERIOD = '{{ $period }}';

// ─── Sales Line Chart ───────────────────────────────────────────────
const salesCtx = document.getElementById('salesChart');
if (salesCtx) {
  fetch(`/dashboard/sales-chart?period=${PERIOD}`)
    .then(r => r.json())
    .then(({ data }) => {
      const labels = data.map(d => d.period);
      const values = data.map(d => parseFloat(d.total));

      new Chart(salesCtx, {
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: '{{ __('app.sales') }}',
            data: values,
            borderColor: ACCENT,
            backgroundColor: ACCENT + '18',
            borderWidth: 2.5,
            pointBackgroundColor: ACCENT,
            pointRadius: 4,
            pointHoverRadius: 6,
            tension: 0.4,
            fill: true,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#0f172a',
              titleColor: '#94a3b8',
              bodyColor: '#f8fafc',
              padding: 12,
              callbacks: {
                label: ctx => ' ' + ctx.parsed.y.toLocaleString() + ' {{ $currency }}'
              }
            }
          },
          scales: {
            x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 } } },
            y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 11 } } }
          }
        }
      });
    })
    .catch(() => {
      // silent — no chart data available
    });
}

// ─── Revenue vs Expenses Doughnut ───────────────────────────────────
const donutCtx = document.getElementById('donutChart');
if (donutCtx) {
  fetch(`/dashboard/revenue-vs-expenses?period=${PERIOD}`)
    .then(r => r.json())
    .then(({ data }) => {
      const rev = parseFloat(data.revenue) || 0;
      const pur = parseFloat(data.purchases) || 0;
      const exp = parseFloat(data.expenses) || 0;

      document.getElementById('lg-revenue').textContent   = rev.toLocaleString() + ' {{ $currency }}';
      document.getElementById('lg-purchases').textContent = pur.toLocaleString() + ' {{ $currency }}';
      document.getElementById('lg-expenses').textContent  = exp.toLocaleString() + ' {{ $currency }}';

      new Chart(donutCtx, {
        type: 'doughnut',
        data: {
          labels: ['{{ __('app.revenue') }}', '{{ __('app.purchases') }}', '{{ __('app.expenses') }}'],
          datasets: [{
            data: [rev || 0.001, pur || 0.001, exp || 0.001],
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
            borderWidth: 3,
            borderColor: '#ffffff',
            hoverOffset: 8,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '68%',
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#0f172a',
              titleColor: '#94a3b8',
              bodyColor: '#f8fafc',
              padding: 10,
              callbacks: {
                label: ctx => ' ' + ctx.parsed.toLocaleString() + ' {{ $currency }}'
              }
            }
          }
        }
      });
    })
    .catch(() => {});
}
</script>
@endpush
