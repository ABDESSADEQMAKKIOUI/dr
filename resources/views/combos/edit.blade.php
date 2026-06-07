@extends('layouts.app')
@section('title', __('app.edit_combo'))
@php $pageTitle = __('app.edit_combo'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.combo_products'),'url'=>route('combos.index')],['label'=>__('app.edit')]]; @endphp
@section('content')
<div class="card max-w-4xl mx-auto">
    <div class="card-header"><h3 class="text-lg font-semibold">{{ __('app.edit_combo') }}: {{ $combo->name }}</h3></div>
    <form method="POST" action="{{ route('combos.update', $combo) }}" class="p-6 space-y-6" id="combo-form">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">{{ __('app.name') }} <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $combo->name) }}" class="form-input" required>
                @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">{{ __('app.sku') }}</label>
                <input type="text" name="sku" value="{{ old('sku', $combo->sku) }}" class="form-input" readonly>
            </div>
            <div>
                <label class="form-label">{{ __('app.price') }} <span class="text-red-500">*</span></label>
                <input type="number" name="price" value="{{ old('price', $combo->price) }}" class="form-input" step="0.01" min="0" required>
                @error('price') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">{{ __('app.status') }}</label>
                <label class="flex items-center gap-2 mt-2">
                    <input type="checkbox" name="is_active" value="1" class="form-checkbox" {{ old('is_active', $combo->is_active) ? 'checked' : '' }}>
                    <span>{{ __('app.active') }}</span>
                </label>
            </div>
        </div>
        <div>
            <label class="form-label">{{ __('app.description') }}</label>
            <textarea name="description" rows="2" class="form-input">{{ old('description', $combo->description) }}</textarea>
        </div>

        {{-- Component Products --}}
        <div>
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-md font-semibold text-gray-700">{{ __('app.component_products') }}</h4>
                <button type="button" onclick="addItem()" class="btn btn-secondary btn-sm">+ {{ __('app.add_item') }}</button>
            </div>
            <div id="items-container">
                @foreach($combo->items as $idx => $item)
                <div class="item-row flex gap-3 items-end mb-2" data-index="{{ $idx }}">
                    <div class="flex-1">
                        <label class="form-label text-sm">{{ __('app.product') }}</label>
                        <select name="items[{{ $idx }}][product_id]" class="form-select" required>
                            <option value="">-- {{ __('app.select_product') }} --</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}" @selected($item->product_id == $p->id)>{{ $p->name }} ({{ $p->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-32">
                        <label class="form-label text-sm">{{ __('app.quantity') }}</label>
                        <input type="number" name="items[{{ $idx }}][quantity]" value="{{ $item->quantity }}" class="form-input" min="1" required>
                    </div>
                    <button type="button" onclick="removeItem(this)" class="text-red-500 hover:text-red-700 pb-2" title="Remove">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
                @endforeach
            </div>
            @error('items') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3 pt-4 border-t">
            <button type="submit" class="btn btn-primary">{{ __('app.update') }}</button>
            <a href="{{ route('combos.index') }}" class="btn btn-secondary">{{ __('app.cancel') }}</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
let itemIndex = {{ $combo->items->count() }};
const productsOptions = `<option value="">-- {{ __('app.select_product') }} --</option>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>@endforeach`;

function addItem() {
    const container = document.getElementById('items-container');
    const row = document.createElement('div');
    row.className = 'item-row flex gap-3 items-end mb-2';
    row.innerHTML = `
        <div class="flex-1">
            <label class="form-label text-sm">{{ __('app.product') }}</label>
            <select name="items[${itemIndex}][product_id]" class="form-select" required>${productsOptions}</select>
        </div>
        <div class="w-32">
            <label class="form-label text-sm">{{ __('app.quantity') }}</label>
            <input type="number" name="items[${itemIndex}][quantity]" value="1" class="form-input" min="1" required>
        </div>
        <button type="button" onclick="removeItem(this)" class="text-red-500 hover:text-red-700 pb-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
    `;
    container.appendChild(row);
    itemIndex++;
}

function removeItem(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length <= 1) { alert('At least one product is required.'); return; }
    btn.closest('.item-row').remove();
}
</script>
@endpush
@endsection
