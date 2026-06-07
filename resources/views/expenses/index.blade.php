@extends('layouts.app')
@section('title', __('app.expenses'))

@php
$pageTitle = __('app.expenses');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.expenses'), 'url' => route('expenses.index')],
];

$collection   = $expenses->getCollection();
$totalAmount  = $collection->sum('amount');
$thisMonth    = $collection->filter(fn($e) => \Carbon\Carbon::parse($e->date)->isCurrentMonth())->sum('amount');
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
            <div class="stat-mini-value">{{ $expenses->total() }}</div>
            <div class="stat-mini-label">{{ __('app.total') }} {{ __('app.expenses') }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-rose-100">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-rose-600">{{ number_format($totalAmount, 2) }}</div>
            <div class="stat-mini-label">{{ __('app.total_amount') ?? 'Total Amount' }} (DH)</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-amber-100">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-amber-600">{{ number_format($thisMonth, 2) }}</div>
            <div class="stat-mini-label">{{ __('app.this_month') ?? 'This Month' }} (DH)</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-slate-100">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-slate-600">{{ $collection->pluck('category.name')->filter()->unique()->count() }}</div>
            <div class="stat-mini-label">{{ __('app.categories') }}</div>
        </div>
    </div>
</div>

{{-- ── Main card ────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">{{ __('app.expenses') }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('app.manage_expenses') ?? 'Track all your business expenses' }}</p>
        </div>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.add_expense') }}
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
                <input type="text" id="search" placeholder="{{ __('app.description') }}…"
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
        <table class="table">
            <thead>
                <tr>
                    <th class="w-10">#</th>
                    <th>{{ __('app.date') }}</th>
                    <th>{{ __('app.description') }}</th>
                    <th>{{ __('app.category') }}</th>
                    <th class="text-right">{{ __('app.amount') }}</th>
                    <th>{{ __('app.payment_method') }}</th>
                    <th>{{ __('app.created_by') }}</th>
                    <th class="w-20 text-center">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody id="expenses-tbody">
                @forelse($expenses as $expense)
                <tr data-search="{{ strtolower($expense->description . ' ' . ($expense->category->name ?? '')) }}">
                    <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>

                    <td class="text-sm text-slate-600">
                        {{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}
                    </td>

                    <td class="text-sm font-medium text-slate-700 max-w-xs truncate">
                        {{ $expense->description ?? '—' }}
                    </td>

                    <td>
                        @if($expense->category)
                        <span class="badge badge-secondary">{{ $expense->category->name }}</span>
                        @else
                        <span class="text-slate-400 text-xs">—</span>
                        @endif
                    </td>

                    <td class="text-right text-sm font-bold text-rose-600">
                        {{ number_format($expense->amount, 2) }}
                        <span class="text-xs font-normal text-slate-400">DH</span>
                    </td>

                    <td>
                        @if($expense->payment_method)
                        <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md font-medium">
                            {{ $expense->payment_method }}
                        </span>
                        @else
                        <span class="text-slate-400 text-xs">—</span>
                        @endif
                    </td>

                    <td class="text-sm text-slate-600">{{ $expense->user->full_name ?? '—' }}</td>

                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('expenses.show', $expense->id) }}"
                               class="action-btn action-btn-view" title="{{ __('app.view') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('expenses.edit', $expense->id) }}"
                               class="action-btn action-btn-edit" title="{{ __('app.edit') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
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
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="empty-state-title">{{ __('app.no_results') }}</p>
                            <p class="empty-state-desc">{{ __('app.no_expenses_yet') ?? 'No expenses recorded yet.' }}</p>
                            <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm mt-4">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.add_expense') }}
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($expenses->hasPages())
    <div class="pagination">{{ $expenses->links() }}</div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function applyFilters() {
    const search = document.getElementById('search').value.toLowerCase();
    document.querySelectorAll('#expenses-tbody tr[data-search]').forEach(row => {
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
