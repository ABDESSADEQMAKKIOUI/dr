@extends('layouts.platform')
@section('title', __('app.add_plan'))

@php
$pageTitle = __('app.add_plan');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('platform.dashboard')],
    ['label' => __('app.plans'),     'url' => route('platform.plans.index')],
    ['label' => __('app.add_plan'),  'url' => ''],
];

$periods    = ['monthly', 'quarterly', 'yearly', 'lifetime'];
$currencies = ['MAD', 'EUR', 'USD'];
$features   = array_values(array_filter((array) old('features', [])));
@endphp

@section('content')
<form method="POST" action="{{ route('platform.plans.store') }}">
@csrf

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <div class="xl:col-span-2 space-y-6">

        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:color-mix(in srgb,var(--accent) 14%,transparent)">
                        <svg class="w-4 h-4" fill="none" stroke="var(--accent)" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title">{{ __('app.general') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('app.plan_general_hint') }}</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div class="form-group">
                        <label class="form-label" for="name">{{ __('app.name') }} <span class="text-rose-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="form-control @error('name') is-invalid @enderror" placeholder="Business">
                        @error('name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="slug">{{ __('app.code') }} <span class="text-rose-500">*</span></label>
                        <input type="text" id="slug" name="slug" value="{{ old('slug') }}" required
                               class="form-control @error('slug') is-invalid @enderror" placeholder="business">
                        <span class="form-hint">{{ __('app.plan_slug_hint') }}</span>
                        @error('slug')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group md:col-span-2">
                        <label class="form-label" for="description">{{ __('app.description') }}</label>
                        <textarea id="description" name="description" rows="2" class="form-control">{{ old('description') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="price">{{ __('app.price') }} <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="0" id="price" name="price" value="{{ old('price', '0.00') }}" required
                               class="form-control @error('price') is-invalid @enderror">
                        @error('price')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="currency">{{ __('app.currency') }} <span class="text-rose-500">*</span></label>
                        <select id="currency" name="currency" class="form-control @error('currency') is-invalid @enderror" required>
                            @foreach($currencies as $c)
                            <option value="{{ $c }}" {{ old('currency', 'MAD') === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                        @error('currency')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="billing_period">{{ __('app.billing_period') }} <span class="text-rose-500">*</span></label>
                        <select id="billing_period" name="billing_period" class="form-control @error('billing_period') is-invalid @enderror" required>
                            @foreach($periods as $p)
                            <option value="{{ $p }}" {{ old('billing_period', 'monthly') === $p ? 'selected' : '' }}>{{ __('app.period_'.$p) }}</option>
                            @endforeach
                        </select>
                        @error('billing_period')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="trial_days">{{ __('app.trial_days') }} <span class="text-rose-500">*</span></label>
                        <input type="number" min="0" max="365" id="trial_days" name="trial_days" value="{{ old('trial_days', 0) }}" required
                               class="form-control @error('trial_days') is-invalid @enderror">
                        @error('trial_days')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">{{ __('app.limits') }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('app.limits_hint') }}</p>
                </div>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="form-group">
                        <label class="form-label" for="max_users">{{ __('app.max_users') }}</label>
                        <input type="number" min="1" id="max_users" name="max_users" value="{{ old('max_users') }}"
                               class="form-control @error('max_users') is-invalid @enderror" placeholder="{{ __('app.unlimited') }}">
                        @error('max_users')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="max_warehouses">{{ __('app.max_warehouses') }}</label>
                        <input type="number" min="1" id="max_warehouses" name="max_warehouses" value="{{ old('max_warehouses') }}"
                               class="form-control @error('max_warehouses') is-invalid @enderror" placeholder="{{ __('app.unlimited') }}">
                        @error('max_warehouses')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="max_products">{{ __('app.max_products') }}</label>
                        <input type="number" min="1" id="max_products" name="max_products" value="{{ old('max_products') }}"
                               class="form-control @error('max_products') is-invalid @enderror" placeholder="{{ __('app.unlimited') }}">
                        @error('max_products')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">{{ __('app.features') }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('app.features_hint') }}</p>
                </div>
            </div>
            <div class="card-body" x-data="{ items: @js($features) }">
                <template x-for="(item, i) in items" :key="i">
                    <div class="flex items-center gap-2 mb-2">
                        <input type="text" class="form-control" :name="'features[' + i + ']'" x-model="items[i]">
                        <button type="button" class="btn btn-ghost btn-sm text-rose-500" @click="items.splice(i, 1)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
                <button type="button" class="btn btn-outline btn-sm" @click="items.push('')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('app.add') }}
                </button>
                @error('features')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>

    </div>

    <div class="space-y-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.status') }}</h3></div>
            <div class="card-body">
                <div class="tr" style="padding:.5rem 0">
                    <div>
                        <div class="tr-title">{{ __('app.active') }}</div>
                        <div class="tr-sub">{{ __('app.plan_active_hint') }}</div>
                    </div>
                    <label class="ts">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                        <span class="ts-slider"></span>
                    </label>
                </div>

                <div class="form-group mt-4">
                    <label class="form-label" for="sort_order">{{ __('app.sort_order') }}</label>
                    <input type="number" min="0" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}"
                           class="form-control @error('sort_order') is-invalid @enderror">
                    <span class="form-hint">{{ __('app.sort_order_hint') }}</span>
                    @error('sort_order')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sticky bottom-4 mt-6 z-10">
    <div class="bg-white/95 backdrop-blur border border-slate-200 rounded-2xl shadow-xl px-6 py-4 flex items-center justify-between gap-4">
        <p class="text-sm text-slate-400 hidden sm:block">{{ __('app.fields_marked_required') }}</p>
        <div class="flex items-center gap-3 ml-auto">
            <a href="{{ route('platform.plans.index') }}" class="btn btn-outline btn-md">{{ __('app.cancel') }}</a>
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
