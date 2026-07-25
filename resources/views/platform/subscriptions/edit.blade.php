@extends('layouts.platform')
@section('title', __('app.edit_subscription'))

@php
/**
 * Payload from App\Http\Controllers\Platform\SubscriptionController@edit:
 *   $subscription (with tenant + plan), $plans
 * Every write goes through UpdateSubscriptionRequest, whose 'action' field is
 * one of change_plan | renew | cancel | update — hence four separate forms
 * rather than one big field editor.
 */
$pageTitle = __('app.edit_subscription');
$breadcrumbs = [
    ['label' => __('app.dashboard'),     'url' => route('platform.dashboard')],
    ['label' => __('app.subscriptions'), 'url' => route('platform.subscriptions.index')],
    ['label' => $subscription->tenant->name ?? __('app.subscription'), 'url' => ''],
];

$badge = [
    'active'    => 'badge-success',
    'trialing'  => 'badge-info',
    'past_due'  => 'badge-warning',
    'cancelled' => 'badge-secondary',
    'expired'   => 'badge-danger',
];

$planList  = $plans ?? collect();
$isClosed  = in_array($subscription->status, ['cancelled', 'expired'], true);
$action    = old('action');
@endphp

@section('content')

{{-- ── Summary ──────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-body">
        <div class="flex flex-col sm:flex-row items-center gap-4">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:var(--accent)">
                <span class="text-white text-xl font-bold">{{ strtoupper(substr($subscription->tenant->name ?? '—', 0, 2)) }}</span>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-3 flex-wrap">
                    <h2 class="text-2xl font-bold text-slate-800">{{ $subscription->tenant->name ?? '—' }}</h2>
                    <span class="badge {{ $badge[$subscription->status] ?? 'badge-secondary' }}">{{ __('app.sub_status_'.$subscription->status) }}</span>
                </div>
                <p class="mono text-slate-500 mt-1">{{ $subscription->tenant->slug ?? '' }}</p>
            </div>
            @platformCan('tenants.view')
            <a href="{{ route('platform.tenants.show', $subscription->tenant_id) }}" class="btn btn-outline btn-sm">
                {{ __('app.tenant_details') }}
            </a>
            @endplatformCan
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── LEFT : actions ─────────────────────────────────────────── --}}
    <div class="xl:col-span-2 space-y-6">

        {{-- Change plan --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:color-mix(in srgb,var(--accent) 14%,transparent)">
                        <svg class="w-4 h-4" fill="none" stroke="var(--accent)" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title">{{ __('app.change_plan') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('app.change_plan_hint') }}</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.subscriptions.update', $subscription->id) }}"
                      onsubmit="return confirm('{{ __('app.confirm_change_plan') }}')">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="change_plan">

                    <div class="form-group">
                        <label class="form-label" for="plan_id">{{ __('app.plan') }} <span class="text-rose-500">*</span></label>
                        <select id="plan_id" name="plan_id" class="form-control @error('plan_id') is-invalid @enderror" required>
                            @forelse($planList as $plan)
                            <option value="{{ $plan->id }}" {{ (string) old('plan_id', $subscription->plan_id) === (string) $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} — {{ number_format($plan->price, 2) }} {{ $plan->currency }} / {{ __('app.period_'.$plan->billing_period) }}
                            </option>
                            @empty
                            <option value="">{{ __('app.no_plans_found') }}</option>
                            @endforelse
                        </select>
                        @error('plan_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-md" style="background:var(--accent);border-color:var(--accent)">
                        {{ __('app.apply') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Editable fields --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">{{ __('app.general') }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('app.subscription_edit_hint') }}</p>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.subscriptions.update', $subscription->id) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="update">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="form-group">
                            <label class="form-label" for="grace_days">{{ __('app.grace_days') }}</label>
                            <input type="number" min="0" max="365" id="grace_days" name="grace_days"
                                   value="{{ $action === 'update' ? old('grace_days', $subscription->grace_days) : $subscription->grace_days }}"
                                   class="form-control @error('grace_days') is-invalid @enderror">
                            <span class="form-hint">{{ __('app.grace_days_hint') }}</span>
                            @error('grace_days')<p class="form-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ __('app.price') }}</label>
                            <input type="text" class="form-control"
                                   value="{{ number_format($subscription->price, 2) }} {{ $subscription->currency }}" disabled>
                            <span class="form-hint">{{ __('app.price_snapshot_hint') }}</span>
                        </div>

                        <div class="form-group md:col-span-2">
                            <label class="form-label" for="notes">{{ __('app.notes') }}</label>
                            <textarea id="notes" name="notes" rows="3" class="form-control">{{ $action === 'update' ? old('notes', $subscription->notes) : $subscription->notes }}</textarea>
                            @error('notes')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-md" style="background:var(--accent);border-color:var(--accent)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('app.save') }}
                    </button>
                </form>
            </div>
        </div>

    </div>

    {{-- ── RIGHT : state + lifecycle ──────────────────────────────── --}}
    <div class="space-y-6">

        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.subscription') }}</h3></div>
            <div class="card-body">
                <div class="pf-kv">
                    <span class="pf-kv-k">{{ __('app.plan') }}</span>
                    <span class="pf-kv-v">{{ $subscription->plan->name ?? '—' }}</span>
                </div>
                <div class="pf-kv">
                    <span class="pf-kv-k">{{ __('app.billing_period') }}</span>
                    <span class="pf-kv-v">{{ __('app.period_'.$subscription->billing_period) }}</span>
                </div>
                <div class="pf-kv">
                    <span class="pf-kv-k">{{ __('app.price') }}</span>
                    <span class="pf-kv-v">{{ number_format($subscription->price, 2) }} {{ $subscription->currency }}</span>
                </div>
                <div class="pf-kv">
                    <span class="pf-kv-k">{{ __('app.start') }}</span>
                    <span class="pf-kv-v">{{ $subscription->starts_at ? \Illuminate\Support\Carbon::parse($subscription->starts_at)->format('d/m/Y') : '—' }}</span>
                </div>
                <div class="pf-kv">
                    <span class="pf-kv-k">{{ __('app.end') }}</span>
                    <span class="pf-kv-v">{{ $subscription->ends_at ? \Illuminate\Support\Carbon::parse($subscription->ends_at)->format('d/m/Y') : __('app.period_lifetime') }}</span>
                </div>
                <div class="pf-kv">
                    <span class="pf-kv-k">{{ __('app.trial_ends_at') }}</span>
                    <span class="pf-kv-v">{{ $subscription->trial_ends_at ? \Illuminate\Support\Carbon::parse($subscription->trial_ends_at)->format('d/m/Y') : '—' }}</span>
                </div>
                <div class="pf-kv">
                    <span class="pf-kv-k">{{ __('app.cancelled_at') }}</span>
                    <span class="pf-kv-v">{{ $subscription->cancelled_at ? \Illuminate\Support\Carbon::parse($subscription->cancelled_at)->format('d/m/Y') : '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Renew --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.renew') }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.subscriptions.update', $subscription->id) }}"
                      onsubmit="return confirm('{{ __('app.confirm_renew') }}')">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="renew">
                    <button type="submit" class="btn btn-success btn-md w-full" {{ $isClosed ? 'disabled' : '' }}>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ __('app.renew') }}
                    </button>
                </form>
                <p class="form-hint mt-2">{{ __('app.renew_hint') }}</p>
            </div>
        </div>

        {{-- Cancel --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title text-rose-600">{{ __('app.cancel_subscription') }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.subscriptions.update', $subscription->id) }}"
                      onsubmit="return confirm('{{ __('app.confirm_cancel_subscription') }}')">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="cancel">

                    <div class="form-group">
                        <label class="form-label" for="reason">{{ __('app.cancellation_reason') }}</label>
                        <textarea id="reason" name="reason" rows="2" class="form-control @error('reason') is-invalid @enderror"></textarea>
                        @error('reason')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="btn btn-danger btn-md w-full" {{ $isClosed ? 'disabled' : '' }}>
                        {{ __('app.cancel_subscription') }}
                    </button>
                </form>
                <p class="form-hint mt-2">{{ __('app.cancel_subscription_hint') }}</p>
            </div>
        </div>

        <a href="{{ route('platform.subscriptions.index') }}" class="btn btn-ghost btn-md w-full text-slate-500">{{ __('app.back_to_list') }}</a>

    </div>
</div>

@endsection
