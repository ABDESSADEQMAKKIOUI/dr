@extends('layouts.app')
@section('title', __('app.edit_shipment'))
@php $pageTitle = __('app.edit_shipment'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.shipments'),'url'=>route('shipments.index')],['label'=>__('app.edit')]]; @endphp
@section('content')
<div class="card max-w-4xl mx-auto shadow-xl border-t-4 border-green-500">
    <div class="card-header bg-white">
        <h3 class="text-xl font-bold text-gray-800">{{ __('app.update_shipment') }}: {{ $shipment->reference }}</h3>
    </div>
    <form method="POST" action="{{ route('shipments.update', $shipment) }}" class="p-8 space-y-6">
        @csrf @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.status') }} *</label>
                <select name="status" class="form-select font-black text-blue-600">
                    @foreach(['pending', 'shipped', 'delivered', 'cancelled'] as $st)
                        <option value="{{ $st }}" {{ old('status', $shipment->status) == $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.delivered_at') }}</label>
                <input type="datetime-local" name="delivered_at" value="{{ old('delivered_at', $shipment->delivered_at ? $shipment->delivered_at->format('Y-m-d\TH:i') : '') }}" class="form-input">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.carrier') }}</label>
                <input type="text" name="carrier" value="{{ old('carrier', $shipment->carrier) }}" class="form-input">
            </div>
            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.tracking_number') }}</label>
                <input type="text" name="tracking_number" value="{{ old('tracking_number', $shipment->tracking_number) }}" class="form-input">
            </div>
            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.est_delivery') }}</label>
                <input type="date" name="estimated_delivery" value="{{ old('estimated_delivery', $shipment->estimated_delivery ? $shipment->estimated_delivery->format('Y-m-d') : '') }}" class="form-input">
            </div>
        </div>

        <div>
            <label class="form-label font-bold text-gray-700">{{ __('app.delivery_address') }}</label>
            <textarea name="delivery_address" rows="3" class="form-input">{{ old('delivery_address', $shipment->delivery_address) }}</textarea>
        </div>

        <div>
            <label class="form-label font-bold text-gray-700">{{ __('app.notes') }}</label>
            <textarea name="notes" rows="2" class="form-input">{{ old('notes', $shipment->notes) }}</textarea>
        </div>

        <div class="flex gap-4 pt-6 border-t">
            <button type="submit" class="btn btn-primary px-10">{{ __('app.update_shipment') }}</button>
            <a href="{{ route('shipments.show', $shipment) }}" class="btn btn-secondary">{{ __('app.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
