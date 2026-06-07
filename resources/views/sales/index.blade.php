@extends('layouts.app')
@section('title', __('app.sales'))

@php
$pageTitle = __('app.sales');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.sales'), 'url' => route('sales.index')],
];

$collection = $sales->getCollection();
$totalRevenue  = $collection->sum('total');
$totalPaid     = $collection->sum('paid');
$completedCount = $collection->where('status', 'confirmed')->count();
$pendingCount   = $collection->whereIn('status', ['draft','pending'])->count();
@endphp

@section('content')

{{-- ── Stats row ────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-mini">
        <div class="stat-mini-icon bg-indigo-100">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value">{{ $sales->total() }}</div>
            <div class="stat-mini-label">{{ __('app.total') }} {{ __('app.sales') }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-emerald-100">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-emerald-600">{{ number_format($totalRevenue, 2) }}</div>
            <div class="stat-mini-label">{{ __('app.revenue') ?? 'Revenue' }} (DH)</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-sky-100">
            <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-sky-600">{{ $completedCount }}</div>
            <div class="stat-mini-label">{{ __('app.completed') }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-amber-100">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-amber-600">{{ $pendingCount }}</div>
            <div class="stat-mini-label">{{ __('app.pending') }}</div>
        </div>
    </div>
</div>

{{-- ── Main card ────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">{{ __('app.sales') }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('app.manage_sales') ?? 'Manage all your sales' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('sales.pos') }}" class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ __('app.pos') }}
            </a>
            <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('app.add_sale') }}
            </a>
        </div>
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
                <input type="text" id="search" placeholder="{{ __('app.reference') }}, {{ __('app.customer') }}…"
                       class="form-control pl-9 py-2">
            </div>
        </div>
        <div class="filter-group w-44">
            <label class="filter-label">{{ __('app.status') }}</label>
            <select id="filter-status" class="form-control py-2">
                <option value="">{{ __('app.all') }}</option>
                <option value="draft">{{ __('app.draft') }}</option>
                <option value="confirmed">{{ __('app.completed') }}</option>
                <option value="delivered">{{ __('app.delivered') }}</option>
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
                    <th>{{ __('app.reference') }}</th>
                    <th>{{ __('app.customer') }}</th>
                    <th>{{ __('app.date') }}</th>
                    <th class="text-right">{{ __('app.total') }}</th>
                    <th class="text-right">{{ __('app.paid') }}</th>
                    <th class="w-28">{{ __('app.status') }}</th>
                    <th>{{ __('app.created_by') }}</th>
                    <th class="w-20 text-center">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody id="sales-tbody">
                @forelse($sales as $sale)
                @php
                $statusCfg = match($sale->status) {
                    'confirmed' => ['badge' => 'badge-success',   'label' => __('app.completed')],
                    'draft'     => ['badge' => 'badge-secondary', 'label' => __('app.draft')],
                    'delivered' => ['badge' => 'badge-info',      'label' => __('app.delivered')],
                    default     => ['badge' => 'badge-secondary', 'label' => ucfirst($sale->status)],
                };
                $due = ($sale->total ?? 0) - ($sale->paid ?? 0);
                @endphp
                <tr data-search="{{ strtolower(($sale->reference ?? '') . ' ' . ($sale->customer->name ?? '')) }}"
                    data-status="{{ $sale->status }}">
                    <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>

                    <td>
                        <a href="{{ route('sales.show', $sale->id) }}"
                           class="mono text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                            {{ $sale->reference ?? '#' . $sale->id }}
                        </a>
                    </td>

                    <td>
                        <div class="text-sm font-medium text-slate-700">{{ $sale->customer->name ?? 'Walk-in' }}</div>
                    </td>

                    <td class="text-sm text-slate-600">
                        {{ \Carbon\Carbon::parse($sale->sale_date ?? $sale->created_at)->format('d M Y') }}
                    </td>

                    <td class="text-right text-sm font-semibold text-slate-700">
                        {{ number_format($sale->total ?? 0, 2) }}
                        <span class="text-xs text-slate-400 font-normal">DH</span>
                    </td>

                    <td class="text-right">
                        <div class="text-sm font-semibold {{ $due > 0.01 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ number_format($sale->paid ?? 0, 2) }}
                            <span class="text-xs font-normal">DH</span>
                        </div>
                        @if($due > 0.01)
                        <div class="text-xs text-rose-400">{{ __('app.due') ?? 'Due' }}: {{ number_format($due, 2) }}</div>
                        @endif
                    </td>

                    <td>
                        <span class="badge {{ $statusCfg['badge'] }}">{{ $statusCfg['label'] }}</span>
                    </td>

                    <td class="text-sm text-slate-600">{{ $sale->user->full_name ?? '—' }}</td>

                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('sales.show', $sale->id) }}"
                               class="action-btn action-btn-view" title="{{ __('app.view') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('sales.print', $sale->id) }}" target="_blank"
                               class="action-btn" title="{{ __('app.print') }}"
                               style="color: #7c3aed;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="empty-state-title">{{ __('app.no_results') }}</p>
                            <p class="empty-state-desc">{{ __('app.no_sales_yet') ?? 'No sales found. Create your first sale.' }}</p>
                            <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm mt-4">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.add_sale') }}
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sales->hasPages())
    <div class="pagination">{{ $sales->links() }}</div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function applyFilters() {
    const search = document.getElementById('search').value.toLowerCase();
    const status = document.getElementById('filter-status').value;
    document.querySelectorAll('#sales-tbody tr[data-search]').forEach(row => {
        const matchSearch = !search || row.dataset.search.includes(search);
        const matchStatus = !status || row.dataset.status === status;
        row.style.display = (matchSearch && matchStatus) ? '' : 'none';
    });
}
function resetFilters() {
    document.getElementById('search').value = '';
    document.getElementById('filter-status').value = '';
    applyFilters();
}
document.getElementById('search').addEventListener('input', applyFilters);
document.getElementById('filter-status').addEventListener('change', applyFilters);
</script>
@endpush
