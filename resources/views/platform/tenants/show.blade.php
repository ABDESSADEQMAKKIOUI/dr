@extends('layouts.platform')
@section('title', $tenant->name)

@php
$pageTitle = $tenant->name;
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('platform.dashboard')],
    ['label' => __('app.tenants'),   'url' => route('platform.tenants.index')],
    ['label' => $tenant->name,       'url' => ''],
];

$statusColors = [
    'active'       => ['#d1fae5', '#059669'],
    'provisioning' => ['#dbeafe', '#2563eb'],
    'suspended'    => ['#fef3c7', '#d97706'],
    'expired'      => ['#fee2e2', '#dc2626'],
    'failed'       => ['#fee2e2', '#dc2626'],
    'archived'     => ['#f1f5f9', '#64748b'],
];

$subs      = $subscriptions ?? $tenant->subscriptions ?? collect();
$logs      = $auditLogs ?? collect();
$fullHost  = $tenant->slug.'.'.config('tenancy.root_domain');
@endphp

@section('content')

{{-- ── Hero ─────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-body">
        <div class="flex flex-col sm:flex-row items-center gap-4">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:var(--accent)">
                <span class="text-white text-xl font-bold">{{ strtoupper(substr($tenant->name, 0, 2)) }}</span>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-3 flex-wrap">
                    <h2 class="text-2xl font-bold text-slate-800">{{ $tenant->name }}</h2>
                    <span class="badge" style="background:{{ $statusColors[$tenant->status][0] ?? '#f1f5f9' }};color:{{ $statusColors[$tenant->status][1] ?? '#64748b' }}">
                        {{ __('app.tenant_status_'.$tenant->status) }}
                    </span>
                </div>
                <p class="mono text-slate-500 mt-1">https://{{ $fullHost }}</p>
                <p class="pf-meta">{{ $tenant->contact_email }}@if($tenant->contact_phone) · {{ $tenant->contact_phone }}@endif</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap justify-end">
                <a href="https://{{ $fullHost }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    {{ __('app.open_tenant') }}
                </a>
                @platformCan('tenants.update')
                <a href="{{ route('platform.tenants.edit', $tenant->id) }}" class="btn btn-primary btn-sm" style="background:var(--accent);border-color:var(--accent)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    {{ __('app.edit') }}
                </a>
                @endplatformCan
            </div>
        </div>
    </div>
</div>

@if($tenant->provision_error)
<div class="alert alert-error">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div class="flex-1">
        <p class="font-semibold mb-1">{{ __('app.provision_error') }}</p>
        <p class="mono" style="white-space:pre-wrap;word-break:break-word">{{ $tenant->provision_error }}</p>
    </div>
</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── LEFT ───────────────────────────────────────────────────── --}}
    <div class="xl:col-span-2 space-y-6">

        {{-- Technical details --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.tenant_details') }}</h3></div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.domain') }}</span>
                            <span class="pf-kv-v mono">{{ $fullHost }}</span>
                        </div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.tenant_database') }}</span>
                            <span class="pf-kv-v mono">{{ $tenant->database_name }}</span>
                        </div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.plan') }}</span>
                            <span class="pf-kv-v">{{ $tenant->plan->name ?? '—' }}</span>
                        </div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.contact_name') }}</span>
                            <span class="pf-kv-v">{{ $tenant->contact_name ?? '—' }}</span>
                        </div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.language') }}</span>
                            <span class="pf-kv-v">{{ strtoupper($tenant->locale) }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.currency') }}</span>
                            <span class="pf-kv-v">{{ $tenant->currency }}</span>
                        </div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.timezone') }}</span>
                            <span class="pf-kv-v">{{ $tenant->timezone }}</span>
                        </div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.provisioned_at') }}</span>
                            <span class="pf-kv-v">{{ $tenant->provisioned_at ? \Illuminate\Support\Carbon::parse($tenant->provisioned_at)->format('d/m/Y H:i') : '—' }}</span>
                        </div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.suspended_at') }}</span>
                            <span class="pf-kv-v">{{ $tenant->suspended_at ? \Illuminate\Support\Carbon::parse($tenant->suspended_at)->format('d/m/Y H:i') : '—' }}</span>
                        </div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.last_seen_at') }}</span>
                            <span class="pf-kv-v">{{ $tenant->last_seen_at ? \Illuminate\Support\Carbon::parse($tenant->last_seen_at)->format('d/m/Y H:i') : '—' }}</span>
                        </div>
                    </div>
                </div>

                @if($tenant->notes)
                <div class="divider"></div>
                <p class="form-label">{{ __('app.notes') }}</p>
                <p class="text-sm text-slate-600" style="white-space:pre-wrap">{{ $tenant->notes }}</p>
                @endif
            </div>
        </div>

        {{-- Subscriptions --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('app.subscriptions') }}</h3>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('app.plan') }}</th>
                            <th>{{ __('app.status') }}</th>
                            <th>{{ __('app.billing_period') }}</th>
                            <th>{{ __('app.start') }}</th>
                            <th>{{ __('app.end') }}</th>
                            <th class="text-right">{{ __('app.price') }}</th>
                            <th class="w-20 text-center">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subs as $sub)
                        <tr>
                            <td class="text-sm font-semibold text-slate-800">{{ $sub->plan->name ?? '—' }}</td>
                            <td>
                                <span class="badge {{ match($sub->status) {
                                    'active'    => 'badge-success',
                                    'trialing'  => 'badge-info',
                                    'past_due'  => 'badge-warning',
                                    'cancelled' => 'badge-secondary',
                                    default     => 'badge-danger',
                                } }}">{{ __('app.sub_status_'.$sub->status) }}</span>
                            </td>
                            <td class="text-sm text-slate-600">{{ __('app.period_'.$sub->billing_period) }}</td>
                            <td class="text-sm text-slate-600">{{ $sub->starts_at ? \Illuminate\Support\Carbon::parse($sub->starts_at)->format('d/m/Y') : '—' }}</td>
                            <td class="text-sm text-slate-600">{{ $sub->ends_at ? \Illuminate\Support\Carbon::parse($sub->ends_at)->format('d/m/Y') : '—' }}</td>
                            <td class="text-right text-sm font-semibold text-slate-700">
                                {{ number_format($sub->price, 2) }} <span class="text-xs text-slate-400 font-normal">{{ $sub->currency }}</span>
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-1">
                                    @platformCan('subscriptions.update')
                                    <a href="{{ route('platform.subscriptions.edit', $sub->id) }}" class="action-btn action-btn-edit" title="{{ __('app.edit') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    @endplatformCan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <p class="empty-state-title">{{ __('app.no_subscriptions_found') }}</p>
                                    <p class="empty-state-desc">{{ __('app.no_data') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Audit trail --}}
        @platformCan('audit.view')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('app.recent_activity') }}</h3>
                <a href="{{ route('platform.audit.index', ['tenant_id' => $tenant->id]) }}" class="btn btn-ghost btn-sm text-slate-500">{{ __('app.view_all') }}</a>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('app.action') }}</th>
                            <th>{{ __('app.actor') }}</th>
                            <th>{{ __('app.date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>
                                <span class="mono text-slate-700">{{ $log->action }}</span>
                                @if($log->description)<div class="pf-meta">{{ $log->description }}</div>@endif
                            </td>
                            <td class="text-sm text-slate-600">{{ $log->actor_email ?? '—' }}</td>
                            <td class="text-sm text-slate-600">{{ $log->created_at ? \Illuminate\Support\Carbon::parse($log->created_at)->format('d/m/Y H:i') : '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">
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
        @endplatformCan

    </div>

    {{-- ── RIGHT : lifecycle actions ──────────────────────────────── --}}
    <div class="space-y-6">

        @platformCan('tenants.suspend')
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.lifecycle') }}</h3></div>
            <div class="card-body">

                @if($tenant->status === 'suspended')
                    <form method="POST" action="{{ route('platform.tenants.reactivate', $tenant->id) }}"
                          onsubmit="return confirm('{{ __('app.confirm_reactivate') }}')">
                        @csrf
                        <button type="submit" class="btn btn-success btn-md w-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('app.reactivate') }}
                        </button>
                        <p class="form-hint mt-2">{{ __('app.reactivate_hint') }}</p>
                    </form>
                @else
                    <div x-data="{ open: false }">
                        <button type="button" @click="open = !open" class="btn btn-outline btn-md w-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('app.suspend') }}
                        </button>
                        <div x-show="open" x-collapse style="display:none">
                            <form method="POST" action="{{ route('platform.tenants.suspend', $tenant->id) }}"
                                  onsubmit="return confirm('{{ __('app.confirm_suspend') }}')" class="mt-3">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label" for="reason">{{ __('app.suspension_reason') }}</label>
                                    <textarea id="reason" name="reason" rows="2" class="form-control"></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger btn-md w-full">{{ __('app.confirm') }}</button>
                            </form>
                        </div>
                        <p class="form-hint mt-2">{{ __('app.suspend_hint') }}</p>
                    </div>
                @endif

            </div>
        </div>
        @endplatformCan

        @platformCan('tenants.provision')
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.provisioning') }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.tenants.reprovision', $tenant->id) }}"
                      onsubmit="return confirm('{{ __('app.confirm_reprovision') }}')">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-md w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ __('app.reprovision') }}
                    </button>
                </form>
                <p class="form-hint mt-2">{{ __('app.reprovision_hint') }}</p>
            </div>
        </div>
        @endplatformCan

        @platformCan('tenants.delete')
        <div class="card">
            <div class="card-header"><h3 class="card-title text-rose-600">{{ __('app.danger_zone') }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.tenants.destroy', $tenant->id) }}"
                      onsubmit="return confirm('{{ __('app.confirm_delete_tenant') }}')">
                    @csrf
                    @method('DELETE')
                    <div class="tr" style="padding:.5rem 0">
                        <div>
                            <div class="tr-title">{{ __('app.drop_database') }}</div>
                            <div class="tr-sub">{{ __('app.drop_database_hint') }}</div>
                        </div>
                        <label class="ts">
                            <input type="checkbox" name="drop_database" value="1">
                            <span class="ts-slider"></span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-danger btn-md w-full mt-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        {{ __('app.delete') }}
                    </button>
                </form>
            </div>
        </div>
        @endplatformCan

        <a href="{{ route('platform.tenants.index') }}" class="btn btn-ghost btn-md w-full text-slate-500">{{ __('app.back_to_list') }}</a>

    </div>
</div>

@endsection
