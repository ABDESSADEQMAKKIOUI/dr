@extends('layouts.app')
@section('title', __('app.stock_alerts'))

@php
$pageTitle = __('app.stock_alerts');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.stock'),      'url' => '#'],
    ['label' => __('app.alerts'),     'url' => ''],
];
@endphp

@section('content')

{{-- ── Stats row ────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="stat-mini">
        <div class="stat-mini-icon bg-rose-100">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-rose-600">{{ $outOfStockCount }}</div>
            <div class="stat-mini-label">{{ __('app.out_of_stock') }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-amber-100">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-amber-600">{{ $lowStockCount }}</div>
            <div class="stat-mini-label">{{ __('app.low_stock') }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-yellow-100">
            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-yellow-600">{{ $expiringSoonCount }}</div>
            <div class="stat-mini-label">{{ __('app.expiring_soon') }}</div>
        </div>
    </div>
</div>

{{-- ── Main card ────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">{{ __('app.stock_alerts') }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('app.products_needing_attention') ?? 'Products that need restocking' }}</p>
        </div>
        @if($alerts->count())
        <span class="badge badge-danger">{{ $alerts->count() }} {{ __('app.alerts') }}</span>
        @endif
    </div>

    {{-- Filter bar --}}
    <div class="filter-bar">
        <div class="filter-group flex-1 min-w-48">
            <label class="filter-label">{{ __('app.search') }}</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                </svg>
                <input type="text" id="search" placeholder="{{ __('app.product') }}, {{ __('app.sku') }}…"
                       class="form-control pl-9 py-2">
            </div>
        </div>
        <div class="filter-group w-44">
            <label class="filter-label">{{ __('app.type') ?? 'Type' }}</label>
            <select id="filter-type" class="form-control py-2">
                <option value="">{{ __('app.all') }}</option>
                <option value="out_of_stock">{{ __('app.out_of_stock') }}</option>
                <option value="low_stock">{{ __('app.low_stock') }}</option>
            </select>
        </div>
        <div class="filter-group">
            <label class="filter-label opacity-0">.</label>
            <button onclick="resetFilters()" class="btn btn-ghost btn-sm text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ __('app.reset') }}
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-10">#</th>
                    <th>{{ __('app.product') }}</th>
                    <th>{{ __('app.sku') }}</th>
                    <th class="w-28">{{ __('app.status') }}</th>
                    <th class="text-right w-28">{{ __('app.stock') }}</th>
                    <th class="text-right w-28">{{ __('app.stock_alert') }}</th>
                    <th class="w-20 text-center">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody id="alerts-tbody">
                @forelse($alerts as $alert)
                @php
                $isOut = $alert->stock_quantity <= 0;
                $type  = $isOut ? 'out_of_stock' : 'low_stock';
                @endphp
                <tr data-search="{{ strtolower($alert->name . ' ' . $alert->sku) }}"
                    data-type="{{ $type }}">
                    <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>

                    <td>
                        <div class="flex items-center gap-3">
                            @if($alert->image)
                                <img src="{{ asset('storage/' . $alert->image) }}"
                                     class="w-8 h-8 rounded-lg object-cover ring-1 ring-slate-200"
                                     alt="{{ $alert->name }}">
                            @else
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br {{ $isOut ? 'from-rose-400 to-rose-600' : 'from-amber-400 to-orange-500' }} flex items-center justify-center flex-shrink-0">
                                    <span class="text-white text-xs font-bold">{{ strtoupper(substr($alert->name, 0, 2)) }}</span>
                                </div>
                            @endif
                            <div class="text-sm font-semibold text-slate-800">{{ $alert->name }}</div>
                        </div>
                    </td>

                    <td>
                        <span class="mono text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md">
                            {{ $alert->sku ?? '—' }}
                        </span>
                    </td>

                    <td>
                        @if($isOut)
                            <span class="badge badge-danger">{{ __('app.out_of_stock') }}</span>
                        @else
                            <span class="badge badge-warning">{{ __('app.low_stock') }}</span>
                        @endif
                    </td>

                    <td class="text-right">
                        <span class="text-sm font-bold {{ $isOut ? 'text-rose-600' : 'text-amber-600' }}">
                            {{ $alert->stock_quantity }}
                        </span>
                    </td>

                    <td class="text-right text-sm text-slate-500">
                        {{ $alert->stock_alert }}
                    </td>

                    <td>
                        <div class="flex items-center justify-center">
                            <a href="{{ route('products.edit', $alert->id) }}"
                               class="action-btn action-btn-edit" title="{{ __('app.restock') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="empty-state-title">{{ __('app.no_alerts') ?? 'No alerts' }}</p>
                            <p class="empty-state-desc">{{ __('app.all_products_stocked') ?? 'All products are well stocked.' }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
function applyFilters() {
    const search = document.getElementById('search').value.toLowerCase();
    const type   = document.getElementById('filter-type').value;
    document.querySelectorAll('#alerts-tbody tr[data-search]').forEach(row => {
        const matchSearch = !search || row.dataset.search.includes(search);
        const matchType   = !type   || row.dataset.type === type;
        row.style.display = (matchSearch && matchType) ? '' : 'none';
    });
}
function resetFilters() {
    document.getElementById('search').value = '';
    document.getElementById('filter-type').value = '';
    applyFilters();
}
document.getElementById('search').addEventListener('input', applyFilters);
document.getElementById('filter-type').addEventListener('change', applyFilters);
</script>
@endpush
