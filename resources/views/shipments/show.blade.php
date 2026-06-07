@extends('layouts.app')
@section('title', __('app.shipment_details'))
@php $pageTitle = __('app.shipment_details'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.shipments'),'url'=>route('shipments.index')],['label'=>__('app.view')]]; @endphp
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="card overflow-hidden shadow-2xl border-none">
        {{-- Hero Header --}}
        <div class="bg-gradient-to-br from-blue-700 to-indigo-900 text-white p-8">
            <div class="flex justify-between items-start">
                <div>
                    <span class="badge badge-light mb-2 font-black uppercase tracking-widest">{{ $shipment->status }}</span>
                    <h2 class="text-4xl font-black italic tracking-tighter">{{ $shipment->reference ?? '#SHP-'.$shipment->id }}</h2>
                    <p class="text-blue-100 mt-2 font-bold">{{ $shipment->carrier ?? 'UNSPECIFIED CARRIER' }} @if($shipment->tracking_number) • {{ $shipment->tracking_number }} @endif</p>
                </div>
                <div class="bg-white/10 p-4 rounded-2xl text-center backdrop-blur-md">
                    <p class="text-[10px] font-black uppercase tracking-widest text-blue-200 mb-1">{{ __('app.delivery_target') }}</p>
                    <p class="text-2xl font-black">{{ $shipment->estimated_delivery ? $shipment->estimated_delivery->format('d M Y') : '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Main Info --}}
        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-12 bg-white">
            <div class="space-y-6">
                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2 block">{{ __('app.customer') }}</label>
                    <p class="text-xl font-black text-gray-800">{{ $shipment->customer->name ?? '—' }}</p>
                    <p class="text-sm text-gray-500">{{ $shipment->customer->email ?? '—' }}</p>
                </div>
                
                @if($shipment->sale)
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 block">{{ __('app.linked_sale') }}</label>
                    <a href="{{ route('sales.show', $shipment->sale_id) }}" class="text-blue-600 font-black flex items-center gap-2 hover:underline">
                        <i class="fas fa-file-invoice"></i> {{ $shipment->sale->reference }}
                    </a>
                </div>
                @endif
            </div>

            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2 block">{{ __('app.shipping_address') }}</label>
                <div class="text-gray-700 leading-relaxed font-bold bg-blue-50/50 p-4 rounded-xl border border-blue-100 italic">
                    {!! nl2br(e($shipment->delivery_address)) !!}
                </div>
            </div>
        </div>

        {{-- Package Contents --}}
        @if($shipment->sale && $shipment->sale->items->count())
        <div class="px-8 pb-8 bg-white">
            <label class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 block">{{ __('app.package_contents') }}</label>
            <table class="w-full text-sm">
                <thead><tr class="text-left border-b-2 border-gray-100"><th class="pb-2">{{ __('app.product') }}</th><th class="pb-2 text-right">{{ __('app.qty') }}</th></tr></thead>
                <tbody>
                    @foreach($shipment->sale->items as $item)
                    <tr class="border-b border-gray-50">
                        <td class="py-3 font-bold text-gray-700">{{ $item->product->name ?? '—' }}</td>
                        <td class="py-3 text-right font-mono">{{ $item->quantity }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Footer Actions --}}
        <div class="p-8 bg-gray-50 flex gap-3 border-t">
            <a href="{{ route('shipments.edit', $shipment) }}" class="btn btn-primary px-8">{{ __('app.update_shipment') }}</a>
            <a href="{{ route('shipments.index') }}" class="btn btn-secondary px-8">{{ __('app.back_to_logistics') }}</a>
        </div>
    </div>
</div>
@endsection
