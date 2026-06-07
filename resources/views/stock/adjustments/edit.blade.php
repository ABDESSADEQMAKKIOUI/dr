@extends('layouts.app')
@section('title', __('app.edit') . ' ' . __('app.adjustment'))
@php
$pageTitle = __('app.edit') . ' ' . __('app.adjustment');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.stock'), 'url' => '#'], ['label' => __('app.adjustments'), 'url' => route('stock.adjustments.index')], ['label' => __('app.edit'), 'url' => '']];
@endphp
@section('content')
<div class="card max-w-3xl">
    <div class="card-header">
        <h3 class="text-lg font-semibold text-gray-800">{{ __('app.edit') }} {{ __('app.adjustment') }}: {{ $adjustment->reference }}</h3>
    </div>
    <form method="POST" action="{{ route('stock.adjustments.update', $adjustment) }}" data-validate>
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="form-group">
                <label class="form-label">{{ __('app.warehouse') }} *</label>
                <select name="warehouse_id" class="form-control" required>
                    <option value="">{{ __('app.select_warehouse') }}</option>
                    @foreach($warehouses ?? [] as $w)
                        <option value="{{ $w->id }}" {{ $adjustment->warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
                @error('warehouse_id')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('app.type') }} *</label>
                <select name="type" class="form-control" required>
                    <option value="">{{ __('app.select') }} {{ __('app.type') }}</option>
                    <option value="addition" {{ $adjustment->type == 'addition' ? 'selected' : '' }}>{{ __('app.addition') }} (+)</option>
                    <option value="subtraction" {{ $adjustment->type == 'subtraction' ? 'selected' : '' }}>{{ __('app.subtraction') }} (-)</option>
                    <option value="damage" {{ $adjustment->type == 'damage' ? 'selected' : '' }}>{{ __('app.damage') }}</option>
                    <option value="loss" {{ $adjustment->type == 'loss' ? 'selected' : '' }}>{{ __('app.loss') }}</option>
                </select>
                @error('type')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label">{{ __('app.reason') }} *</label>
                <textarea name="reason" rows="3" class="form-control" required>{{ old('reason', $adjustment->reason) }}</textarea>
                @error('reason')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h4 class="font-semibold text-gray-700 mb-2">{{ __('app.products') }} {{ __('app.info') }}</h4>
            <p class="text-sm text-gray-600">{{ $adjustment->notes ?: __('app.no_results') }}</p>
            <p class="text-xs text-gray-500 mt-2">{{ __('app.notes') }}: Product details cannot be modified after creation.</p>
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('stock.adjustments.show', $adjustment) }}" class="btn btn-outline">{{ __('app.cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
        </div>
    </form>
</div>
@endsection
