@extends('layouts.platform')
@section('title', __('app.edit_tenant'))

@php
$pageTitle = __('app.edit_tenant');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('platform.dashboard')],
    ['label' => __('app.tenants'),   'url' => route('platform.tenants.index')],
    ['label' => $tenant->name,       'url' => route('platform.tenants.show', $tenant->id)],
    ['label' => __('app.edit'),      'url' => ''],
];

$locales    = ['fr' => 'Français', 'en' => 'English', 'ar' => 'العربية'];
$currencies = ['MAD' => 'MAD', 'EUR' => 'EUR', 'USD' => 'USD'];
$timezones  = ['Africa/Casablanca', 'Europe/Paris', 'Europe/Madrid', 'UTC'];
@endphp

@section('content')
<form method="POST" action="{{ route('platform.tenants.update', $tenant->id) }}" id="tenant-form">
@csrf
@method('PUT')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── LEFT ───────────────────────────────────────────────────── --}}
    <div class="xl:col-span-2 space-y-6">

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
                        <input type="text" id="name" name="name" value="{{ old('name', $tenant->name) }}" required
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('app.domain') }}</label>
                        <input type="text" class="form-control" value="{{ $tenant->slug }}.{{ config('tenancy.root_domain') }}" disabled>
                        <span class="form-hint">{{ __('app.slug_immutable_hint') }}</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('app.tenant_database') }}</label>
                        <input type="text" class="form-control mono" value="{{ $tenant->database_name }}" disabled>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_name">{{ __('app.contact_name') }}</label>
                        <input type="text" id="contact_name" name="contact_name" value="{{ old('contact_name', $tenant->contact_name) }}"
                               class="form-control @error('contact_name') is-invalid @enderror">
                        @error('contact_name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_email">{{ __('app.contact_email') }} <span class="text-rose-500">*</span></label>
                        <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', $tenant->contact_email) }}" required
                               class="form-control @error('contact_email') is-invalid @enderror">
                        @error('contact_email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_phone">{{ __('app.contact_phone') }}</label>
                        <input type="text" id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $tenant->contact_phone) }}"
                               class="form-control @error('contact_phone') is-invalid @enderror">
                        @error('contact_phone')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="locale">{{ __('app.language') }} <span class="text-rose-500">*</span></label>
                        <select id="locale" name="locale" class="form-control @error('locale') is-invalid @enderror" required>
                            @foreach($locales as $code => $label)
                            <option value="{{ $code }}" {{ old('locale', $tenant->locale) === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('locale')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="currency">{{ __('app.currency') }} <span class="text-rose-500">*</span></label>
                        <select id="currency" name="currency" class="form-control @error('currency') is-invalid @enderror" required>
                            @foreach($currencies as $code => $label)
                            <option value="{{ $code }}" {{ old('currency', $tenant->currency) === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('currency')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="timezone">{{ __('app.timezone') }} <span class="text-rose-500">*</span></label>
                        <select id="timezone" name="timezone" class="form-control @error('timezone') is-invalid @enderror" required>
                            @foreach($timezones as $tz)
                            <option value="{{ $tz }}" {{ old('timezone', $tenant->timezone) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                        @error('timezone')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group md:col-span-2">
                        <label class="form-label" for="notes">{{ __('app.notes') }}</label>
                        <textarea id="notes" name="notes" rows="3" class="form-control">{{ old('notes', $tenant->notes) }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        @if($tenant->provision_error)
        <div class="card">
            <div class="card-header"><h3 class="card-title text-rose-600">{{ __('app.provision_error') }}</h3></div>
            <div class="card-body">
                <p class="mono text-slate-700" style="white-space:pre-wrap;word-break:break-word">{{ $tenant->provision_error }}</p>
            </div>
        </div>
        @endif

    </div>

    {{-- ── RIGHT ──────────────────────────────────────────────────── --}}
    <div class="space-y-6">

        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.subscription') }}</h3></div>
            <div class="card-body">
                <div class="pf-kv">
                    <span class="pf-kv-k">{{ __('app.plan') }}</span>
                    <span class="pf-kv-v">{{ $tenant->plan->name ?? '—' }}</span>
                </div>
                <p class="form-hint mt-3">{{ __('app.plan_change_hint') }}</p>
                @platformCan('subscriptions.view')
                <a href="{{ route('platform.subscriptions.index') }}" class="btn btn-outline btn-sm mt-2">{{ __('app.subscriptions') }}</a>
                @endplatformCan
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.status') }}</h3></div>
            <div class="card-body">
                <div class="pf-kv">
                    <span class="pf-kv-k">{{ __('app.status') }}</span>
                    <span class="pf-kv-v">{{ __('app.tenant_status_'.$tenant->status) }}</span>
                </div>
                <div class="pf-kv">
                    <span class="pf-kv-k">{{ __('app.provisioned_at') }}</span>
                    <span class="pf-kv-v">{{ $tenant->provisioned_at ? \Illuminate\Support\Carbon::parse($tenant->provisioned_at)->format('d/m/Y H:i') : '—' }}</span>
                </div>
                <div class="pf-kv">
                    <span class="pf-kv-k">{{ __('app.expires_on') }}</span>
                    <span class="pf-kv-v">{{ $tenant->expires_at ? \Illuminate\Support\Carbon::parse($tenant->expires_at)->format('d/m/Y') : '—' }}</span>
                </div>
                <p class="form-hint mt-3">{{ __('app.status_actions_hint') }}</p>
                <a href="{{ route('platform.tenants.show', $tenant->id) }}" class="btn btn-outline btn-sm mt-2">{{ __('app.tenant_details') }}</a>
            </div>
        </div>

    </div>
</div>

<div class="sticky bottom-4 mt-6 z-10">
    <div class="bg-white/95 backdrop-blur border border-slate-200 rounded-2xl shadow-xl px-6 py-4 flex items-center justify-between gap-4">
        <p class="text-sm text-slate-400 hidden sm:block">{{ __('app.fields_marked_required') }}</p>
        <div class="flex items-center gap-3 ml-auto">
            <a href="{{ route('platform.tenants.index') }}" class="btn btn-outline btn-md">{{ __('app.cancel') }}</a>
            <button type="submit" class="btn btn-primary btn-md" style="background:var(--accent);border-color:var(--accent)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ __('app.save') }}
            </button>
        </div>
    </div>
</div>

</form>
@endsection
