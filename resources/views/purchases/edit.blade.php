@extends('layouts.app')
@section('title', 'Edit Purchase')
@php
$pageTitle = 'Edit Purchase';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Purchases', 'url' => route('purchases.index')], ['label' => 'Edit', 'url' => '']];
@endphp
@section('content')
<div class="card max-w-6xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">Edit Purchase: {{ $purchase->reference }}</h3></div>
    <form method="POST" action="{{ route('purchases.update', $purchase->id) }}" data-validate>
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="form-group"><label class="form-label">Supplier *</label><select name="supplier_id" class="form-control" required><option value="">Select Supplier</option>@foreach($suppliers ?? [] as $s)<option value="{{ $s->id }}" {{ $purchase->supplier_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Warehouse *</label><select name="warehouse_id" class="form-control" required><option value="">Select Warehouse</option>@foreach($warehouses ?? [] as $w)<option value="{{ $w->id }}" {{ $purchase->warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Purchase Date *</label><input type="date" name="purchase_date" value="{{ old('purchase_date', optional($purchase->date)->format('Y-m-d')) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Tax (%)</label><input type="number" step="0.01" name="tax" value="{{ old('tax', 0) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Discount</label><input type="number" step="0.01" name="discount" value="{{ old('discount', $purchase->discount_amount) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Shipping Cost</label><input type="number" step="0.01" name="shipping" value="{{ old('shipping', $purchase->shipping_cost) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Paid Amount</label><input type="number" step="0.01" name="paid_amount" value="{{ old('paid_amount', $purchase->paid_amount) }}" class="form-control"></div>
        </div>
        <div class="border-t border-gray-200 pt-4 mb-4">
            <h4 class="font-semibold text-gray-800 mb-3">Products</h4>
            <div class="overflow-x-auto"><table class="table" id="products-table"><thead><tr><th>Product</th><th>Quantity</th><th>Unit Price</th><th>Subtotal</th><th>Actions</th></tr></thead>
            <tbody id="products-tbody">@foreach($purchase->items ?? [] as $index => $item)<tr><td><select name="products[{{ $index }}][product_id]" class="form-control product-select" required><option value="">Select Product</option>@foreach($products ?? [] as $p)<option value="{{ $p->id }}" {{ $item->product_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach</select></td><td><input type="number" name="products[{{ $index }}][quantity]" value="{{ $item->quantity }}" class="form-control quantity-input" min="1" required></td><td><input type="number" step="0.01" name="products[{{ $index }}][price]" value="{{ $item->price }}" class="form-control price-input" required></td><td class="subtotal font-semibold">{{ number_format($item->quantity * $item->price, 2) }} DH</td><td><button type="button" onclick="this.closest('tr').remove();calculateTotal()" class="btn btn-danger btn-sm">×</button></td></tr>@endforeach</tbody></table></div>
            <button type="button" onclick="addProductRow()" class="btn btn-outline btn-sm mt-2">+ Add Item</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="form-group"><label class="form-label">Notes</label><textarea name="notes" rows="3" class="form-control">{{ old('notes', $purchase->notes) }}</textarea></div>
            <div class="bg-gray-50 p-4 rounded-lg"><dl class="space-y-2"><div class="flex justify-between text-sm"><dt>Subtotal:</dt><dd id="order-subtotal" class="font-semibold">0.00 DH</dd></div><div class="flex justify-between text-sm"><dt>Tax:</dt><dd id="order-tax" class="font-semibold">0.00 DH</dd></div><div class="flex justify-between text-sm"><dt>Shipping:</dt><dd id="order-shipping" class="font-semibold">0.00 DH</dd></div><div class="flex justify-between text-sm"><dt>Discount:</dt><dd id="order-discount" class="font-semibold">0.00 DH</dd></div><div class="flex justify-between text-lg border-t border-gray-300 pt-2"><dt class="font-semibold">Total:</dt><dd id="order-total" class="font-bold text-blue-600">0.00 DH</dd></div></dl></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('purchases.index') }}" class="btn btn-outline">Cancel</a><button type="submit" class="btn btn-primary">Update Purchase</button></div>
    </form>
</div>
@push('scripts')
<script>
let rowCount = {{ count($purchase->items ?? []) }};
function addProductRow() {
    const tbody = document.getElementById('products-tbody');
    const row = `<tr><td><select name="products[${rowCount}][product_id]" class="form-control product-select" required><option value="">Select Product</option>@foreach($products ?? [] as $p)<option value="{{ $p->id }}" data-price="{{ $p->purchase_price }}">{{ $p->name }}</option>@endforeach</select></td><td><input type="number" name="products[${rowCount}][quantity]" class="form-control quantity-input" value="1" min="1" required></td><td><input type="number" step="0.01" name="products[${rowCount}][price]" class="form-control price-input" required></td><td class="subtotal font-semibold">0.00 DH</td><td><button type="button" onclick="this.closest('tr').remove();calculateTotal()" class="btn btn-danger btn-sm">×</button></td></tr>`;
    tbody.insertAdjacentHTML('beforeend', row);
    rowCount++;
}
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
    
    const table = document.getElementById('products-table');
    table.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-select')) {
            const row = e.target.closest('tr');
            const price = e.target.options[e.target.selectedIndex].dataset.price;
            row.querySelector('.price-input').value = price;
            updateRowSubtotal(row);
        }
    });
    table.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input') || e.target.classList.contains('price-input')) {
            updateRowSubtotal(e.target.closest('tr'));
        }
    });
    document.querySelectorAll('input[name="tax"], input[name="discount"], input[name="shipping"]').forEach(input => {
        input.addEventListener('input', calculateTotal);
    });
});
function updateRowSubtotal(row) {
    const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const subtotal = qty * price;
    row.querySelector('.subtotal').textContent = subtotal.toFixed(2) + ' DH';
    calculateTotal();
}
function calculateTotal() {
    let subtotal = 0;
    document.querySelectorAll('#products-tbody tr').forEach(row => {
        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        subtotal += qty * price;
    });
    const taxRate = parseFloat(document.querySelector('input[name="tax"]').value) || 0;
    const discount = parseFloat(document.querySelector('input[name="discount"]').value) || 0;
    const shipping = parseFloat(document.querySelector('input[name="shipping"]').value) || 0;
    const tax = subtotal * (taxRate / 100);
    const total = subtotal + tax + shipping - discount;
    document.getElementById('order-subtotal').textContent = subtotal.toFixed(2) + ' DH';
    document.getElementById('order-tax').textContent = tax.toFixed(2) + ' DH';
    document.getElementById('order-shipping').textContent = shipping.toFixed(2) + ' DH';
    document.getElementById('order-discount').textContent = discount.toFixed(2) + ' DH';
    document.getElementById('order-total').textContent = total.toFixed(2) + ' DH';
}
</script>
@endpush
@endsection
