@extends('layouts.app')
@section('title', __('app.purchases'))

@php
$pageTitle = __('app.purchases');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.purchases'), 'url' => route('purchases.index')],
];

$collection     = $purchases->getCollection();
$totalAmount    = $collection->sum('total_amount');
$totalPaid      = $collection->sum('paid_amount');
$receivedCount  = $collection->where('status', 'received')->count();
$pendingCount   = $collection->whereIn('status', ['ordered','draft'])->count();
@endphp

@section('content')

{{-- ── Stats row ────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-mini">
        <div class="stat-mini-icon bg-indigo-100">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value">{{ $purchases->total() }}</div>
            <div class="stat-mini-label">{{ __('app.total') }} {{ __('app.purchases') }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-emerald-100">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-emerald-600">{{ number_format($totalAmount, 2) }}</div>
            <div class="stat-mini-label">{{ __('app.total_amount') ?? 'Total Amount' }} (DH)</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-sky-100">
            <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-sky-600">{{ $receivedCount }}</div>
            <div class="stat-mini-label">{{ __('app.received') ?? 'Received' }}</div>
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
            <h3 class="card-title">{{ __('app.purchases') }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('app.manage_purchases') ?? 'Manage your purchase orders' }}</p>
        </div>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.add_purchase') }}
        </a>
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
                <input type="text" id="search" placeholder="{{ __('app.reference') }}, {{ __('app.supplier') }}…"
                       class="form-control pl-9 py-2">
            </div>
        </div>
        <div class="filter-group w-44">
            <label class="filter-label">{{ __('app.status') }}</label>
            <select id="filter-status" class="form-control py-2">
                <option value="">{{ __('app.all') }}</option>
                <option value="draft">{{ __('app.draft') }}</option>
                <option value="ordered">{{ __('app.ordered') ?? 'Ordered' }}</option>
                <option value="received">{{ __('app.received') ?? 'Received' }}</option>
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
                    <th>{{ __('app.supplier') }}</th>
                    <th>{{ __('app.date') }}</th>
                    <th class="text-right">{{ __('app.total') }}</th>
                    <th class="text-right">{{ __('app.paid') }}</th>
                    <th class="w-28">{{ __('app.status') }}</th>
                    <th>{{ __('app.created_by') }}</th>
                    <th class="w-20 text-center">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody id="purchases-tbody">
                @forelse($purchases as $purchase)
                @php
                $statusCfg = match($purchase->status ?? '') {
                    'received' => ['badge' => 'badge-success',   'label' => __('app.received') ?? 'Received'],
                    'ordered'  => ['badge' => 'badge-info',      'label' => __('app.ordered') ?? 'Ordered'],
                    'draft'    => ['badge' => 'badge-secondary', 'label' => __('app.draft')],
                    default    => ['badge' => 'badge-secondary', 'label' => ucfirst($purchase->status ?? 'N/A')],
                };
                $due = ($purchase->total_amount ?? 0) - ($purchase->paid_amount ?? 0);
                @endphp
                <tr data-search="{{ strtolower(($purchase->reference ?? '') . ' ' . ($purchase->supplier->name ?? '')) }}"
                    data-status="{{ $purchase->status }}">
                    <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>

                    <td>
                        <a href="{{ route('purchases.show', $purchase->id) }}"
                           class="mono text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                            {{ $purchase->reference ?? '#' . $purchase->id }}
                        </a>
                    </td>

                    <td class="text-sm font-medium text-slate-700">{{ $purchase->supplier->name ?? '—' }}</td>

                    <td class="text-sm text-slate-600">
                        {{ \Carbon\Carbon::parse($purchase->date ?? $purchase->created_at)->format('d M Y') }}
                    </td>

                    <td class="text-right text-sm font-semibold text-slate-700">
                        {{ number_format($purchase->total_amount ?? 0, 2) }}
                        <span class="text-xs text-slate-400 font-normal">DH</span>
                    </td>

                    <td class="text-right">
                        <div class="text-sm font-semibold {{ $due > 0.01 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ number_format($purchase->paid_amount ?? 0, 2) }}
                            <span class="text-xs font-normal">DH</span>
                        </div>
                        @if($due > 0.01)
                        <div class="text-xs text-rose-400">{{ __('app.due') ?? 'Due' }}: {{ number_format($due, 2) }}</div>
                        @endif
                    </td>

                    <td>
                        <span class="badge {{ $statusCfg['badge'] }}">{{ $statusCfg['label'] }}</span>
                    </td>

                    <td class="text-sm text-slate-600">{{ $purchase->user->full_name ?? '—' }}</td>

                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('purchases.show', $purchase->id) }}"
                               class="action-btn action-btn-view" title="{{ __('app.view') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('purchases.edit', $purchase->id) }}"
                               class="action-btn action-btn-edit" title="{{ __('app.edit') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('purchases.destroy', $purchase->id) }}"
                                  onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn action-btn-delete" title="{{ __('app.delete') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <p class="empty-state-title">{{ __('app.no_results') }}</p>
                            <p class="empty-state-desc">{{ __('app.no_purchases_yet') ?? 'No purchases found. Create your first purchase order.' }}</p>
                            <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm mt-4">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.add_purchase') }}
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($purchases->hasPages())
    <div class="pagination">{{ $purchases->links() }}</div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function applyFilters() {
    const search = document.getElementById('search').value.toLowerCase();
    const status = document.getElementById('filter-status').value;
    document.querySelectorAll('#purchases-tbody tr[data-search]').forEach(row => {
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
