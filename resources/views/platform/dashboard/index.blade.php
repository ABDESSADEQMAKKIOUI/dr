@extends('layouts.platform')
@section('title', __('app.dashboard'))

@php
    /**
     * Payload from App\Http\Controllers\Platform\DashboardController@index:
     *   $tenantsByStatus, $tenantsTotal, $activeSubscriptions, $mrr,
     *   $expiringSoon, $recentAudits, $plansCount, $newLeads, $recentLeads
     */
    $byStatus = $tenantsByStatus ?? [];
    $total    = $tenantsTotal    ?? 0;
    $expiring = $expiringSoon    ?? collect();
    $audits   = $recentAudits    ?? collect();
    $leads    = $recentLeads     ?? collect();

    $leadBadges = [
        'new'       => 'badge-primary',
        'contacted' => 'badge-info',
        'qualified' => 'badge-warning',
        'converted' => 'badge-success',
        'rejected'  => 'badge-danger',
    ];

    $countOf = fn (string $key) => (int) ($byStatus[$key] ?? 0);

    $statusColors = [
        'active'       => ['#d1fae5', '#059669'],
        'provisioning' => ['#dbeafe', '#2563eb'],
        'suspended'    => ['#fef3c7', '#d97706'],
        'expired'      => ['#fee2e2', '#dc2626'],
        'failed'       => ['#fee2e2', '#dc2626'],
        'archived'     => ['#f1f5f9', '#64748b'],
    ];

    $breakdownMax = max(1, $byStatus ? max($byStatus) : 1);
@endphp

