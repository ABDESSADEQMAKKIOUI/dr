@extends('layouts.app')
@section('title', __('app.create_purchase_return'))
@php
$pageTitle = __('app.create_purchase_return');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.purchases'), 'url' => route('purchases.index')], ['label' => __('app.returns'), 'url' => route('purchases.returns.index')], ['label' => __('app.create'), 'url' => '']];
@endphp
@section('content')
<div class="card max-w-5xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.new_purchase_return') }}</h3></div>
    <form method="POST" action="{{ route('purchases.returns.store') }}" data-validate>
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="form-group">
                <label class="form-label">{{ __('app.purchase') }} *</label>
                <select name="purchase_id" id="purchase-select" class="form-control" required>
                    <option value="">{{ __('app.select_purchase') }}</option>
                    @foreach($purchases ?? [] as $p)
                        <option value="{{ $p->id }}" data-items='@json($p->items)'>
                            {{ $p->reference }} - {{ $p->supplier->name }} ({{ optional($p->date)->format('M d, Y') ?? $p->created_at->format('M d, Y') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label class="form-label">{{ __('app.return_date') }} *</label><input type="date" name="return_date" value="{{ old('return_date', date('Y-m-d')) }}" class="form-control" required></div>
            <div class="form-group md:col-span-2"><label class="form-label">{{ __('app.reason') }} *</label><textarea name="reason" rows="2" class="form-control" required>{{ old('reason') }}</textarea></div>
        </div>
        <div class="border-t border-gray-200 pt-4 mb-4">
            <h4 class="font-semibold text-gray-800 mb-3">{{ __('app.products_to_return') }}</h4>
            <div id="products-container"><p class="text-gray-500 text-sm">{{ __('app.select_purchase_first') }}</p></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('purchases.returns.index') }}" class="btn btn-outline">{{ __('app.cancel') }}</a><button type="submit" id="submit-btn" class="btn btn-primary" disabled>{{ __('app.create_return') }}</button></div>
    </form>
</div>
@push('scripts')
<script>
document.getElementById('purchase-select').addEventListener('change', function() {
    const container = document.getElementById('products-container');
    const submitBtn = document.getElementById('submit-btn');
    const selectedOption = this.options[this.selectedIndex];
    
    if (!this.value) {
        container.innerHTML = '<p class="text-gray-500 text-sm">{{ __('app.select_purchase_first') }}</p>';
        submitBtn.disabled = true;
        return;
    }
    
    const items = JSON.parse(selectedOption.dataset.items || '[]');
    
    if (items.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-sm">{{ __('app.no_items_for_return') }}</p>';
        submitBtn.disabled = true;
        return;
    }
    
    let html = '<div class="overflow-x-auto"><table class="table"><thead><tr><th>{{ __('app.product') }}</th><th>{{ __('app.purchased_qty') }}</th><th>{{ __('app.return_qty') }}</th><th>{{ __('app.unit_price') }}</th><th>{{ __('app.subtotal') }}</th></tr></thead><tbody>';
    
    items.forEach((item, index) => {
        html += `<tr>
            <td class="font-medium">${item.product.name}<input type="hidden" name="items[${index}][product_id]" value="${item.product_id}"></td>
            <td>${item.quantity}</td>
            <td><input type="number" name="items[${index}][quantity]" class="form-control return-qty" data-price="${item.price}" min="0" max="${item.quantity}" value="0"></td>
            <td>${parseFloat(item.price).toFixed(2)} DH</td>
            <td class="subtotal font-semibold">0.00 DH</td>
        </tr>`;
    });
    
    html += '</tbody></table></div><div class="mt-4 bg-gray-50 p-4 rounded-lg"><div class="flex justify-between text-lg"><span class="font-semibold">{{ __('app.return_total') }}:</span><span id="return-total" class="font-bold text-red-600">0.00 DH</span></div></div>';
    
    container.innerHTML = html;
    submitBtn.disabled = false;
    
    container.addEventListener('input', function(e) {
        if (e.target.classList.contains('return-qty')) {
            const row = e.target.closest('tr');
            const qty = parseFloat(e.target.value) || 0;
            const price = parseFloat(e.target.dataset.price) || 0;
            const subtotal = qty * price;
            row.querySelector('.subtotal').textContent = subtotal.toFixed(2) + ' DH';
            
            let total = 0;
            document.querySelectorAll('.return-qty').forEach(input => {
                const q = parseFloat(input.value) || 0;
                const p = parseFloat(input.dataset.price) || 0;
                total += q * p;
            });
            document.getElementById('return-total').textContent = total.toFixed(2) + ' DH';
        }
    });
});
</script>
@endpush
@endsection
