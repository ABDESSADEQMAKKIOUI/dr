@extends('layouts.app')
@section('title', 'Edit Quotation')
@php
$pageTitle = 'Edit Quotation';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Quotations', 'url' => route('quotations.index')], ['label' => 'Edit', 'url' => '']];
@endphp
@section('content')
<div class="card max-w-6xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">Edit Quotation: {{ $quotation->reference }}</h3></div>
    <form method="POST" action="{{ route('quotations.update', $quotation->id) }}" data-validate>
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="form-group"><label class="form-label">Customer *</label><select name="customer_id" class="form-control" required><option value="">Select Customer</option>@foreach($customers ?? [] as $c)<option value="{{ $c->id }}" {{ $quotation->customer_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Quotation Date *</label><input type="date" name="quotation_date" value="{{ old('quotation_date', $quotation->quotation_date->format('Y-m-d')) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Valid Until *</label><input type="date" name="valid_until" value="{{ old('valid_until', $quotation->valid_until->format('Y-m-d')) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Tax (%)</label><input type="number" step="0.01" name="tax" value="{{ old('tax', $quotation->tax) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Discount</label><input type="number" step="0.01" name="discount" value="{{ old('discount', $quotation->discount) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Shipping Cost</label><input type="number" step="0.01" name="shipping" value="{{ old('shipping', $quotation->shipping) }}" class="form-control"></div>
        </div>
        <div class="border-t border-gray-200 pt-4 mb-4">
            <h4 class="font-semibold text-gray-800 mb-3">Products</h4>
            <div class="overflow-x-auto"><table class="table"><thead><tr><th>Product</th><th>Quantity</th><th>Unit Price</th><th>Subtotal</th></tr></thead>
            <tbody>@foreach($quotation->items ?? [] as $index => $item)<tr><td><select name="products[{{ $index }}][product_id]" class="form-control" required><option value="">Select Product</option>@foreach($products ?? [] as $p)<option value="{{ $p->id }}" {{ $item->product_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach</select></td><td><input type="number" name="products[{{ $index }}][quantity]" value="{{ $item->quantity }}" class="form-control" min="1" required></td><td><input type="number" step="0.01" name="products[{{ $index }}][price]" value="{{ $item->price }}" class="form-control" required></td><td class="font-semibold">{{ number_format($item->quantity * $item->price, 2) }} DH</td></tr>@endforeach</tbody></table></div>
        </div>
        <div class="form-group"><label class="form-label">Terms & Conditions</label><textarea name="terms" rows="4" class="form-control">{{ old('terms', $quotation->terms) }}</textarea></div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('quotations.index') }}" class="btn btn-outline">Cancel</a><button type="submit" class="btn btn-primary">Update Quotation</button></div>
    </form>
</div>
@endsection
