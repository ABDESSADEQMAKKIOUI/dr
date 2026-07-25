@extends('layouts.platform')
@section('title', __('app.subscriptions'))

@php
/**
 * Payload from App\Http\Controllers\Platform\SubscriptionController@index:
 *   $subscriptions (paginator), $search, $status, $statuses
 */
$pageTitle = __('app.subscriptions');
$breadcrumbs = [
    ['label' => __('app.dashboard'),     'url' => route('platform.dashboard')],
    ['label' => __('app.subscriptions'), 'url' => route('platform.subscriptions.index')],
];

$search     = $search ?? '';
$status     = $status ?? '';
$statusList = $statuses ?? ['trialing', 'active', 'past_due', 'cancelled', 'expired'];

$badge = [
    'active'    => 'badge-success',
    'trialing'  => 'badge-info',
    'past_due'  => 'badge-warning',
    'cancelled' => 'badge-secondary',
    'expired'   => 'badge-danger',
];
@endphp

@section('content')

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">{{ __('app.subscriptions') }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ __('app.subscriptions_hint') }}
                @if($subscriptions instanceof \Illuminate\Pagination\AbstractPaginator)
                    — {{ $subscriptions->total() }} {{ __('app.total') }}
                @endif
            </p>
        </div>
    </div>

    <form method="GET" action="{{ route('platform.subscriptions.index') }}" class="filter-bar">
        <div class="filter-group flex-1 min-w-48">
            <label class="filter-label" for="search">{{ __('app.search') }}</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                </svg>
                <input type="text" id="search" name="search" value="{{ $search }}" class="form-control pl-9 py-2"
                       placeholder="{{ __('app.tenant') }}, {{ __('app.domain') }}…">
            </div>
        </div>
        <div class="filter-group">
            <label class="filter-label" for="status">{{ __('app.status') }}</label>
            <select id="status" name="status" class="form-control py-2">
                <option value="">{{ __('app.all') }}</option>
                @foreach($statusList as $st)
                <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>{{ __('app.sub_status_'.$st) }}</option>
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
            <a href="{{ route('platform.subscriptions.index') }}" class="btn btn-ghost btn-sm text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ __('app.reset') }}
            </a>
        </div>
    </form>

    <div class="table-wrapper">
        <table class="table" id="subs-table">
            <thead>
                <tr>
                    <th class="w-10">#</th>
                    <th>{{ __('app.tenant') }}</th>
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
                @forelse($subscriptions as $sub)
                <tr>
                    <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>

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

                    <td>
                        <span class="badge {{ $badge[$sub->status] ?? 'badge-secondary' }}">{{ __('app.sub_status_'.$sub->status) }}</span>
                    </td>

                    <td class="text-sm text-slate-600">{{ __('app.period_'.$sub->billing_period) }}</td>

                    <td class="text-sm text-slate-600">{{ $sub->starts_at ? \Illuminate\Support\Carbon::parse($sub->starts_at)->format('d/m/Y') : '—' }}</td>

                    <td class="text-sm text-slate-600">{{ $sub->ends_at ? \Illuminate\Support\Carbon::parse($sub->ends_at)->format('d/m/Y') : '—' }}</td>

                    <td class="text-right text-sm font-semibold text-slate-700">
                        {{ number_format($sub->price, 2) }}
                        <span class="text-xs text-slate-400 font-normal">{{ $sub->currency }}</span>
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
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <p class="empty-state-title">{{ __('app.no_subscriptions_found') }}</p>
                            <p class="empty-state-desc">{{ __('app.no_data') }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($subscriptions instanceof \Illuminate\Pagination\AbstractPaginator && $subscriptions->hasPages())
    <div class="pagination">{{ $subscriptions->links() }}</div>
    @endif
</div>

@endsection
