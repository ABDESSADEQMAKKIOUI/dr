@extends('layouts.platform')
@section('title', __('app.plans'))

@php
$pageTitle = __('app.plans');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('platform.dashboard')],
    ['label' => __('app.plans'),     'url' => route('platform.plans.index')],
];
@endphp

@section('content')

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">{{ __('app.plans') }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('app.plans_hint') }}</p>
        </div>
        @platformCan('plans.create')
        <a href="{{ route('platform.plans.create') }}" class="btn btn-primary btn-sm" style="background:var(--accent);border-color:var(--accent)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.add_plan') }}
        </a>
        @endplatformCan
    </div>

    <div class="filter-bar">
        <div class="filter-group flex-1 min-w-48">
            <label class="filter-label">{{ __('app.search') }}</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                </svg>
                <input type="text" id="search" class="form-control pl-9 py-2" placeholder="{{ __('app.name') }}, {{ __('app.code') }}…">
            </div>
        </div>
        <div class="filter-group">
            <label class="filter-label">{{ __('app.status') }}</label>
            <select id="f-active" class="form-control py-2">
                <option value="">{{ __('app.all') }}</option>
                <option value="1">{{ __('app.active') }}</option>
                <option value="0">{{ __('app.inactive') }}</option>
            </select>
        </div>
        <div class="filter-group">
            <label class="filter-label opacity-0">.</label>
            <button type="button" onclick="resetFilters()" class="btn btn-ghost btn-sm text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ __('app.reset') }}
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="table" id="plans-table">
            <thead>
                <tr>
                    <th class="w-10">#</th>
                    <th>{{ __('app.name') }}</th>
                    <th class="text-right">{{ __('app.price') }}</th>
                    <th>{{ __('app.billing_period') }}</th>
                    <th class="text-right">{{ __('app.trial_days') }}</th>
                    <th>{{ __('app.limits') }}</th>
                    <th class="text-right">{{ __('app.usage') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th class="w-20 text-center">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody id="plans-tbody">
                @forelse($plans as $plan)
                <tr data-search="{{ strtolower($plan->name.' '.$plan->slug) }}" data-active="{{ $plan->is_active ? 1 : 0 }}">
                    <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>

                    <td>
                        <div class="text-sm font-semibold text-slate-800">{{ $plan->name }}</div>
                        <div class="mono text-slate-400">{{ $plan->slug }}</div>
                    </td>

                    <td class="text-right text-sm font-semibold text-slate-700">
                        {{ number_format($plan->price, 2) }}
                        <span class="text-xs text-slate-400 font-normal">{{ $plan->currency }}</span>
                    </td>

                    <td class="text-sm text-slate-600">{{ __('app.period_'.$plan->billing_period) }}</td>

                    <td class="text-right text-sm text-slate-600">{{ $plan->trial_days }}</td>

                    <td class="text-xs text-slate-500">
                        {{ __('app.users') }}: {{ $plan->max_users ?? __('app.unlimited') }} ·
                        {{ __('app.warehouses') }}: {{ $plan->max_warehouses ?? __('app.unlimited') }} ·
                        {{ __('app.products') }}: {{ $plan->max_products ?? __('app.unlimited') }}
                    </td>

                    <td class="text-right text-xs text-slate-500">
                        {{ $plan->tenants_count ?? 0 }} {{ __('app.tenants') }}<br>
                        {{ $plan->subscriptions_count ?? 0 }} {{ __('app.subscriptions') }}
                    </td>

                    <td>
                        <span class="badge {{ $plan->is_active ? 'badge-success' : 'badge-secondary' }}">
                            {{ $plan->is_active ? __('app.active') : __('app.inactive') }}
                        </span>
                    </td>

                    <td>
                        <div class="flex items-center justify-center gap-1">
                            @platformCan('plans.update')
                            <a href="{{ route('platform.plans.edit', $plan->id) }}" class="action-btn action-btn-edit" title="{{ __('app.edit') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('platform.plans.destroy', $plan->id) }}"
                                  onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
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
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <p class="empty-state-title">{{ __('app.no_plans_found') }}</p>
                            <p class="empty-state-desc">{{ __('app.no_data') }}</p>
                            @platformCan('plans.create')
                            <a href="{{ route('platform.plans.create') }}" class="btn btn-primary btn-sm mt-4" style="background:var(--accent);border-color:var(--accent)">
                                {{ __('app.add_plan') }}
                            </a>
                            @endplatformCan
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($plans instanceof \Illuminate\Pagination\AbstractPaginator && $plans->hasPages())
    <div class="pagination">{{ $plans->links() }}</div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function applyFilters() {
    const q = document.getElementById('search').value.toLowerCase();
    const a = document.getElementById('f-active').value;
    document.querySelectorAll('#plans-tbody tr[data-search]').forEach(row => {
        const ok = (!q || row.dataset.search.includes(q)) && (a === '' || row.dataset.active === a);
        row.style.display = ok ? '' : 'none';
    });
}
function resetFilters() {
    document.getElementById('search').value = '';
    document.getElementById('f-active').value = '';
    applyFilters();
}
document.getElementById('search').addEventListener('input', applyFilters);
document.getElementById('f-active').addEventListener('change', applyFilters);
</script>
@endpush
