@extends('layouts.platform')
@section('title', __('app.tenants'))

@php
/**
 * Payload from App\Http\Controllers\Platform\TenantController@index:
 *   $tenants (paginator), $search, $status, $statuses
 * Filtering is server-side so it applies across every page, not just the
 * rows currently rendered.
 */
$pageTitle = __('app.tenants');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('platform.dashboard')],
    ['label' => __('app.tenants'),   'url' => route('platform.tenants.index')],
];

$search     = $search ?? '';
$status     = $status ?? '';
$statusList = $statuses ?? ['provisioning', 'active', 'suspended', 'expired', 'failed', 'archived'];

$statusColors = [
    'active'       => ['#d1fae5', '#059669'],
    'provisioning' => ['#dbeafe', '#2563eb'],
    'suspended'    => ['#fef3c7', '#d97706'],
    'expired'      => ['#fee2e2', '#dc2626'],
    'failed'       => ['#fee2e2', '#dc2626'],
    'archived'     => ['#f1f5f9', '#64748b'],
];
@endphp

@section('content')

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">{{ __('app.tenants') }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ __('app.tenants_hint') }}
                @if($tenants instanceof \Illuminate\Pagination\AbstractPaginator)
                    — {{ $tenants->total() }} {{ __('app.total') }}
                @endif
            </p>
        </div>
        @platformCan('tenants.create')
        <a href="{{ route('platform.tenants.create') }}" class="btn btn-primary btn-sm" style="background:var(--accent);border-color:var(--accent)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.add_tenant') }}
        </a>
        @endplatformCan
    </div>

    {{-- Filter bar (server-side) --}}
    <form method="GET" action="{{ route('platform.tenants.index') }}" class="filter-bar">
        <div class="filter-group flex-1 min-w-48">
            <label class="filter-label" for="search">{{ __('app.search') }}</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                </svg>
                <input type="text" id="search" name="search" value="{{ $search }}" class="form-control pl-9 py-2"
                       placeholder="{{ __('app.name') }}, {{ __('app.domain') }}, {{ __('app.email') }}…">
            </div>
        </div>
        <div class="filter-group">
            <label class="filter-label" for="status">{{ __('app.status') }}</label>
            <select id="status" name="status" class="form-control py-2">
                <option value="">{{ __('app.all') }}</option>
                @foreach($statusList as $st)
                <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>{{ __('app.tenant_status_'.$st) }}</option>
                @endforeach
            </select>
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
            <a href="{{ route('platform.tenants.index') }}" class="btn btn-ghost btn-sm text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ __('app.reset') }}
            </a>
        </div>
    </form>

    {{-- Table --}}
    <div class="table-wrapper">
        <table class="table" id="tenants-table">
            <thead>
                <tr>
                    <th class="w-10">#</th>
                    <th>{{ __('app.name') }}</th>
                    <th>{{ __('app.domain') }}</th>
                    <th>{{ __('app.plan') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th>{{ __('app.expires_on') }}</th>
                    <th class="w-20 text-center">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $tenant)
                <tr>
                    <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>

                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                                 style="background:var(--accent)">
                                <span class="text-white text-xs font-bold">{{ strtoupper(substr($tenant->name, 0, 2)) }}</span>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-slate-800">{{ $tenant->name }}</div>
                                <div class="text-xs text-slate-400">{{ $tenant->contact_email }}</div>
                            </div>
                        </div>
                    </td>

                    <td>
                        <div class="mono text-slate-600">{{ $tenant->slug }}.{{ config('tenancy.root_domain') }}</div>
                        <div class="mono text-slate-400">{{ $tenant->database_name }}</div>
                    </td>

                    <td class="text-sm text-slate-600">{{ $tenant->plan->name ?? '—' }}</td>

                    <td>
                        <span class="badge" style="background:{{ $statusColors[$tenant->status][0] ?? '#f1f5f9' }};color:{{ $statusColors[$tenant->status][1] ?? '#64748b' }}">
                            {{ __('app.tenant_status_'.$tenant->status) }}
                        </span>
                    </td>

                    <td class="text-sm text-slate-600">
                        {{ $tenant->expires_at ? \Illuminate\Support\Carbon::parse($tenant->expires_at)->format('d/m/Y') : '—' }}
                    </td>

                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('platform.tenants.show', $tenant->id) }}" class="action-btn action-btn-view" title="{{ __('app.view') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @platformCan('tenants.update')
                            <a href="{{ route('platform.tenants.edit', $tenant->id) }}" class="action-btn action-btn-edit" title="{{ __('app.edit') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            @endplatformCan
                            @platformCan('tenants.delete')
                            <form method="POST" action="{{ route('platform.tenants.destroy', $tenant->id) }}"
                                  onsubmit="return confirm('{{ __('app.confirm_delete_tenant') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn action-btn-delete" title="{{ __('app.delete') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endplatformCan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                                </svg>
                            </div>
                            <p class="empty-state-title">{{ __('app.no_tenants_found') }}</p>
                            <p class="empty-state-desc">{{ __('app.no_data') }}</p>
                            @platformCan('tenants.create')
                            <a href="{{ route('platform.tenants.create') }}" class="btn btn-primary btn-sm mt-4" style="background:var(--accent);border-color:var(--accent)">
                                {{ __('app.add_tenant') }}
                            </a>
                            @endplatformCan
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tenants instanceof \Illuminate\Pagination\AbstractPaginator && $tenants->hasPages())
    <div class="pagination">{{ $tenants->links() }}</div>
    @endif
</div>

@endsection
