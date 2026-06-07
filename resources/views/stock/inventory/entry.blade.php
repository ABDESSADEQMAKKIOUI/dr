@extends('layouts.app')
@section('title', 'Enter Stock Count')
@php $pageTitle = 'Enter Stock Count'; $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>'Stock Counts','url'=>route('stock.inventory.counts')],['label'=>'Count #'.$count->reference]]; @endphp
@section('content')
<div class="card">
    <div class="card-header flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold">Count #{{ $count->reference }}</h3>
            <p class="text-sm text-gray-500">Warehouse: {{ $count->warehouse->name ?? '—' }} | Date: {{ $count->date }}</p>
        </div>
        <span class="badge badge-warning">{{ ucfirst($count->status) }}</span>
    </div>
    <form method="POST" action="{{ route('stock.inventory.count-save', $count) }}" class="p-4">
        @csrf
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4 text-sm text-blue-800">
            Enter the physical quantity you counted for each product. Leave blank for products not counted.
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>System Qty</th>
                        <th>Physical Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $pw)
                    <tr>
                        <td class="font-medium">{{ $pw->product->name ?? '—' }}</td>
                        <td><code>{{ $pw->product->sku ?? '—' }}</code></td>
                        <td>{{ $pw->quantity }}</td>
                        <td>
                            <input type="number"
                                   name="counts[{{ $pw->product_id }}]"
                                   value="{{ $counted[$pw->product_id] ?? '' }}"
                                   class="form-input w-32"
                                   min="0"
                                   placeholder="—">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(count($products) === 0)
        <div class="text-center text-gray-500 py-8">No products found in this warehouse.</div>
        @endif
        <div class="flex gap-3 mt-4">
            <button type="submit" class="btn btn-primary">Save Counts</button>
            <a href="{{ route('stock.inventory.counts') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
