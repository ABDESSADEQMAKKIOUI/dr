@extends('layouts.app')
@section('title', __('app.stock_counts') ?? 'Stock Counts')

@php
$pageTitle = __('app.stock_counts') ?? 'Stock Counts';
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.stock'),      'url' => '#'],
    ['label' => __('app.inventory'),  'url' => route('stock.inventory.index')],
    ['label' => __('app.stock_counts') ?? 'Stock Counts', 'url' => ''],
];
@endphp

@section('content')

{{-- ── Stats row ────────────────────────────────────────────────── --}}
@php
$total     = $counts->total();
$completed = $counts->getCollection()->where('status', 'completed')->count();
$adjusted  = $counts->getCollection()->where('status', 'adjusted')->count();
$pending   = $counts->getCollection()->whereIn('status', ['draft','counting'])->count();
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-mini">
        <div class="stat-mini-icon bg-indigo-100">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value">{{ $total }}</div>
            <div class="stat-mini-label">{{ __('app.total') }} {{ __('app.counts') ?? 'Counts' }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-emerald-100">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-emerald-600">{{ $completed }}</div>
            <div class="stat-mini-label">{{ __('app.completed') }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-sky-100">
            <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-sky-600">{{ $adjusted }}</div>
            <div class="stat-mini-label">{{ __('app.adjusted') ?? 'Adjusted' }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-amber-100">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-amber-600">{{ $pending }}</div>
            <div class="stat-mini-label">{{ __('app.in_progress') ?? 'In Progress' }}</div>
        </div>
    </div>
</div>

{{-- ── Main card ────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">{{ __('app.stock_counts') ?? 'Stock Counts' }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('app.stock_counts_desc') ?? 'History of all physical stock counts' }}</p>
        </div>
        <a href="{{ route('stock.inventory.count') }}" class="btn btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.new_stock_count') ?? 'New Stock Count' }}
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
                <input type="text" id="search" placeholder="{{ __('app.reference') }}…"
                       class="form-control pl-9 py-2">
            </div>
        </div>
        <div class="filter-group w-44">
            <label class="filter-label">{{ __('app.status') }}</label>
            <select id="filter-status" class="form-control py-2">
                <option value="">{{ __('app.all') }}</option>
                <option value="draft">{{ __('app.draft') }}</option>
                <option value="counting">{{ __('app.counting') ?? 'Counting' }}</option>
                <option value="completed">{{ __('app.completed') }}</option>
                <option value="adjusted">{{ __('app.adjusted') ?? 'Adjusted' }}</option>
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
        <table class="table" id="counts-table">
            <thead>
                <tr>
                    <th class="w-10">#</th>
                    <th>{{ __('app.reference') }}</th>
                    <th>{{ __('app.warehouse') }}</th>
                    <th>{{ __('app.date') }}</th>
                    <th>{{ __('app.created_by') }}</th>
                    <th>{{ __('app.notes') }}</th>
                    <th class="w-28">{{ __('app.status') }}</th>
                    <th class="w-28 text-center">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody id="counts-tbody">
                @forelse($counts as $count)
                @php
                    $statusCfg = match($count->status) {
                        'draft'     => ['badge' => 'badge-secondary', 'label' => __('app.draft')],
                        'counting'  => ['badge' => 'badge-warning',   'label' => __('app.counting') ?? 'Counting'],
                        'completed' => ['badge' => 'badge-success',   'label' => __('app.completed')],
                        'adjusted'  => ['badge' => 'badge-info',      'label' => __('app.adjusted') ?? 'Adjusted'],
                        default     => ['badge' => 'badge-secondary', 'label' => ucfirst($count->status)],
                    };
                @endphp
                <tr data-ref="{{ strtolower($count->reference) }}"
                    data-status="{{ $count->status }}">
                    <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>

                    {{-- Reference --}}
                    <td>
                        <a href="{{ route('stock.inventory.count-show', $count) }}"
                           class="mono text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                            {{ $count->reference }}
                        </a>
                    </td>

                    {{-- Warehouse --}}
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-slate-700">{{ $count->warehouse->name ?? '—' }}</span>
                        </div>
                    </td>

                    {{-- Date --}}
                    <td class="text-sm text-slate-600">
                        {{ \Carbon\Carbon::parse($count->date)->format('d M Y') }}
                    </td>

                    {{-- Created by --}}
                    <td class="text-sm text-slate-600">{{ $count->user->full_name ?? '—' }}</td>

                    {{-- Notes --}}
                    <td class="text-sm text-slate-500 max-w-xs truncate">
                        {{ $count->notes ? \Illuminate\Support\Str::limit($count->notes, 40) : '—' }}
                    </td>

                    {{-- Status --}}
                    <td>
                        <span class="badge {{ $statusCfg['badge'] }}">{{ $statusCfg['label'] }}</span>
                    </td>

                    {{-- Actions --}}
                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('stock.inventory.count-show', $count) }}"
                               class="action-btn action-btn-view" title="{{ __('app.view') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @if(in_array($count->status, ['draft','counting']))
                            <a href="{{ route('stock.inventory.count-entry', $count) }}"
                               class="action-btn action-btn-edit" title="{{ __('app.enter_counts') ?? 'Enter Counts' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            @endif
                            <form method="POST" action="{{ route('stock.inventory.count-destroy', $count) }}"
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="empty-state-title">{{ __('app.no_stock_counts') ?? 'No stock counts yet' }}</p>
                            <p class="empty-state-desc">{{ __('app.create_first_count') ?? 'Create your first physical stock count to get started.' }}</p>
                            <a href="{{ route('stock.inventory.count') }}" class="btn btn-primary btn-sm mt-4">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.new_stock_count') ?? 'New Stock Count' }}
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($counts->hasPages())
    <div class="pagination">{{ $counts->links() }}</div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function applyFilters() {
    const search = document.getElementById('search').value.toLowerCase();
    const status = document.getElementById('filter-status').value;

    document.querySelectorAll('#counts-tbody tr[data-ref]').forEach(row => {
        const matchRef    = !search || row.dataset.ref.includes(search);
        const matchStatus = !status || row.dataset.status === status;
        row.style.display = (matchRef && matchStatus) ? '' : 'none';
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
