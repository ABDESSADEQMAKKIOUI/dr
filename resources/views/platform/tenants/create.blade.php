@extends('layouts.platform')
@section('title', __('app.add_tenant'))

@php
$pageTitle = __('app.add_tenant');
$breadcrumbs = [
    ['label' => __('app.dashboard'),  'url' => route('platform.dashboard')],
    ['label' => __('app.tenants'),    'url' => route('platform.tenants.index')],
    ['label' => __('app.add_tenant'), 'url' => ''],
];

$locales    = ['fr' => 'Français', 'en' => 'English', 'ar' => 'العربية'];
$currencies = ['MAD' => 'MAD', 'EUR' => 'EUR', 'USD' => 'USD'];
$timezones  = ['Africa/Casablanca', 'Europe/Paris', 'Europe/Madrid', 'UTC'];
@endphp

@section('content')
<form method="POST" action="{{ route('platform.tenants.store') }}" id="tenant-form">
@csrf

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── LEFT : main fields ─────────────────────────────────────── --}}
    <div class="xl:col-span-2 space-y-6">

        {{-- Identity --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:color-mix(in srgb,var(--accent) 14%,transparent)">
                        <svg class="w-4 h-4" fill="none" stroke="var(--accent)" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title">{{ __('app.general') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('app.tenant_identity_hint') }}</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div class="form-group md:col-span-2">
                        <label class="form-label" for="name">{{ __('app.company_name') }} <span class="text-rose-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="ACME SARL">
                        @error('name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group md:col-span-2">
                        <label class="form-label" for="slug">{{ __('app.domain') }} <span class="text-rose-500">*</span></label>
                        <div class="flex items-center gap-2">
                            <input type="text" id="slug" name="slug" value="{{ old('slug') }}" required
                                   class="form-control @error('slug') is-invalid @enderror" placeholder="acme">
                            <span class="text-sm text-slate-400 whitespace-nowrap">.{{ config('tenancy.root_domain') }}</span>
                        </div>
                        <span class="form-hint">{{ __('app.slug_hint') }}</span>
                        @error('slug')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_name">{{ __('app.contact_name') }}</label>
                        <input type="text" id="contact_name" name="contact_name" value="{{ old('contact_name') }}"
                               class="form-control @error('contact_name') is-invalid @enderror">
                        @error('contact_name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_email">{{ __('app.contact_email') }} <span class="text-rose-500">*</span></label>
                        <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email') }}" required
                               class="form-control @error('contact_email') is-invalid @enderror">
                        @error('contact_email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_phone">{{ __('app.contact_phone') }}</label>
                        <input type="text" id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}"
                               class="form-control @error('contact_phone') is-invalid @enderror" placeholder="+212 6 00 00 00 00">
                        @error('contact_phone')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="locale">{{ __('app.language') }} <span class="text-rose-500">*</span></label>
                        <select id="locale" name="locale" class="form-control @error('locale') is-invalid @enderror" required>
                            @foreach($locales as $code => $label)
                            <option value="{{ $code }}" {{ old('locale', 'fr') === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('locale')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="currency">{{ __('app.currency') }} <span class="text-rose-500">*</span></label>
                        <select id="currency" name="currency" class="form-control @error('currency') is-invalid @enderror" required>
                            @foreach($currencies as $code => $label)
                            <option value="{{ $code }}" {{ old('currency', 'MAD') === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('currency')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="timezone">{{ __('app.timezone') }} <span class="text-rose-500">*</span></label>
                        <select id="timezone" name="timezone" class="form-control @error('timezone') is-invalid @enderror" required>
                            @foreach($timezones as $tz)
                            <option value="{{ $tz }}" {{ old('timezone', 'Africa/Casablanca') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                        @error('timezone')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group md:col-span-2">
                        <label class="form-label" for="notes">{{ __('app.notes') }}</label>
                        <textarea id="notes" name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        {{-- Administrator --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title">{{ __('app.tenant_admin') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('app.tenant_admin_hint') }}</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div class="form-group">
                        <label class="form-label" for="admin_first_name">{{ __('app.first_name') }} <span class="text-rose-500">*</span></label>
                        <input type="text" id="admin_first_name" name="admin_first_name" value="{{ old('admin_first_name') }}" required
                               class="form-control @error('admin_first_name') is-invalid @enderror">
                        @error('admin_first_name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="admin_last_name">{{ __('app.last_name') }} <span class="text-rose-500">*</span></label>
                        <input type="text" id="admin_last_name" name="admin_last_name" value="{{ old('admin_last_name') }}" required
                               class="form-control @error('admin_last_name') is-invalid @enderror">
                        @error('admin_last_name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="admin_email">{{ __('app.email') }} <span class="text-rose-500">*</span></label>
                        <input type="email" id="admin_email" name="admin_email" value="{{ old('admin_email') }}" required
                               class="form-control @error('admin_email') is-invalid @enderror" autocomplete="off">
                        <span class="form-hint">{{ __('app.tenant_admin_email_hint') }}</span>
                        @error('admin_email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="admin_phone">{{ __('app.phone') }}</label>
                        <input type="text" id="admin_phone" name="admin_phone" value="{{ old('admin_phone') }}"
                               class="form-control @error('admin_phone') is-invalid @enderror">
                        @error('admin_phone')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="admin_password">{{ __('app.password') }} <span class="text-rose-500">*</span></label>
                        <input type="password" id="admin_password" name="admin_password" required
                               class="form-control @error('admin_password') is-invalid @enderror" autocomplete="new-password">
                        <span class="form-hint">{{ __('app.password_rules_hint') }}</span>
                        @error('admin_password')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="admin_password_confirmation">{{ __('app.confirm_password') }} <span class="text-rose-500">*</span></label>
                        <input type="password" id="admin_password_confirmation" name="admin_password_confirmation" required
                               class="form-control" autocomplete="new-password">
                    </div>

                </div>
            </div>
        </div>

        {{-- Warehouse --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title">{{ __('app.default_warehouse') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('app.default_warehouse_hint') }}</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="form-group">
                        <label class="form-label" for="warehouse_name">{{ __('app.name') }}</label>
                        <input type="text" id="warehouse_name" name="warehouse_name" value="{{ old('warehouse_name') }}"
                               class="form-control @error('warehouse_name') is-invalid @enderror"
                               placeholder="{{ __('app.company_name') }}">
                        @error('warehouse_name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="warehouse_code">{{ __('app.code') }}</label>
                        <input type="text" id="warehouse_code" name="warehouse_code" value="{{ old('warehouse_code') }}"
                               class="form-control @error('warehouse_code') is-invalid @enderror" placeholder="WH-001">
                        @error('warehouse_code')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── RIGHT : subscription ───────────────────────────────────── --}}
    <div class="space-y-6">

        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.subscription') }}</h3></div>
            <div class="card-body">

                <div class="form-group">
                    <label class="form-label" for="plan_id">{{ __('app.plan') }} <span class="text-rose-500">*</span></label>
                    <select id="plan_id" name="plan_id" class="form-control @error('plan_id') is-invalid @enderror" required>
                        <option value="">{{ __('app.select') }}</option>
                        @foreach(($plans ?? collect()) as $plan)
                        <option value="{{ $plan->id }}"
                                data-trial="{{ $plan->trial_days }}"
                                {{ (string) old('plan_id') === (string) $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }} — {{ number_format($plan->price, 2) }} {{ $plan->currency }} / {{ __('app.period_'.$plan->billing_period) }}
                        </option>
                        @endforeach
                    </select>
                    @error('plan_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="trial_days">{{ __('app.trial_days') }}</label>
                    <input type="number" id="trial_days" name="trial_days" min="0" max="365"
                           value="{{ old('trial_days') }}"
                           class="form-control @error('trial_days') is-invalid @enderror">
                    <span class="form-hint">{{ __('app.trial_days_hint') }}</span>
                    @error('trial_days')<p class="form-error">{{ $message }}</p>@enderror
                </div>

            </div>
        </div>

        <div class="mc" style="margin-bottom:0">
            <div class="mc-head">
                <div class="mc-icon" style="background:#fef3c7">
                    <svg fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="mc-head-title">{{ __('app.provisioning') }}</p>
                    <p class="mc-head-sub">{{ __('app.provisioning_notice_title') }}</p>
                </div>
            </div>
            <div class="mc-body">
                <p class="text-sm text-slate-600">{{ __('app.provisioning_notice') }}</p>
            </div>
        </div>

    </div>
</div>

{{-- ── Sticky bottom action bar ──────────────────────────────────── --}}
<div class="sticky bottom-4 mt-6 z-10">
    <div class="bg-white/95 backdrop-blur border border-slate-200 rounded-2xl shadow-xl px-6 py-4 flex items-center justify-between gap-4">
        <p class="text-sm text-slate-400 hidden sm:block">{{ __('app.fields_marked_required') }}</p>
        <div class="flex items-center gap-3 ml-auto">
            <a href="{{ route('platform.tenants.index') }}" class="btn btn-outline btn-md">{{ __('app.cancel') }}</a>
            <button type="submit" class="btn btn-primary btn-md" style="background:var(--accent);border-color:var(--accent)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ __('app.provision_tenant') }}
            </button>
        </div>
    </div>
</div>

</form>
@endsection

@push('scripts')
<script>
(function () {
    const name = document.getElementById('name');
    const slug = document.getElementById('slug');
    let slugTouched = slug.value.length > 0;

    slug.addEventListener('input', function () { slugTouched = true; });

    name.addEventListener('input', function () {
        if (slugTouched) return;
        slug.value = name.value
            .toLowerCase()
            .normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .substring(0, 63);
    });

    const plan  = document.getElementById('plan_id');
    const trial = document.getElementById('trial_days');
    plan.addEventListener('change', function () {
        const opt = plan.options[plan.selectedIndex];
        if (opt && opt.dataset.trial !== undefined && trial.value === '') {
            trial.value = opt.dataset.trial;
        }
    });
})();
</script>
@endpush
