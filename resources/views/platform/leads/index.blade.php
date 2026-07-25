@extends('layouts.platform')
@section('title', __('app.demo_requests'))

@php
/**
 * Payload from App\Http\Controllers\Platform\LeadController@index:
 *   $leads (paginator), $search, $status, $source, $statuses, $sources, $counts
 * Filtering is server-side so it applies across every page, not just the
 * rows currently rendered.
 */
$pageTitle = __('app.demo_requests');
$breadcrumbs = [
    ['label' => __('app.dashboard'),      'url' => route('platform.dashboard')],
    ['label' => __('app.demo_requests'),  'url' => route('platform.leads.index')],
];

$search     = $search   ?? '';
$status     = $status   ?? '';
$source     = $source   ?? '';
$statusList = $statuses ?? [];
$sourceList = $sources  ?? [];
$counts     = $counts   ?? [];

$statusBadges = [
    'new'       => 'badge-primary',
    'contacted' => 'badge-info',
    'qualified' => 'badge-warning',
    'converted' => 'badge-success',
    'rejected'  => 'badge-danger',
];
@endphp

@section('content')

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">{{ __('app.demo_requests') }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ __('app.leads_hint') }}
                @if($leads instanceof \Illuminate\Pagination\AbstractPaginator)
                    — {{ $leads->total() }} {{ __('app.total') }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap justify-end">
            @foreach($statusList as $st)
            <a href="{{ route('platform.leads.index', ['status' => $st]) }}"
               class="badge {{ $statusBadges[$st] ?? 'badge-secondary' }}"
               style="{{ $status === $st ? '' : 'opacity:.55' }}">
                {{ __('app.lead_status_'.$st) }} · {{ (int) ($counts[$st] ?? 0) }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- Filter bar (server-side) --}}
    <form method="GET" action="{{ route('platform.leads.index') }}" class="filter-bar">
        <div class="filter-group flex-1 min-w-48">
            <label class="filter-label" for="search">{{ __('app.search') }}</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                </svg>
                <input type="text" id="search" name="search" value="{{ $search }}" class="form-control pl-9 py-2"
                       placeholder="{{ __('app.company') }}, {{ __('app.contact_name') }}, {{ __('app.email') }}…">
            </div>
        </div>
        <div class="filter-group">
            <label class="filter-label" for="status">{{ __('app.status') }}</label>
            <select id="status" name="status" class="form-control py-2">
                <option value="">{{ __('app.all') }}</option>
                @foreach($statusList as $st)
                <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>{{ __('app.lead_status_'.$st) }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label class="filter-label" for="source">{{ __('app.source') }}</label>
            <select id="source" name="source" class="form-control py-2">
                <option value="">{{ __('app.all') }}</option>
                @foreach($sourceList as $sc)
                <option value="{{ $sc }}" {{ $source === $sc ? 'selected' : '' }}>{{ __('app.lead_source_'.$sc) }}</option>
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
            <a href="{{ route('platform.leads.index') }}" class="btn btn-ghost btn-sm text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ __('app.reset') }}
            </a>
        </div>
    </form>

    {{-- Table --}}
    <div class="table-wrapper">
        <table class="table" id="leads-table">
            <thead>
                <tr>
                    <th class="w-10">#</th>
                    <th>{{ __('app.company') }}</th>
                    <th>{{ __('app.email') }}</th>
                    <th>{{ __('app.source') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th>{{ __('app.received_at') }}</th>
                    <th class="w-20 text-center">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                <tr>
                    <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>

                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                                 style="background:var(--accent)">
                                <span class="text-white text-xs font-bold">{{ strtoupper(substr($lead->displayName(), 0, 2)) }}</span>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-slate-800">{{ $lead->displayName() }}</div>
                                <div class="text-xs text-slate-400">{{ $lead->contact_name }}</div>
                            </div>
                        </div>
                    </td>

                    <td>
                        <div class="mono text-slate-600">{{ $lead->email }}</div>
                        <div class="mono text-slate-400">{{ $lead->phone ?: '—' }}</div>
                    </td>

                    <td>
                        <span class="badge badge-secondary">{{ __('app.lead_source_'.$lead->source) }}</span>
                        @if($lead->plan)
                        <div class="pf-meta">{{ $lead->plan->name }}</div>
                        @endif
                    </td>

                    <td>
                        <span class="badge {{ $statusBadges[$lead->status] ?? 'badge-secondary' }}">
                            {{ __('app.lead_status_'.$lead->status) }}
                        </span>
                        @if($lead->tenant)
                        <div class="pf-meta">{{ $lead->tenant->name }}</div>
                        @endif
                    </td>

                    <td class="text-sm text-slate-600 whitespace-nowrap">
                        {{ $lead->created_at ? \Illuminate\Support\Carbon::parse($lead->created_at)->format('d/m/Y H:i') : '—' }}
                        @if($lead->handler)
                        <div class="pf-meta">{{ $lead->handler->email }}</div>
                        @endif
                    </td>

                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('platform.leads.show', $lead->id) }}" class="action-btn action-btn-view" title="{{ __('app.view') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @platformCan('leads.manage')
                            <form method="POST" action="{{ route('platform.leads.destroy', $lead->id) }}"
                                  onsubmit="return confirm('{{ __('app.confirm_delete_lead') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn action-btn-delete" title="{{ __('app.delete') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endplatformCan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="empty-state-title">{{ __('app.no_leads_found') }}</p>
                            <p class="empty-state-desc">{{ __('app.no_data') }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($leads instanceof \Illuminate\Pagination\AbstractPaginator && $leads->hasPages())
    <div class="pagination">{{ $leads->links() }}</div>
    @endif
</div>

@endsection
