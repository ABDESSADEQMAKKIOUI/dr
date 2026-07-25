@extends('layouts.platform')
@section('title', __('app.audit_log'))

@php
/**
 * Payload from App\Http\Controllers\Platform\AuditLogController@index:
 *   $logs (paginator), $action, $tenantId, $operatorId, $search, $from, $to,
 *   $actions, $tenants, $operators
 */
$pageTitle = __('app.audit_log');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('platform.dashboard')],
    ['label' => __('app.audit_log'), 'url' => route('platform.audit.index')],
];

$entries      = $logs ?? collect();
$action       = $action     ?? '';
$tenantId     = $tenantId   ?? 0;
$operatorId   = $operatorId ?? 0;
$search       = $search     ?? '';
$from         = $from       ?? '';
$to           = $to         ?? '';
$actionList   = $actions    ?? [];
$tenantList   = $tenants    ?? collect();
$operatorList = $operators  ?? collect();

$actionColor = function (string $value): string {
    return match (true) {
        str_contains($value, 'deleted'), str_contains($value, 'failed'), str_contains($value, 'suspended') => 'badge-danger',
        str_contains($value, 'created'), str_contains($value, 'provisioned'), str_contains($value, 'reactivated') => 'badge-success',
        str_contains($value, 'updated'), str_contains($value, 'login') => 'badge-info',
        default => 'badge-secondary',
    };
};
@endphp

@section('content')

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">{{ __('app.audit_log') }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ __('app.audit_log_hint') }}
                @if($entries instanceof \Illuminate\Pagination\AbstractPaginator)
                    — {{ $entries->total() }} {{ __('app.total') }}
                @endif
            </p>
        </div>
    </div>

    <form method="GET" action="{{ route('platform.audit.index') }}" class="filter-bar">
        <div class="filter-group flex-1 min-w-48">
            <label class="filter-label" for="search">{{ __('app.search') }}</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                </svg>
                <input type="text" id="search" name="search" value="{{ $search }}" class="form-control pl-9 py-2"
                       placeholder="{{ __('app.action') }}, {{ __('app.actor') }}, {{ __('app.description') }}…">
            </div>
        </div>

        <div class="filter-group">
            <label class="filter-label" for="action">{{ __('app.action') }}</label>
            <select id="action" name="action" class="form-control py-2">
                <option value="">{{ __('app.all') }}</option>
                @foreach($actionList as $a)
                <option value="{{ $a }}" {{ $action === $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label" for="tenant_id">{{ __('app.tenant') }}</label>
            <select id="tenant_id" name="tenant_id" class="form-control py-2">
                <option value="">{{ __('app.all') }}</option>
                @foreach($tenantList as $t)
                <option value="{{ $t->id }}" {{ (int) $tenantId === (int) $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label" for="operator_id">{{ __('app.actor') }}</label>
            <select id="operator_id" name="operator_id" class="form-control py-2">
                <option value="">{{ __('app.all') }}</option>
                @foreach($operatorList as $o)
                <option value="{{ $o->id }}" {{ (int) $operatorId === (int) $o->id ? 'selected' : '' }}>{{ $o->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label" for="from">{{ __('app.start') }}</label>
            <input type="date" id="from" name="from" value="{{ $from }}" class="form-control py-2">
        </div>

        <div class="filter-group">
            <label class="filter-label" for="to">{{ __('app.end') }}</label>
            <input type="date" id="to" name="to" value="{{ $to }}" class="form-control py-2">
        </div>

        <div class="filter-group">
            <label class="filter-label opacity-0">.</label>
            <button type="submit" class="btn btn-secondary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                {{ __('app.filter') }}
            </button>
        </div>

        <div class="filter-group">
            <label class="filter-label opacity-0">.</label>
            <a href="{{ route('platform.audit.index') }}" class="btn btn-ghost btn-sm text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ __('app.reset') }}
            </a>
        </div>
    </form>

    <div class="table-wrapper">
        <table class="table" id="audit-table">
            <thead>
                <tr>
                    <th>{{ __('app.date') }}</th>
                    <th>{{ __('app.action') }}</th>
                    <th>{{ __('app.actor') }}</th>
                    <th>{{ __('app.tenant') }}</th>
                    <th>{{ __('app.ip_address') }}</th>
                    <th class="w-20 text-center">{{ __('app.details') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $log)
                @php
                    $props = $log->properties;
                    if (is_string($props)) {
                        $props = json_decode($props, true);
                    }
                @endphp
                <tr x-data="{ open: false }">
                    <td class="text-sm text-slate-600 whitespace-nowrap">
                        {{ $log->created_at ? \Illuminate\Support\Carbon::parse($log->created_at)->format('d/m/Y H:i') : '—' }}
                    </td>

                    <td>
                        <span class="badge {{ $actionColor($log->action) }}">{{ $log->action }}</span>
                        @if($log->description)
                        <div class="pf-meta">{{ $log->description }}</div>
                        @endif
                        @if(!empty($props))
                        <div x-show="open" x-collapse style="display:none">
                            <pre class="mono text-slate-600" style="white-space:pre-wrap;word-break:break-word;background:#f8fafc;border-radius:.5rem;padding:.5rem;margin-top:.5rem">{{ json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                        </div>
                        @endif
                    </td>

                    <td class="text-sm text-slate-600">{{ $log->actor_email ?? '—' }}</td>

                    <td class="text-sm text-slate-600">
                        @if($log->tenant_id && ($log->tenant->name ?? null))
                            @platformCan('tenants.view')
                            <a href="{{ route('platform.tenants.show', $log->tenant_id) }}" class="hover:text-blue-600">{{ $log->tenant->name }}</a>
                            @else
                            {{ $log->tenant->name }}
                            @endplatformCan
                        @else
                            —
                        @endif
                    </td>

                    <td class="mono text-slate-500">{{ $log->ip_address ?? '—' }}</td>

                    <td>
                        <div class="flex items-center justify-center">
                            @if(!empty($props))
                            <button type="button" class="action-btn action-btn-view" @click="open = !open" title="{{ __('app.details') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="{ 'rotate-180': open }">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <p class="empty-state-title">{{ __('app.no_audit_logs_found') }}</p>
                            <p class="empty-state-desc">{{ __('app.no_data') }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($entries instanceof \Illuminate\Pagination\AbstractPaginator && $entries->hasPages())
    <div class="pagination">{{ $entries->links() }}</div>
    @endif
</div>

@endsection
