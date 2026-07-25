@extends('layouts.platform')
@section('title', $lead->displayName())

@php
/**
 * Payload from App\Http\Controllers\Platform\LeadController@show:
 *   $lead, $statuses, $prefill
 *
 * $prefill is the query string handed to the existing tenant-create form; this
 * screen never provisions anything itself.
 */
$pageTitle = $lead->displayName();
$breadcrumbs = [
    ['label' => __('app.dashboard'),     'url' => route('platform.dashboard')],
    ['label' => __('app.demo_requests'), 'url' => route('platform.leads.index')],
    ['label' => $lead->displayName(),    'url' => ''],
];

$statusList = $statuses ?? [];
$prefill    = $prefill  ?? [];

$statusBadges = [
    'new'       => 'badge-primary',
    'contacted' => 'badge-info',
    'qualified' => 'badge-warning',
    'converted' => 'badge-success',
    'rejected'  => 'badge-danger',
];
@endphp

@section('content')

{{-- ── Hero ─────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-body">
        <div class="flex flex-col sm:flex-row items-center gap-4">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:var(--accent)">
                <span class="text-white text-xl font-bold">{{ strtoupper(substr($lead->displayName(), 0, 2)) }}</span>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-3 flex-wrap">
                    <h2 class="text-2xl font-bold text-slate-800">{{ $lead->displayName() }}</h2>
                    <span class="badge {{ $statusBadges[$lead->status] ?? 'badge-secondary' }}">
                        {{ __('app.lead_status_'.$lead->status) }}
                    </span>
                    <span class="badge badge-secondary">{{ __('app.lead_source_'.$lead->source) }}</span>
                </div>
                <p class="mono text-slate-500 mt-1">{{ $lead->email }}</p>
                <p class="pf-meta">
                    {{ $lead->contact_name }}@if($lead->phone) · {{ $lead->phone }}@endif
                    · {{ $lead->created_at ? \Illuminate\Support\Carbon::parse($lead->created_at)->format('d/m/Y H:i') : '—' }}
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap justify-end">
                <a href="mailto:{{ $lead->email }}" class="btn btn-outline btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ __('app.email') }}
                </a>
                @platformCan('tenants.create')
                @if(! $lead->isConverted())
                <a href="{{ route('platform.tenants.create', $prefill) }}" class="btn btn-primary btn-sm" style="background:var(--accent);border-color:var(--accent)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    {{ __('app.convert_to_tenant') }}
                </a>
                @endif
                @endplatformCan
            </div>
        </div>
    </div>
</div>

@if($lead->tenant)
<div class="alert alert-success">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div class="flex-1">
        <p class="font-semibold mb-1">{{ __('app.lead_already_converted') }}</p>
        <p>
            @platformCan('tenants.view')
            <a href="{{ route('platform.tenants.show', $lead->tenant_id) }}" class="hover:text-blue-600">{{ $lead->tenant->name }}</a>
            @else
            {{ $lead->tenant->name }}
            @endplatformCan
        </p>
    </div>
</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── LEFT ───────────────────────────────────────────────────── --}}
    <div class="xl:col-span-2 space-y-6">

        {{-- Submitted enquiry --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.lead_details') }}</h3></div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.company') }}</span>
                            <span class="pf-kv-v">{{ $lead->company_name ?: '—' }}</span>
                        </div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.contact_name') }}</span>
                            <span class="pf-kv-v">{{ $lead->contact_name }}</span>
                        </div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.email') }}</span>
                            <span class="pf-kv-v mono">{{ $lead->email }}</span>
                        </div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.phone') }}</span>
                            <span class="pf-kv-v mono">{{ $lead->phone ?: '—' }}</span>
                        </div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.plan') }}</span>
                            <span class="pf-kv-v">{{ $lead->plan->name ?? '—' }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.source') }}</span>
                            <span class="pf-kv-v">{{ __('app.lead_source_'.$lead->source) }}</span>
                        </div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.received_at') }}</span>
                            <span class="pf-kv-v">{{ $lead->created_at ? \Illuminate\Support\Carbon::parse($lead->created_at)->format('d/m/Y H:i') : '—' }}</span>
                        </div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.handled_by') }}</span>
                            <span class="pf-kv-v">{{ $lead->handler->email ?? '—' }}</span>
                        </div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.handled_at') }}</span>
                            <span class="pf-kv-v">{{ $lead->handled_at ? \Illuminate\Support\Carbon::parse($lead->handled_at)->format('d/m/Y H:i') : '—' }}</span>
                        </div>
                        <div class="pf-kv">
                            <span class="pf-kv-k">{{ __('app.ip_address') }}</span>
                            <span class="pf-kv-v mono">{{ $lead->ip_address ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                @if($lead->message)
                <div class="divider"></div>
                <p class="form-label">{{ __('app.message') }}</p>
                <p class="text-sm text-slate-600" style="white-space:pre-wrap">{{ $lead->message }}</p>
                @endif

                @if($lead->user_agent)
                <div class="divider"></div>
                <p class="pf-meta mono" style="word-break:break-word">{{ $lead->user_agent }}</p>
                @endif
            </div>
        </div>

        {{-- Handling: status + notes --}}
        @platformCan('leads.manage')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('app.lead_handling') }}</h3>
            </div>
            <form method="POST" action="{{ route('platform.leads.update', $lead->id) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label" for="status">{{ __('app.status') }}</label>
                        <select id="status" name="status" class="form-control" required>
                            @foreach($statusList as $st)
                            <option value="{{ $st }}" {{ old('status', $lead->status) === $st ? 'selected' : '' }}>
                                {{ __('app.lead_status_'.$st) }}
                            </option>
                            @endforeach
                        </select>
                        <p class="form-hint">{{ __('app.lead_status_hint') }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="notes">{{ __('app.internal_notes') }}</label>
                        <textarea id="notes" name="notes" rows="5" class="form-control">{{ old('notes', $lead->notes) }}</textarea>
                        <p class="form-hint">{{ __('app.lead_notes_hint') }}</p>
                    </div>

                    <button type="submit" class="btn btn-primary btn-md" style="background:var(--accent);border-color:var(--accent)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('app.save') }}
                    </button>
                </div>
            </form>
        </div>
        @else
        @if($lead->notes)
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.internal_notes') }}</h3></div>
            <div class="card-body">
                <p class="text-sm text-slate-600" style="white-space:pre-wrap">{{ $lead->notes }}</p>
            </div>
        </div>
        @endif
        @endplatformCan

    </div>

    {{-- ── RIGHT : conversion + danger zone ───────────────────────── --}}
    <div class="space-y-6">

        @platformCan('tenants.create')
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.conversion') }}</h3></div>
            <div class="card-body">
                @if($lead->isConverted() && $lead->tenant_id)
                    @platformCan('tenants.view')
                    <a href="{{ route('platform.tenants.show', $lead->tenant_id) }}" class="btn btn-outline btn-md w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                        </svg>
                        {{ __('app.open_tenant') }}
                    </a>
                    @endplatformCan
                    <p class="form-hint mt-2">{{ __('app.lead_already_converted') }}</p>
                @else
                    <a href="{{ route('platform.tenants.create', $prefill) }}" class="btn btn-success btn-md w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        {{ __('app.convert_to_tenant') }}
                    </a>
                    <p class="form-hint mt-2">{{ __('app.convert_to_tenant_hint') }}</p>
                @endif
            </div>
        </div>
        @endplatformCan

        @platformCan('leads.manage')
        <div class="card">
            <div class="card-header"><h3 class="card-title text-rose-600">{{ __('app.danger_zone') }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.leads.destroy', $lead->id) }}"
                      onsubmit="return confirm('{{ __('app.confirm_delete_lead') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-md w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        {{ __('app.delete') }}
                    </button>
                </form>
                <p class="form-hint mt-2">{{ __('app.delete_lead_hint') }}</p>
            </div>
        </div>
        @endplatformCan

        <a href="{{ route('platform.leads.index') }}" class="btn btn-ghost btn-md w-full text-slate-500">{{ __('app.back_to_list') }}</a>

    </div>
</div>

@endsection
