@extends('layouts.app')
@section('title', __('app.shipments'))
@php $pageTitle = __('app.shipments'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')], ['label'=>__('app.shipments')]]; @endphp
@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
        <h3 class="text-2xl font-bold text-gray-800">{{ __('app.shipping_logistics') }}</h3>
        <a href="{{ route('shipments.create') }}" class="btn btn-primary">+ {{ __('app.new_shipment') }}</a>
    </div>

    {{-- Filters --}}
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="{{ __('app.search_by_ref_or_tracking') }}...">
            </div>
            <div class="w-48">
                <select name="status" class="form-select">
                    <option value="">{{ __('app.all_statuses') }}</option>
                    @foreach(['pending', 'shipped', 'delivered', 'cancelled'] as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ __('app.' . $st) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">{{ __('app.filter') }}</button>
            <a href="{{ route('shipments.index') }}" class="btn btn-light"><i class="fas fa-undo"></i></a>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('app.reference') }}</th>
                        <th>{{ __('app.sale') }}</th>
                        <th>{{ __('app.customer') }}</th>
                        <th>{{ __('app.carrier') }}</th>
                        <th>{{ __('app.tracking') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th>{{ __('app.estimated_delivery') }}</th>
                        <th class="text-right">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipments as $shipment)
                    <tr class="hover:bg-gray-50 border-l-4 @if($shipment->status == 'delivered') border-green-500 @elseif($shipment->status == 'shipped') border-blue-500 @else border-gray-200 @endif">
                        <td class="font-mono text-xs font-bold">{{ $shipment->reference ?? '#SHP-'.$shipment->id }}</td>
                        <td>
                            @if($shipment->sale)
                                <a href="{{ route('sales.show', $shipment->sale_id) }}" class="text-blue-600 font-bold hover:underline">{{ $shipment->sale->reference }}</a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="font-bold text-gray-700">{{ $shipment->customer->name ?? '—' }}</td>
                        <td><span class="text-xs uppercase font-black text-gray-500 italic">{{ $shipment->carrier ?? '—' }}</span></td>
                        <td><code>{{ $shipment->tracking_number ?? '—' }}</code></td>
                        <td>
                            <span class="badge @if($shipment->status == 'delivered') badge-success @elseif($shipment->status == 'shipped') badge-primary @elseif($shipment->status == 'cancelled') badge-danger @else badge-secondary @endif">
                                {{ __('app.' . $shipment->status) }}
                            </span>
                        </td>
                        <td class="text-sm font-medium text-gray-600">
                            {{ $shipment->estimated_delivery ? $shipment->estimated_delivery->format('d/m/Y') : '—' }}
                        </td>
                        <td class="text-right flex space-x-2 justify-end">
                            <a href="{{ route('shipments.show', $shipment) }}" class="text-blue-600 hover:text-blue-900"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('shipments.edit', $shipment) }}" class="text-green-600 hover:text-green-900"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('shipments.destroy', $shipment) }}" method="POST" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-gray-400 py-12 italic">{{ __('app.no_shipments_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $shipments->links() }}</div>
    </div>
</div>
@endsection
