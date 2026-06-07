@extends('layouts.app')
@section('title', 'Create Quotation')
@php
$pageTitle = 'Create Quotation';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Quotations', 'url' => route('quotations.index')], ['label' => 'Create', 'url' => '']];
@endphp
@section('content')
<div class="card max-w-6xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">New Quotation</h3></div>
    <form method="POST" action="{{ route('quotations.store') }}" data-validate>
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="form-group"><label class="form-label">Customer *</label><select name="customer_id" class="form-control" required><option value="">Select Customer</option>@foreach($customers ?? [] as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>@error('customer_id')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">Warehouse *</label><select name="warehouse_id" class="form-control" required><option value="">Select Warehouse</option>@foreach($warehouses ?? [] as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Quotation Date *</label><input type="date" name="quotation_date" value="{{ old('quotation_date', date('Y-m-d')) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Valid Until *</label><input type="date" name="valid_until" value="{{ old('valid_until', date('Y-m-d', strtotime('+30 days'))) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Tax (%)</label><input type="number" step="0.01" name="tax" value="{{ old('tax', 0) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Discount</label><input type="number" step="0.01" name="discount" value="{{ old('discount', 0) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Shipping Cost</label><input type="number" step="0.01" name="shipping" value="{{ old('shipping', 0) }}" class="form-control"></div>
        </div>
        <div class="border-t border-gray-200 pt-4 mb-4">
            <h4 class="font-semibold text-gray-800 mb-3">Products</h4>
            <div class="overflow-x-auto"><table class="table" id="products-table"><thead><tr><th>Product</th><th>Quantity</th><th>Unit Price</th><th>Subtotal</th><th>Action</th></tr></thead>
            <tbody id="products-tbody"><tr><td><select name="products[0][product_id]" class="form-control product-select" required><option value="">Select Product</option>@foreach($products ?? [] as $p)<option value="{{ $p->id }}" data-price="{{ $p->sale_price }}" data-stock="{{ $p->stock_quantity }}">{{ $p->name }}</option>@endforeach</select></td><td><input type="number" name="products[0][quantity]" class="form-control quantity-input" value="1" min="1" required><div class="text-xs text-slate-400 stock-hint mt-0.5"></div><div class="text-xs text-rose-500 stock-error mt-0.5 hidden"></div></td><td><input type="number" step="0.01" name="products[0][price]" class="form-control price-input" required></td><td class="subtotal font-semibold">0.00 DH</td><td></td></tr></tbody></table></div>
            <button type="button" onclick="addProductRow()" class="btn btn-outline btn-sm mt-2">+ Add Product</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6"><div class="form-group"><label class="form-label">Terms & Conditions</label><textarea name="terms" rows="4" class="form-control">{{ old('terms') }}</textarea></div>
        <div class="bg-gray-50 p-4 rounded-lg"><dl class="space-y-2"><div class="flex justify-between text-sm"><dt>Subtotal:</dt><dd id="order-subtotal" class="font-semibold">0.00 DH</dd></div><div class="flex justify-between text-sm"><dt>Tax:</dt><dd id="order-tax" class="font-semibold">0.00 DH</dd></div><div class="flex justify-between text-sm"><dt>Shipping:</dt><dd id="order-shipping" class="font-semibold">0.00 DH</dd></div><div class="flex justify-between text-sm"><dt>Discount:</dt><dd id="order-discount" class="font-semibold">0.00 DH</dd></div><div class="flex justify-between text-lg border-t border-gray-300 pt-2"><dt class="font-semibold">Total:</dt><dd id="order-total" class="font-bold text-blue-600">0.00 DH</dd></div></dl></div></div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('quotations.index') }}" class="btn btn-outline">Cancel</a><button type="submit" name="status" value="draft" class="btn btn-outline">Save as Draft</button><button type="submit" name="status" value="sent" class="btn btn-primary">Send to Customer</button></div>
    </form>
</div>
@push('scripts')
<script>
let rowCount = 1;
function addProductRow() {
    const tbody = document.getElementById('products-tbody');
    const row = `<tr><td><select name="products[${rowCount}][product_id]" class="form-control product-select" required><option value="">Select Product</option>@foreach($products ?? [] as $p)<option value="{{ $p->id }}" data-price="{{ $p->sale_price }}" data-stock="{{ $p->stock_quantity }}">{{ $p->name }}</option>@endforeach</select></td><td><input type="number" name="products[${rowCount}][quantity]" class="form-control quantity-input" value="1" min="1" required><div class="text-xs text-slate-400 stock-hint mt-0.5"></div><div class="text-xs text-rose-500 stock-error mt-0.5 hidden"></div></td><td><input type="number" step="0.01" name="products[${rowCount}][price]" class="form-control price-input" required></td><td class="subtotal font-semibold">0.00 DH</td><td><button type="button" onclick="this.closest('tr').remove();calculateTotal()" class="btn btn-danger btn-sm">×</button></td></tr>`;
    tbody.insertAdjacentHTML('beforeend', row);
    rowCount++;
}
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('products-table');
    table.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-select')) {
            const row = e.target.closest('tr');
            const option = e.target.options[e.target.selectedIndex];
            row.querySelector('.price-input').value = option.dataset.price || '';
            const stock = parseInt(option.dataset.stock) || 0;
            row.querySelector('.quantity-input').dataset.stock = stock;
            row.querySelector('.quantity-input').max = stock;
            row.querySelector('.stock-hint').textContent = stock > 0 ? `{{ __('app.in_stock') }}: ${stock}` : `{{ __('app.out_of_stock') }}`;
            row.querySelector('.stock-error').classList.add('hidden');
            updateRowSubtotal(row);
        }
    });
    table.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input')) {
            validateStock(e.target);
            updateRowSubtotal(e.target.closest('tr'));
        }
        if (e.target.classList.contains('price-input')) {
            updateRowSubtotal(e.target.closest('tr'));
        }
    });
    document.querySelectorAll('input[name="tax"], input[name="discount"], input[name="shipping"]').forEach(input => {
        input.addEventListener('input', calculateTotal);
    });
    document.querySelector('form').addEventListener('submit', function(e) {
        let hasError = false;
        document.querySelectorAll('.quantity-input').forEach(input => {
            if (validateStock(input)) hasError = true;
        });
        if (hasError) e.preventDefault();
    });
});
function validateStock(input) {
    const stock = parseInt(input.dataset.stock);
    const qty = parseInt(input.value) || 0;
    const errorEl = input.closest('td').querySelector('.stock-error');
    if (!errorEl) return false;
    if (!isNaN(stock) && qty > stock) {
        errorEl.textContent = `{{ __('app.insufficient_stock') }} ({{ __('app.available') }}: ${stock})`;
        errorEl.classList.remove('hidden');
        input.classList.add('border-rose-400');
        return true;
    }
    errorEl.classList.add('hidden');
    input.classList.remove('border-rose-400');
    return false;
}
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
