@extends('layouts.app')
@section('title', __('app.customers'))

@php
$pageTitle = __('app.customers');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.customers'), 'url' => route('customers.index')],
];
@endphp

@section('content')

{{-- ── Stats row ────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-mini">
        <div class="stat-mini-icon bg-indigo-100">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value">{{ $totalCustomers ?? $customers->total() }}</div>
            <div class="stat-mini-label">{{ __('app.total') }} {{ __('app.customers') }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-emerald-100">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-emerald-600">{{ $activeCustomers ?? '—' }}</div>
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
            <div class="stat-mini-value text-rose-600">{{ $withBalance ?? '—' }}</div>
            <div class="stat-mini-label">{{ __('app.with_balance') ?? 'With Balance' }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-sky-100">
            <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-sky-600">{{ $customers->total() }}</div>
            <div class="stat-mini-label">{{ __('app.total_records') ?? 'Total Records' }}</div>
        </div>
    </div>
</div>

{{-- ── Main card ────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">{{ __('app.customers') }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('app.manage_customers') ?? 'Manage your customers' }}</p>
        </div>
        <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.add_customer') }}
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
        <table class="table" id="customers-table">
            <thead>
                <tr>
                    <th class="w-10">#</th>
                    <th>{{ __('app.name') }}</th>
                    <th>{{ __('app.email') }}</th>
                    <th>{{ __('app.phone') }}</th>
                    <th class="text-right">{{ __('app.total_sales') }}</th>
                    <th class="text-right">{{ __('app.balance') }}</th>
                    <th class="w-20 text-center">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody id="customers-tbody">
                @forelse($customers as $customer)
                <tr data-search="{{ strtolower($customer->name . ' ' . $customer->email . ' ' . $customer->phone) }}">
                    <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>

                    {{-- Name --}}
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-xs font-bold">{{ strtoupper(substr($customer->name, 0, 2)) }}</span>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-slate-800">{{ $customer->name }}</div>
                                @if($customer->city)
                                <div class="text-xs text-slate-400">{{ $customer->city }}</div>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td class="text-sm text-slate-600">{{ $customer->email ?? '—' }}</td>
                    <td class="text-sm text-slate-600">{{ $customer->phone ?? '—' }}</td>

                    <td class="text-right text-sm font-semibold text-slate-700">
                        {{ number_format($customer->total_sales ?? 0, 2) }}
                        <span class="text-xs text-slate-400 font-normal">DH</span>
                    </td>

                    <td class="text-right">
                        @php $bal = $customer->balance ?? 0; @endphp
                        <span class="text-sm font-bold {{ $bal > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ number_format($bal, 2) }}
                            <span class="text-xs font-normal">DH</span>
                        </span>
                    </td>

                    {{-- Actions --}}
                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('customers.show', $customer->id) }}"
                               class="action-btn action-btn-view" title="{{ __('app.view') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('customers.edit', $customer->id) }}"
                               class="action-btn action-btn-edit" title="{{ __('app.edit') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('customers.destroy', $customer->id) }}"
                                  onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <p class="empty-state-title">{{ __('app.no_customers_found') }}</p>
                            <p class="empty-state-desc">{{ __('app.add_first_customer') ?? 'Add your first customer to get started.' }}</p>
                            <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm mt-4">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.add_customer') }}
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
    <div class="pagination">{{ $customers->links() }}</div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function applyFilters() {
    const search = document.getElementById('search').value.toLowerCase();
    document.querySelectorAll('#customers-tbody tr[data-search]').forEach(row => {
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
