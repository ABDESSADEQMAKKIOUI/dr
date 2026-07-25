@extends('layouts.platform')
@section('title', __('app.operators'))

@php
$pageTitle = __('app.operators');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('platform.dashboard')],
    ['label' => __('app.operators'), 'url' => route('platform.operators.index')],
];

$roleBadge = [
    'owner'   => 'badge-primary',
    'admin'   => 'badge-info',
    'billing' => 'badge-warning',
    'support' => 'badge-secondary',
];

$currentId = auth()->guard('platform')->id();
@endphp

@section('content')

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">{{ __('app.operators') }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('app.operators_hint') }}</p>
        </div>
        <a href="{{ route('platform.operators.create') }}" class="btn btn-primary btn-sm" style="background:var(--accent);border-color:var(--accent)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.add_operator') }}
        </a>
    </div>

    <div class="filter-bar">
        <div class="filter-group flex-1 min-w-48">
            <label class="filter-label">{{ __('app.search') }}</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                </svg>
                <input type="text" id="search" class="form-control pl-9 py-2" placeholder="{{ __('app.name') }}, {{ __('app.email') }}…">
            </div>
        </div>
        <div class="filter-group">
            <label class="filter-label">{{ __('app.role') }}</label>
            <select id="f-role" class="form-control py-2">
                <option value="">{{ __('app.all') }}</option>
                @foreach(['owner','admin','support','billing'] as $r)
                <option value="{{ $r }}">{{ __('app.role_'.$r) }}</option>
                @endforeach
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
        <table class="table" id="operators-table">
            <thead>
                <tr>
                    <th class="w-10">#</th>
                    <th>{{ __('app.name') }}</th>
                    <th>{{ __('app.role') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th>{{ __('app.last_login') }}</th>
                    <th class="w-20 text-center">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody id="operators-tbody">
                @forelse($operators as $operator)
                <tr data-search="{{ strtolower($operator->name.' '.$operator->email) }}" data-role="{{ $operator->role }}">
                    <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>

                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background:var(--accent)">
                                <span class="text-white text-xs font-bold">{{ strtoupper(substr($operator->name, 0, 2)) }}</span>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-slate-800">
                                    {{ $operator->name }}
                                    @if($operator->id === $currentId)
                                    <span class="text-xs text-slate-400 font-normal">({{ __('app.you') }})</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-400">{{ $operator->email }}</div>
                            </div>
                        </div>
                    </td>

                    <td>
                        <span class="badge {{ $roleBadge[$operator->role] ?? 'badge-secondary' }}">{{ __('app.role_'.$operator->role) }}</span>
                    </td>

                    <td>
                        <span class="badge {{ $operator->is_active ? 'badge-success' : 'badge-secondary' }}">
                            {{ $operator->is_active ? __('app.active') : __('app.inactive') }}
                        </span>
                    </td>

                    <td class="text-sm text-slate-600">
                        {{ $operator->last_login_at ? \Illuminate\Support\Carbon::parse($operator->last_login_at)->format('d/m/Y H:i') : '—' }}
                        @if($operator->last_login_ip)
                        <div class="mono text-slate-400">{{ $operator->last_login_ip }}</div>
                        @endif
                    </td>

                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('platform.operators.edit', $operator->id) }}" class="action-btn action-btn-edit" title="{{ __('app.edit') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            @if($operator->id !== $currentId)
                            <form method="POST" action="{{ route('platform.operators.destroy', $operator->id) }}"
                                  onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn action-btn-delete" title="{{ __('app.delete') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <p class="empty-state-title">{{ __('app.no_operators_found') }}</p>
                            <p class="empty-state-desc">{{ __('app.no_data') }}</p>
                            <a href="{{ route('platform.operators.create') }}" class="btn btn-primary btn-sm mt-4" style="background:var(--accent);border-color:var(--accent)">
                                {{ __('app.add_operator') }}
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($operators instanceof \Illuminate\Pagination\AbstractPaginator && $operators->hasPages())
    <div class="pagination">{{ $operators->links() }}</div>
    @endif
</div>

{{-- ── Roles legend ─────────────────────────────────────────────── --}}
<div class="mc">
    <div class="mc-head">
        <div class="mc-icon" style="background:#f1f5f9">
            <svg fill="none" stroke="#475569" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <div>
            <p class="mc-head-title">{{ __('app.roles') }}</p>
            <p class="mc-head-sub">{{ __('app.roles_hint') }}</p>
        </div>
    </div>
    <div class="mc-body">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach(['owner','admin','support','billing'] as $r)
            <div class="flex items-start gap-3">
                <span class="badge {{ $roleBadge[$r] }} flex-shrink-0">{{ __('app.role_'.$r) }}</span>
                <span class="text-sm text-slate-600">{{ __('app.role_'.$r.'_desc') }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function applyFilters() {
    const q = document.getElementById('search').value.toLowerCase();
    const r = document.getElementById('f-role').value;
    document.querySelectorAll('#operators-tbody tr[data-search]').forEach(row => {
        const ok = (!q || row.dataset.search.includes(q)) && (!r || row.dataset.role === r);
        row.style.display = ok ? '' : 'none';
    });
}
function resetFilters() {
    document.getElementById('search').value = '';
    document.getElementById('f-role').value = '';
    applyFilters();
}
document.getElementById('search').addEventListener('input', applyFilters);
document.getElementById('f-role').addEventListener('change', applyFilters);
</script>
@endpush
