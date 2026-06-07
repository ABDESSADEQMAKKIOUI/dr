@extends('layouts.app')
@section('title', __('app.suppliers'))

@php
$pageTitle = __('app.suppliers');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.suppliers'), 'url' => route('suppliers.index')],
];
@endphp

@section('content')

{{-- ── Stats row ────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-mini">
        <div class="stat-mini-icon bg-indigo-100">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value">{{ $suppliers->total() }}</div>
            <div class="stat-mini-label">{{ __('app.total') }} {{ __('app.suppliers') }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-emerald-100">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-emerald-600">{{ $activeSuppliers ?? '—' }}</div>
            <div class="stat-mini-label">{{ __('app.active') }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-rose-100">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-rose-600">{{ $totalSuppliers ?? $suppliers->total() }}</div>
            <div class="stat-mini-label">{{ __('app.total_records') ?? 'Total Records' }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-amber-100">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-amber-600">{{ number_format($suppliers->getCollection()->sum('purchases_sum_total_amount'), 2) }} DH</div>
            <div class="stat-mini-label">{{ __('app.total_purchases') ?? 'Total Purchases' }}</div>
        </div>
    </div>
</div>

{{-- ── Main card ────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">{{ __('app.suppliers') }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('app.manage_suppliers') ?? 'Manage your suppliers' }}</p>
        </div>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.add_supplier') }}
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
                <input type="text" id="search" placeholder="{{ __('app.name') }}, {{ __('app.email') }}, {{ __('app.phone') }}…"
                       class="form-control pl-9 py-2">
            </div>
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
        <table class="table" id="suppliers-table">
            <thead>
                <tr>
                    <th class="w-10">#</th>
                    <th>{{ __('app.name') }}</th>
                    <th>{{ __('app.email') }}</th>
                    <th>{{ __('app.phone') }}</th>
                    <th class="text-right">{{ __('app.total_purchases') }}</th>
                    <th class="text-right">{{ __('app.balance') }}</th>
                    <th class="w-20 text-center">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody id="suppliers-tbody">
                @forelse($suppliers as $supplier)
                <tr data-search="{{ strtolower($supplier->name . ' ' . $supplier->email . ' ' . $supplier->phone) }}">
                    <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>

                    {{-- Name --}}
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-xs font-bold">{{ strtoupper(substr($supplier->name, 0, 2)) }}</span>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-slate-800">{{ $supplier->name }}</div>
                                @if($supplier->city)
                                <div class="text-xs text-slate-400">{{ $supplier->city }}</div>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td class="text-sm text-slate-600">{{ $supplier->email ?? '—' }}</td>
                    <td class="text-sm text-slate-600">{{ $supplier->phone ?? '—' }}</td>

                    <td class="text-right text-sm font-semibold text-slate-700">
                        {{ number_format($supplier->total_purchases ?? 0, 2) }}
                        <span class="text-xs text-slate-400 font-normal">DH</span>
                    </td>

                    <td class="text-right">
                        @php $bal = $supplier->balance ?? 0; @endphp
                        <span class="text-sm font-bold {{ $bal > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ number_format($bal, 2) }}
                            <span class="text-xs font-normal">DH</span>
                        </span>
                    </td>

                    {{-- Actions --}}
                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('suppliers.show', $supplier->id) }}"
                               class="action-btn action-btn-view" title="{{ __('app.view') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('suppliers.edit', $supplier->id) }}"
                               class="action-btn action-btn-edit" title="{{ __('app.edit') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('suppliers.destroy', $supplier->id) }}"
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
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <p class="empty-state-title">{{ __('app.no_suppliers_found') }}</p>
                            <p class="empty-state-desc">{{ __('app.add_first_supplier') ?? 'Add your first supplier to get started.' }}</p>
                            <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm mt-4">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.add_supplier') }}
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($suppliers->hasPages())
    <div class="pagination">{{ $suppliers->links() }}</div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function applyFilters() {
    const search = document.getElementById('search').value.toLowerCase();
    document.querySelectorAll('#suppliers-tbody tr[data-search]').forEach(row => {
        row.style.display = (!search || row.dataset.search.includes(search)) ? '' : 'none';
    });
}
function resetFilters() {
    document.getElementById('search').value = '';
    applyFilters();
}
document.getElementById('search').addEventListener('input', applyFilters);
</script>
@endpush
