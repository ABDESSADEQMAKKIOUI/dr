@extends('layouts.app')
@section('title', __('app.add_sale'))
@php
$pageTitle = __('app.add_sale');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.sales'), 'url' => route('sales.index')], ['label' => __('app.create'), 'url' => '']];
@endphp
@section('content')
<div class="card max-w-6xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.add_sale') }}</h3></div>
    <form method="POST" action="{{ route('sales.store') }}" data-validate>
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="form-group"><label class="form-label">{{ __('app.customer') }}</label><select name="customer_id" class="form-control"><option value="">Walk-in Customer</option>@foreach($customers ?? [] as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">{{ __('app.warehouse') }} *</label><select name="warehouse_id" class="form-control" required><option value="">{{ __('app.select_warehouse') }}</option>@foreach($warehouses ?? [] as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">{{ __('app.date') }} *</label><input type="date" name="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">{{ __('app.tax') }} (%)</label><input type="number" step="0.01" name="tax" value="{{ old('tax', 20) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">{{ __('app.discount') }}</label><input type="number" step="0.01" name="discount" value="{{ old('discount', 0) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">{{ __('app.shipping') }}</label><input type="number" step="0.01" name="shipping" value="{{ old('shipping', 0) }}" class="form-control"></div>
        </div>
        <div class="border-t border-gray-200 pt-4 mb-4">
            <h4 class="font-semibold text-gray-800 mb-3">{{ __('app.products') }}</h4>
            <div class="overflow-x-auto"><table class="table" id="products-table"><thead><tr><th>{{ __('app.product') }}</th><th>{{ __('app.stock') }}</th><th>{{ __('app.quantity') }}</th><th>{{ __('app.unit_price') }}</th><th>{{ __('app.subtotal') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
            <tbody id="products-tbody"><tr><td><select name="products[0][product_id]" class="form-control product-select" required><option value="">{{ __('app.select_product') }}</option>@foreach($products ?? [] as $p)<option value="{{ $p->id }}" data-price="{{ $p->sale_price }}" data-stock="{{ $p->stock_quantity }}">{{ $p->name }}</option>@endforeach</select></td><td class="stock-display text-gray-600">-</td><td><input type="number" name="products[0][quantity]" class="form-control quantity-input" value="1" min="1" required></td><td><input type="number" step="0.01" name="products[0][price]" class="form-control price-input" required></td><td class="subtotal font-semibold">0.00 DH</td><td></td></tr></tbody></table></div>
            <button type="button" onclick="addProductRow()" class="btn btn-outline btn-sm mt-2">+ {{ __('app.add_item') }}</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6"><div class="space-y-4"><div class="form-group"><label class="form-label">{{ __('app.status') }}</label><select name="status" class="form-control"><option value="draft">{{ __('app.draft') }}</option><option value="confirmed" selected>{{ __('app.completed') }}</option><option value="delivered">{{ __('app.delivered') }}</option></select></div><div class="form-group"><label class="form-label">{{ __('app.notes') }}</label><textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea></div></div>
        <div class="bg-gray-50 p-4 rounded-lg"><dl class="space-y-2"><div class="flex justify-between text-sm"><dt>{{ __('app.subtotal') }}:</dt><dd id="order-subtotal" class="font-semibold">0.00 DH</dd></div><div class="flex justify-between text-sm"><dt>{{ __('app.tax') }}:</dt><dd id="order-tax" class="font-semibold">0.00 DH</dd></div><div class="flex justify-between text-sm"><dt>{{ __('app.shipping') }}:</dt><dd id="order-shipping" class="font-semibold">0.00 DH</dd></div><div class="flex justify-between text-sm"><dt>{{ __('app.discount') }}:</dt><dd id="order-discount" class="font-semibold">0.00 DH</dd></div><div class="flex justify-between text-lg border-t border-gray-300 pt-2"><dt class="font-semibold">{{ __('app.total') }}:</dt><dd id="order-total" class="font-bold text-blue-600">0.00 DH</dd></div></dl></div></div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('sales.index') }}" class="btn btn-outline">{{ __('app.cancel') }}</a><button type="submit" class="btn btn-primary">{{ __('app.add_sale') }}</button></div>
    </form>
</div>
@push('scripts')
<script>
let rowCount = 1;
function addProductRow() {
    const tbody = document.getElementById('products-tbody');
    const row = `<tr><td><select name="products[${rowCount}][product_id]" class="form-control product-select" required><option value="">{{ __('app.select_product') }}</option>@foreach($products ?? [] as $p)<option value="{{ $p->id }}" data-price="{{ $p->sale_price }}" data-stock="{{ $p->stock_quantity }}">{{ $p->name }}</option>@endforeach</select></td><td class="stock-display text-gray-600">-</td><td><input type="number" name="products[${rowCount}][quantity]" class="form-control quantity-input" value="1" min="1" required></td><td><input type="number" step="0.01" name="products[${rowCount}][price]" class="form-control price-input" required></td><td class="subtotal font-semibold">0.00 DH</td><td><button type="button" onclick="this.closest('tr').remove();calculateTotal()" class="btn btn-danger btn-sm">×</button></td></tr>`;
    tbody.insertAdjacentHTML('beforeend', row);
    rowCount++;
}
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('products-table');
    table.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-select')) {
            const row = e.target.closest('tr');
            const option = e.target.options[e.target.selectedIndex];
            const price = option.dataset.price;
            const stock = option.dataset.stock;
            row.querySelector('.price-input').value = price;
            row.querySelector('.stock-display').textContent = stock + ' available';
            row.querySelector('.quantity-input').max = stock;
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
