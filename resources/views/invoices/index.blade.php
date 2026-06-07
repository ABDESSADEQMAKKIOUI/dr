@extends('layouts.app')
@section('title', __('app.invoices'))

@php
$pageTitle = __('app.invoices');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.invoices'), 'url' => route('invoices.index')],
];

$collection   = $invoices->getCollection();
$paidCount    = $collection->filter(fn($i) => ($i->paid_amount ?? 0) >= ($i->total_amount ?? 0) && ($i->total_amount ?? 0) > 0)->count();
$partialCount = $collection->filter(fn($i) => ($i->paid_amount ?? 0) > 0 && ($i->paid_amount ?? 0) < ($i->total_amount ?? 0))->count();
$unpaidCount  = $collection->filter(fn($i) => ($i->paid_amount ?? 0) <= 0)->count();
@endphp

@section('content')

{{-- ── Stats row ────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-mini">
        <div class="stat-mini-icon bg-indigo-100">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value">{{ $invoices->total() }}</div>
            <div class="stat-mini-label">{{ __('app.total') }} {{ __('app.invoices') }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-emerald-100">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-emerald-600">{{ $paidCount }}</div>
            <div class="stat-mini-label">{{ __('app.paid') }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-amber-100">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-amber-600">{{ $partialCount }}</div>
            <div class="stat-mini-label">{{ __('app.partial') }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-rose-100">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-rose-600">{{ $unpaidCount }}</div>
            <div class="stat-mini-label">{{ __('app.unpaid') }}</div>
        </div>
    </div>
</div>

{{-- ── Main card ────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">{{ __('app.invoices') }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('app.manage_invoices') ?? 'Manage all your invoices' }}</p>
        </div>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.add_invoice') }}
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
                <input type="text" id="search" placeholder="{{ __('app.invoice_number') }}, {{ __('app.customer') }}…"
                       class="form-control pl-9 py-2">
            </div>
        </div>
        <div class="filter-group w-44">
            <label class="filter-label">{{ __('app.status') }}</label>
            <select id="filter-status" class="form-control py-2">
                <option value="">{{ __('app.all') }}</option>
                <option value="paid">{{ __('app.paid') }}</option>
                <option value="partial">{{ __('app.partial') }}</option>
                <option value="unpaid">{{ __('app.unpaid') }}</option>
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
                    <th>{{ __('app.invoice_number') }}</th>
                    <th>{{ __('app.customer') }}</th>
                    <th>{{ __('app.date') }}</th>
                    <th class="text-right">{{ __('app.total') }}</th>
                    <th class="text-right">{{ __('app.paid') }}</th>
                    <th class="w-28">{{ __('app.status') }}</th>
                    <th class="w-20 text-center">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody id="invoices-tbody">
                @forelse($invoices as $invoice)
                @php
                $isPaid    = ($invoice->paid_amount ?? 0) >= ($invoice->total_amount ?? 0) && ($invoice->total_amount ?? 0) > 0;
                $isPartial = !$isPaid && ($invoice->paid_amount ?? 0) > 0;
                $payStatus = $isPaid ? 'paid' : ($isPartial ? 'partial' : 'unpaid');
                $statusCfg = match($payStatus) {
                    'paid'    => ['badge' => 'badge-success', 'label' => __('app.paid')],
                    'partial' => ['badge' => 'badge-warning', 'label' => __('app.partial')],
                    default   => ['badge' => 'badge-danger',  'label' => __('app.unpaid')],
                };
                $due = max(0, ($invoice->total_amount ?? 0) - ($invoice->paid_amount ?? 0));
                @endphp
                <tr data-search="{{ strtolower(($invoice->reference ?? '') . ' ' . ($invoice->customer->name ?? '')) }}"
                    data-status="{{ $payStatus }}">
                    <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>

                    <td>
                        <a href="{{ route('invoices.show', $invoice->id) }}"
                           class="mono text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                            {{ $invoice->reference ?? '#' . $invoice->id }}
                        </a>
                    </td>

                    <td class="text-sm font-medium text-slate-700">{{ $invoice->customer->name ?? '—' }}</td>

                    <td class="text-sm text-slate-600">
                        {{ $invoice->date ? $invoice->date->format('d M Y') : $invoice->created_at->format('d M Y') }}
                    </td>

                    <td class="text-right text-sm font-semibold text-slate-700">
                        {{ number_format($invoice->total_amount ?? 0, 2) }}
                        <span class="text-xs text-slate-400 font-normal">DH</span>
                    </td>

                    <td class="text-right">
                        <div class="text-sm font-semibold {{ $due > 0.01 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ number_format($invoice->paid_amount ?? 0, 2) }}
                            <span class="text-xs font-normal">DH</span>
                        </div>
                        @if($due > 0.01)
                        <div class="text-xs text-rose-400">{{ __('app.due') ?? 'Due' }}: {{ number_format($due, 2) }}</div>
                        @endif
                    </td>

                    <td>
                        <span class="badge {{ $statusCfg['badge'] }}">{{ $statusCfg['label'] }}</span>
                    </td>

                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('invoices.show', $invoice->id) }}"
                               class="action-btn action-btn-view" title="{{ __('app.view') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @if(Route::has('invoices.download'))
                            <a href="{{ route('invoices.download', $invoice->id) }}"
                               class="action-btn" title="{{ __('app.download') }}"
                               style="color: #7c3aed;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                                </svg>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <p class="empty-state-title">{{ __('app.no_results') }}</p>
                            <p class="empty-state-desc">{{ __('app.no_invoices_yet') ?? 'No invoices found.' }}</p>
                            <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm mt-4">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.add_invoice') }}
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices->hasPages())
    <div class="pagination">{{ $invoices->links() }}</div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function applyFilters() {
    const search = document.getElementById('search').value.toLowerCase();
    const status = document.getElementById('filter-status').value;
    document.querySelectorAll('#invoices-tbody tr[data-search]').forEach(row => {
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
