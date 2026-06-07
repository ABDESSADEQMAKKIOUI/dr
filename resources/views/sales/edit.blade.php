@extends('layouts.app')
@section('title', 'Edit Sale')
@php
$pageTitle = 'Edit Sale';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Sales', 'url' => route('sales.index')], ['label' => 'Edit', 'url' => '']];
@endphp
@section('content')
<div class="card max-w-6xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">Edit Sale: {{ $sale->reference }}</h3></div>
    <form method="POST" action="{{ route('sales.update', $sale->id) }}" data-validate>
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="form-group"><label class="form-label">Customer</label><select name="customer_id" class="form-control"><option value="">Walk-in Customer</option>@foreach($customers ?? [] as $c)<option value="{{ $c->id }}" {{ $sale->customer_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Warehouse *</label><select name="warehouse_id" class="form-control" required><option value="">Select Warehouse</option>@foreach($warehouses ?? [] as $w)<option value="{{ $w->id }}" {{ $sale->warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Sale Date *</label><input type="date" name="sale_date" value="{{ old('sale_date', \Carbon\Carbon::parse($sale->date)->format('Y-m-d')) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Tax (%)</label><input type="number" step="0.01" name="tax" value="{{ old('tax', $sale->tax_amount) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Discount</label><input type="number" step="0.01" name="discount" value="{{ old('discount', $sale->discount_amount) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Shipping Cost</label><input type="number" step="0.01" name="shipping" value="{{ old('shipping', $sale->shipping_cost) }}" class="form-control"></div>
        </div>
        <div class="border-t border-gray-200 pt-4 mb-4">
            <h4 class="font-semibold text-gray-800 mb-3">Products</h4>
            <div class="overflow-x-auto"><table class="table"><thead><tr><th>Product</th><th>Quantity</th><th>Unit Price</th><th>Subtotal</th></tr></thead>
            <tbody>@foreach($sale->items ?? [] as $index => $item)<tr><td><select name="products[{{ $index }}][product_id]" class="form-control" required><option value="">Select Product</option>@foreach($products ?? [] as $p)<option value="{{ $p->id }}" {{ $item->product_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach</select></td><td><input type="number" name="products[{{ $index }}][quantity]" value="{{ $item->quantity }}" class="form-control" min="1" required></td><td><input type="number" step="0.01" name="products[{{ $index }}][price]" value="{{ $item->price }}" class="form-control" required></td><td class="font-semibold">{{ number_format($item->quantity * $item->price, 2) }} DH</td></tr>@endforeach</tbody></table></div>
        </div>
        <div class="space-y-4"><div class="form-group"><label class="form-label">Sale Status</label><select name="status" class="form-control"><option value="draft" {{ $sale->status == 'draft' ? 'selected' : '' }}>Draft</option><option value="confirmed" {{ $sale->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option><option value="delivered" {{ $sale->status == 'delivered' ? 'selected' : '' }}>Delivered</option></select></div><div class="form-group"><label class="form-label">Notes</label><textarea name="notes" rows="3" class="form-control">{{ old('notes', $sale->notes) }}</textarea></div></div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('sales.index') }}" class="btn btn-outline">Cancel</a><button type="submit" class="btn btn-primary">Update Sale</button></div>
    </form>
</div>
@endsection
