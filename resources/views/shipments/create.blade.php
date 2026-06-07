@extends('layouts.app')
@section('title', __('app.new_shipment'))
@php $pageTitle = __('app.new_shipment'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.shipments'),'url'=>route('shipments.index')],['label'=>__('app.create')]]; @endphp
@section('content')
<div class="card max-w-4xl mx-auto shadow-xl border-t-4 border-blue-600">
    <div class="card-header bg-white"><h3 class="text-xl font-bold text-gray-800">{{ __('app.initialize_shipment') }}</h3></div>
    <form method="POST" action="{{ route('shipments.store') }}" class="p-8 space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.link_to_sale') }}</label>
                <select name="sale_id" class="form-select">
                    <option value="">-- {{ __('app.standalone_shipment') }} --</option>
                    @foreach($sales as $sale)
                        <option value="{{ $sale->id }}">{{ $sale->reference }} ({{ $sale->customer->name ?? 'N/A' }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.customer') }}</label>
                <select name="customer_id" class="form-select">
                    <option value="">-- {{ __('app.select_customer') }} --</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.shipping_carrier') }}</label>
                <input type="text" name="carrier" class="form-input" placeholder="e.g. DHL, FedEx, AMANA">
            </div>
            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.tracking_number') }}</label>
                <input type="text" name="tracking_number" class="form-input" placeholder="e.g. TRK123456789">
            </div>
            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.est_delivery') }}</label>
                <input type="date" name="estimated_delivery" class="form-input">
            </div>
        </div>

        <div>
            <label class="form-label font-bold text-gray-700">{{ __('app.delivery_address') }}</label>
            <textarea name="delivery_address" rows="3" class="form-input" placeholder="{{ __('app.full_address_for_shipping') }}"></textarea>
        </div>

        <div>
            <label class="form-label font-bold text-gray-700">{{ __('app.internal_notes') }}</label>
            <textarea name="notes" rows="2" class="form-input"></textarea>
        </div>

        <div class="flex gap-4 pt-6 mt-4 border-t">
            <button type="submit" class="btn btn-primary px-10">{{ __('app.create_shipment') }}</button>
            <a href="{{ route('shipments.index') }}" class="btn btn-secondary">{{ __('app.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