@push('styles')
<style>
/* Quick-action tiles (page-local, exactly like the ERP dashboard) */
.qa-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.75rem}
.qa-btn{display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:1rem .75rem;border-radius:.875rem;background:#f8fafc;border:1px solid #e2e8f0;cursor:pointer;text-decoration:none;transition:all .18s;color:#374151;font-size:.8125rem;font-weight:600;text-align:center}
.qa-btn:hover{background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 4px 12px color-mix(in srgb,var(--accent) 30%,transparent);transform:translateY(-2px)}
.qa-btn svg{width:1.4rem;height:1.4rem;transition:color .18s}
/* Status breakdown bars */
.sb-row{display:flex;align-items:center;gap:.75rem;padding:.5rem 0}
.sb-name{font-size:.8125rem;color:#475569;width:7.5rem;flex-shrink:0}
.sb-track{flex:1;height:.5rem;border-radius:999px;background:#f1f5f9;overflow:hidden;display:block}
.sb-fill{height:100%;border-radius:999px;display:block}
.sb-val{font-size:.8125rem;font-weight:700;color:#0f172a;width:2.5rem;text-align:right}
/* Compact inbound-lead list */
.ld-row{display:flex;align-items:center;gap:.75rem;padding:.5rem 0;border-bottom:1px solid #f1f5f9;text-decoration:none}
.ld-row:last-child{border-bottom:none}
.ld-row:hover .ld-name{color:var(--accent)}
.ld-main{flex:1;min-width:0}
.ld-name{display:block;font-size:.8125rem;font-weight:600;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;transition:color .15s}
.ld-sub{display:block;font-size:.75rem;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.ld-date{font-size:.75rem;color:#94a3b8;flex-shrink:0}
</style>
@endpush

@section('content')

{{-- ── Hero ─────────────────────────────────────────────────────── --}}
<div class="pg-hero">
    <div class="pg-hero-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
        </svg>
    </div>
    <div>
        <p class="pg-hero-title">{{ __('app.platform_overview') }}</p>
        <p class="pg-hero-sub">{{ now()->format('d/m/Y H:i') }} — {{ config('tenancy.root_domain') }}</p>
    </div>
</div>

{{-- ── Row 1 : tenants ──────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

    <div class="kpi">
        <div>
            <p class="kpi-label">{{ __('app.total_tenants') }}</p>
            <p class="kpi-value">{{ number_format($total) }}</p>
            <p class="pf-meta" style="margin-top:.15rem">{{ __('app.all_tenants') }}</p>
        </div>
        <div class="kpi-icon" style="background:color-mix(in srgb,var(--accent) 14%,transparent)">
            <svg fill="none" stroke="var(--accent)" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
    </div>

    <div class="kpi">
        <div>
            <p class="kpi-label">{{ __('app.active_tenants') }}</p>
            <p class="kpi-value" style="color:#059669">{{ number_format($countOf('active')) }}</p>
            <p class="pf-meta" style="margin-top:.15rem">{{ __('app.tenant_status_active') }}</p>
        </div>
        <div class="kpi-icon" style="background:#d1fae5">
            <svg fill="none" stroke="#059669" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>

    <div class="kpi">
        <div>
            <p class="kpi-label">{{ __('app.suspended_tenants') }}</p>
            <p class="kpi-value" style="color:#d97706">{{ number_format($countOf('suspended')) }}</p>
            <p class="pf-meta" style="margin-top:.15rem">{{ $countOf('expired') }} {{ __('app.tenant_status_expired') }}</p>
        </div>
        <div class="kpi-icon" style="background:#fef3c7">
            <svg fill="none" stroke="#d97706" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>

    <div class="kpi">
        <div>
            <p class="kpi-label">{{ __('app.needs_attention') }}</p>
            <p class="kpi-value" style="color:#dc2626">{{ number_format($countOf('failed') + $countOf('provisioning')) }}</p>
            <p class="pf-meta" style="margin-top:.15rem">
                {{ $countOf('provisioning') }} {{ __('app.tenant_status_provisioning') }} ·
                {{ $countOf('failed') }} {{ __('app.tenant_status_failed') }}
            </p>
        </div>
        <div class="kpi-icon" style="background:#fee2e2">
            <svg fill="none" stroke="#dc2626" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
        </div>
    </div>
</div>

{{-- ── Row 2 : billing + inbound ────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="kpi">
        <div>
            <p class="kpi-label">{{ __('app.active_subscriptions') }}</p>
            <p class="kpi-value sm">{{ number_format($activeSubscriptions ?? 0) }}</p>
        </div>
        <div class="kpi-icon" style="background:#dbeafe">
            <svg fill="none" stroke="#2563eb" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
        </div>
    </div>

    <div class="kpi">
        <div>
            <p class="kpi-label">{{ __('app.mrr') }}</p>
            <p class="kpi-value sm">
                {{ number_format($mrr ?? 0, 2) }}
                <span style="font-size:.8rem;font-weight:500;color:#94a3b8">{{ __('app.currency') }}</span>
            </p>
        </div>
        <div class="kpi-icon" style="background:#d1fae5">
            <svg fill="none" stroke="#059669" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/>
            </svg>
        </div>
    </div>

    <div class="kpi">
        <div>
            <p class="kpi-label">{{ __('app.plans') }}</p>
            <p class="kpi-value sm">{{ number_format($plansCount ?? 0) }}</p>
            <p class="pf-meta" style="margin-top:.15rem">{{ __('app.active') }}</p>
        </div>
        <div class="kpi-icon" style="background:#f1f5f9">
            <svg fill="none" stroke="#475569" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"/>
            </svg>
        </div>
    </div>

    <div class="kpi">
        <div>
            <p class="kpi-label">{{ __('app.new_leads') }}</p>
            <p class="kpi-value sm" style="color:#4f46e5">{{ number_format($newLeads ?? 0) }}</p>
            <p class="pf-meta" style="margin-top:.15rem">{{ __('app.new_leads_hint') }}</p>
        </div>
        <div class="kpi-icon" style="background:#e0e7ff">
            <svg fill="none" stroke="#4f46e5" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── Expiring subscriptions ───────────────────────────────── --}}
    <div class="xl:col-span-2">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">{{ __('app.expiring_soon') }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('app.expiring_soon_hint') }}</p>
                </div>
                @platformCan('subscriptions.view')
                <a href="{{ route('platform.subscriptions.index') }}" class="btn btn-outline btn-sm">{{ __('app.view_all') }}</a>
                @endplatformCan
            </div>

            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('app.tenant') }}</th>
                            <th>{{ __('app.plan') }}</th>
                            <th>{{ __('app.expires_on') }}</th>
                            <th class="text-right">{{ __('app.days_remaining') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expiring as $sub)
                            @php
                                $end  = $sub->ends_at ? \Illuminate\Support\Carbon::parse($sub->ends_at) : null;
                                $days = $end ? (int) now()->startOfDay()->diffInDays($end->copy()->startOfDay(), false) : null;
                            @endphp
                            <tr>
                                <td>
                                    @platformCan('tenants.view')
                                    <a href="{{ route('platform.tenants.show', $sub->tenant_id) }}" class="text-sm font-semibold text-slate-800 hover:text-blue-600">
                                        {{ $sub->tenant->name ?? '—' }}
                                    </a>
                                    @else
                                    <span class="text-sm font-semibold text-slate-800">{{ $sub->tenant->name ?? '—' }}</span>
                                    @endplatformCan
                                    <div class="mono text-slate-400">{{ $sub->tenant->slug ?? '' }}</div>
                                </td>
                                <td class="text-sm text-slate-600">{{ $sub->plan->name ?? '—' }}</td>
                                <td class="text-sm text-slate-600">{{ $end ? $end->format('d/m/Y') : '—' }}</td>
                                <td class="text-right">
                                    @if($days === null)
                                        <span class="badge badge-secondary">{{ __('app.period_lifetime') }}</span>
                                    @elseif($days < 0)
                                        <span class="badge badge-danger">{{ __('app.tenant_status_expired') }}</span>
                                    @elseif($days <= 7)
                                        <span class="badge badge-danger">{{ $days }} {{ __('app.days') }}</span>
                                    @else
                                        <span class="badge badge-warning">{{ $days }} {{ __('app.days') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <p class="empty-state-title">{{ __('app.no_expiring_subscriptions') }}</p>
                                        <p class="empty-state-desc">{{ __('app.no_expiring_subscriptions_hint') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Side column ──────────────────────────────────────────── --}}
    <div class="space-y-6">

        {{-- Quick actions --}}
        <div class="mc" style="margin-bottom:0">
            <div class="mc-head">
                <div class="mc-icon" style="background:color-mix(in srgb,var(--accent) 14%,transparent)">
                    <svg fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="mc-head-title">{{ __('app.quick_actions') }}</p>
                    <p class="mc-head-sub">{{ __('app.quick_actions_hint') }}</p>
                </div>
            </div>
            <div class="mc-body">
                <div class="qa-grid">
                    @platformCan('tenants.create')
                    <a href="{{ route('platform.tenants.create') }}" class="qa-btn">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('app.add_tenant') }}
                    </a>
                    @endplatformCan

                    @platformCan('tenants.view')
                    <a href="{{ route('platform.tenants.index') }}" class="qa-btn">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                        </svg>
                        {{ __('app.tenants') }}
                    </a>
                    @endplatformCan

                    @platformCan('plans.view')
                    <a href="{{ route('platform.plans.index') }}" class="qa-btn">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        {{ __('app.plans') }}
                    </a>
                    @endplatformCan

                    @platformCan('leads.view')
                    <a href="{{ route('platform.leads.index') }}" class="qa-btn">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ __('app.demo_requests') }}
                    </a>
                    @endplatformCan

                    @platformCan('audit.view')
                    <a href="{{ route('platform.audit.index') }}" class="qa-btn">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('app.audit_log') }}
                    </a>
                    @endplatformCan
                </div>
            </div>
        </div>

        {{-- Latest demo requests --}}
        @platformCan('leads.view')
        <div class="mc" style="margin-bottom:0">
            <div class="mc-head">
                <div class="mc-icon" style="background:#e0e7ff">
                    <svg fill="none" stroke="#4f46e5" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="mc-head-title">{{ __('app.latest_demo_requests') }}</p>
                    <p class="mc-head-sub">{{ __('app.leads_hint') }}</p>
                </div>
            </div>
            <div class="mc-body">
                @forelse($leads as $lead)
                <a href="{{ route('platform.leads.show', $lead->id) }}" class="ld-row">
                    <span class="ld-main">
                        <span class="ld-name">{{ $lead->company_name ?: $lead->contact_name }}</span>
                        <span class="ld-sub">{{ $lead->email }}</span>
                    </span>
                    <span class="badge {{ $leadBadges[$lead->status] ?? 'badge-secondary' }}">{{ __('app.lead_status_'.$lead->status) }}</span>
                    <span class="ld-date">{{ $lead->created_at ? \Illuminate\Support\Carbon::parse($lead->created_at)->format('d/m') : '—' }}</span>
                </a>
                @empty
                <div class="empty-state">
                    <p class="empty-state-title">{{ __('app.no_leads_found') }}</p>
                    <p class="empty-state-desc">{{ __('app.no_data') }}</p>
                </div>
                @endforelse
            </div>
            @if($leads->isNotEmpty())
            <div class="mc-body" style="padding-top:0">
                <a href="{{ route('platform.leads.index') }}" class="btn btn-ghost btn-sm w-full text-slate-500">{{ __('app.view_all') }}</a>
            </div>
            @endif
        </div>
        @endplatformCan

        {{-- Status breakdown --}}
        <div class="mc" style="margin-bottom:0">
            <div class="mc-head">
                <div class="mc-icon" style="background:#f1f5f9">
                    <svg fill="none" stroke="#475569" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="mc-head-title">{{ __('app.tenants_by_status') }}</p>
                    <p class="mc-head-sub">{{ __('app.distribution') }}</p>
                </div>
            </div>
            <div class="mc-body">
                @foreach($byStatus as $key => $count)
                <div class="sb-row">
                    <span class="sb-name">{{ __('app.tenant_status_'.$key) }}</span>
                    <span class="sb-track">
                        <span class="sb-fill" style="width:{{ max(2, (int) round($count / $breakdownMax * 100)) }}%;background:{{ $statusColors[$key][1] ?? '#64748b' }}"></span>
                    </span>
                    <span class="sb-val">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ── Recent activity ──────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('app.recent_activity') }}</h3>
        @platformCan('audit.view')
        <a href="{{ route('platform.audit.index') }}" class="btn btn-ghost btn-sm text-slate-500">{{ __('app.view_all') }}</a>
        @endplatformCan
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('app.date') }}</th>
                    <th>{{ __('app.action') }}</th>
                    <th>{{ __('app.actor') }}</th>
                    <th>{{ __('app.tenant') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($audits as $log)
                <tr>
                    <td class="text-sm text-slate-600 whitespace-nowrap">
                        {{ $log->created_at ? \Illuminate\Support\Carbon::parse($log->created_at)->format('d/m/Y H:i') : '—' }}
                    </td>
                    <td>
                        <span class="mono text-slate-700">{{ $log->action }}</span>
                        @if($log->description)
                        <div class="pf-meta">{{ $log->description }}</div>
                        @endif
                    </td>
                    <td class="text-sm text-slate-600">{{ $log->actor_email ?? '—' }}</td>
                    <td class="text-sm text-slate-600">{{ $log->tenant->name ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">
                        <div class="empty-state">
                            <p class="empty-state-title">{{ __('app.no_audit_logs_found') }}</p>
                            <p class="empty-state-desc">{{ __('app.no_data') }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
