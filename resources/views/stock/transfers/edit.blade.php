@extends('layouts.app')
@section('title', __('app.edit') . ' ' . __('app.transfer'))
@php
$pageTitle = __('app.edit') . ' ' . __('app.transfer');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.stock'), 'url' => '#'], ['label' => __('app.transfers'), 'url' => route('stock.transfers.index')], ['label' => __('app.edit'), 'url' => '']];
@endphp
@section('content')
<div class="card max-w-3xl">
    <div class="card-header">
        <h3 class="text-lg font-semibold text-gray-800">{{ __('app.edit') }} {{ __('app.transfer') }}: {{ $transfer->reference }}</h3>
    </div>
    <form method="POST" action="{{ route('stock.transfers.update', $transfer) }}" data-validate>
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="form-group">
                <label class="form-label">{{ __('app.from') }} {{ __('app.warehouse') }} *</label>
                <select name="from_warehouse_id" class="form-control" required>
                    <option value="">{{ __('app.select_warehouse') }}</option>
                    @foreach($warehouses ?? [] as $w)
                        <option value="{{ $w->id }}" {{ $transfer->from_warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
                @error('from_warehouse_id')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('app.to') }} {{ __('app.warehouse') }} *</label>
                <select name="to_warehouse_id" class="form-control" required>
                    <option value="">{{ __('app.select_warehouse') }}</option>
                    @foreach($warehouses ?? [] as $w)
                        <option value="{{ $w->id }}" {{ $transfer->to_warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
                @error('to_warehouse_id')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('app.status') }} *</label>
                <select name="status" class="form-control" required>
                    <option value="pending" {{ $transfer->status == 'pending' ? 'selected' : '' }}>{{ __('app.pending') }}</option>
                    <option value="sent" {{ $transfer->status == 'sent' ? 'selected' : '' }}>{{ __('app.sent') }}</option>
                    <option value="received" {{ $transfer->status == 'received' ? 'selected' : '' }}>{{ __('app.received') }}</option>
                </select>
                @error('status')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('app.date') }}</label>
                <input type="text" class="form-control bg-gray-100" value="{{ $transfer->date?->format('M d, Y') ?? $transfer->created_at->format('M d, Y') }}" disabled>
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label">{{ __('app.notes') }}</label>
                <textarea name="notes" rows="3" class="form-control">{{ old('notes', $transfer->notes) }}</textarea>
                @error('notes')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h4 class="font-semibold text-gray-700 mb-2">{{ __('app.reference') }}</h4>
            <p class="text-sm text-gray-600 font-mono">{{ $transfer->reference }}</p>
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('stock.transfers.show', $transfer) }}" class="btn btn-outline">{{ __('app.cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
        </div>
    </form>
</div>
@endsection
