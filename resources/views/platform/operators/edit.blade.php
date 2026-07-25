@extends('layouts.platform')
@section('title', __('app.edit_operator'))

@php
$pageTitle = __('app.edit_operator');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('platform.dashboard')],
    ['label' => __('app.operators'), 'url' => route('platform.operators.index')],
    ['label' => $operator->name,     'url' => ''],
];

$roles     = $roles ?? ['owner', 'admin', 'support', 'billing'];
$isCurrent = $operator->id === auth()->guard('platform')->id();
@endphp

@section('content')
<form method="POST" action="{{ route('platform.operators.update', $operator->id) }}">
@csrf
@method('PUT')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <div class="xl:col-span-2 space-y-6">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:color-mix(in srgb,var(--accent) 14%,transparent)">
                        <svg class="w-4 h-4" fill="none" stroke="var(--accent)" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title">{{ __('app.operator') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('app.operator_hint') }}</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div class="form-group md:col-span-2">
                        <label class="form-label" for="name">{{ __('app.name') }} <span class="text-rose-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $operator->name) }}" required
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group md:col-span-2">
                        <label class="form-label" for="email">{{ __('app.email') }} <span class="text-rose-500">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email', $operator->email) }}" required
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">{{ __('app.password') }}</label>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                        <span class="form-hint">{{ __('app.password_leave_blank_hint') }}</span>
                        @error('password')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">{{ __('app.confirm_password') }}</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control" autocomplete="new-password">
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.role') }}</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label" for="role">{{ __('app.role') }} <span class="text-rose-500">*</span></label>
                    <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required {{ $isCurrent ? 'disabled' : '' }}>
                        @foreach($roles as $r)
                        <option value="{{ $r }}" {{ old('role', $operator->role) === $r ? 'selected' : '' }}>{{ __('app.role_'.$r) }}</option>
                        @endforeach
                    </select>
                    @if($isCurrent)
                    <input type="hidden" name="role" value="{{ $operator->role }}">
                    <span class="form-hint">{{ __('app.own_role_locked_hint') }}</span>
                    @endif
                    @error('role')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="divider"></div>

                @foreach($roles as $r)
                <p class="text-xs text-slate-500 mb-2"><strong class="text-slate-700">{{ __('app.role_'.$r) }}</strong> — {{ __('app.role_'.$r.'_desc') }}</p>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.status') }}</h3></div>
            <div class="card-body">
                <div class="tr" style="padding:.5rem 0">
                    <div>
                        <div class="tr-title">{{ __('app.active') }}</div>
                        <div class="tr-sub">{{ __('app.operator_active_hint') }}</div>
                    </div>
                    <label class="ts">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $operator->is_active) ? 'checked' : '' }} {{ $isCurrent ? 'disabled' : '' }}>
                        <span class="ts-slider"></span>
                    </label>
                </div>
                @if($isCurrent)
                <input type="hidden" name="is_active" value="1">
                <p class="form-hint mt-2">{{ __('app.own_account_locked_hint') }}</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.record_info') }}</h3></div>
            <div class="card-body">
                <div class="pf-kv">
                    <span class="pf-kv-k">{{ __('app.last_login') }}</span>
                    <span class="pf-kv-v">{{ $operator->last_login_at ? \Illuminate\Support\Carbon::parse($operator->last_login_at)->format('d/m/Y H:i') : '—' }}</span>
                </div>
                <div class="pf-kv">
                    <span class="pf-kv-k">{{ __('app.ip_address') }}</span>
                    <span class="pf-kv-v mono">{{ $operator->last_login_ip ?? '—' }}</span>
                </div>
                <div class="pf-kv">
                    <span class="pf-kv-k">{{ __('app.created_at') }}</span>
                    <span class="pf-kv-v">{{ $operator->created_at ? \Illuminate\Support\Carbon::parse($operator->created_at)->format('d/m/Y') : '—' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sticky bottom-4 mt-6 z-10">
    <div class="bg-white/95 backdrop-blur border border-slate-200 rounded-2xl shadow-xl px-6 py-4 flex items-center justify-between gap-4">
        <p class="text-sm text-slate-400 hidden sm:block">{{ __('app.fields_marked_required') }}</p>
        <div class="flex items-center gap-3 ml-auto">
            <a href="{{ route('platform.operators.index') }}" class="btn btn-outline btn-md">{{ __('app.cancel') }}</a>
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
