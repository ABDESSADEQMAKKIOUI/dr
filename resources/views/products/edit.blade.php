@extends('layouts.app')
@section('title', __('app.edit_product'))
@php
$pageTitle = __('app.edit_product');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.products'), 'url' => route('products.index')], ['label' => __('app.edit'), 'url' => '']];
@endphp
@section('content')
<div class="card max-w-4xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.edit_product') }}: {{ $product->name }}</h3></div>
    <form method="POST" action="{{ route('products.update', $product->id) }}" enctype="multipart/form-data" data-validate>
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group"><label class="form-label">{{ __('app.name') }} *</label><input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control" required>@error('name')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">{{ __('app.sku') }} *</label><input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="form-control" required>@error('sku')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group md:col-span-2">
                <label class="form-label">Barcode</label>
                <div class="flex gap-2">
                    <input type="text" name="barcode" id="barcode-field" value="{{ old('barcode', $product->barcode) }}" class="form-control" placeholder="Scan or enter barcode">
                    <button type="button" class="btn btn-outline btn-sm whitespace-nowrap" onclick="generateBarcode()">Auto-Generate</button>
                </div>
                @error('barcode')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group"><label class="form-label">{{ __('app.category') }} *</label><select name="category_id" class="form-control" required><option value="">{{ __('app.select_category') }}</option>@foreach($categories ?? [] as $cat)<option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">{{ __('app.brand') }}</label><select name="brand_id" class="form-control"><option value="">{{ __('app.select_brand') }}</option>@foreach($brands ?? [] as $brand)<option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">{{ __('app.unit') }} *</label><select name="unit_id" class="form-control" required><option value="">{{ __('app.select_unit') }}</option>@foreach($units ?? [] as $unit)<option value="{{ $unit->id }}" {{ $product->unit_id == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">{{ __('app.cost_price') }} *</label><input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">{{ __('app.sale_price') }} *</label><input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">{{ __('app.tax') }} (%)</label><input type="number" step="0.01" name="tax_rate" value="{{ old('tax_rate', $product->tax_rate ?? 20) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">{{ __('app.stock_alert') }}</label><input type="number" name="stock_alert" value="{{ old('stock_alert', $product->stock_alert) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">{{ __('app.stock_quantity') }}</label><input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" class="form-control"></div>
            <div class="form-group md:col-span-2">
                <label class="form-label">{{ __('app.current_image') }}</label>
                @if($product->image)
                <div class="mb-2"><img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-32 h-32 rounded object-cover"></div>
                @endif
                <label class="form-label">{{ __('app.change_image') }}</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <div class="form-group md:col-span-2"><label class="form-label">{{ __('app.description') }}</label><textarea name="description" rows="3" class="form-control">{{ old('description', $product->description) }}</textarea></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('products.index') }}" class="btn btn-outline">{{ __('app.cancel') }}</a><button type="submit" class="btn btn-primary">{{ __('app.save') }}</button></div>
    </form>
</div>
@push('scripts')
<script>
async function generateBarcode() {
    try {
        const res  = await fetch('{{ route("barcodes.generate") }}');
        const data = await res.json();
        document.getElementById('barcode-field').value = data.barcode;
    } catch (e) { alert('Could not generate barcode'); }
}
</script>
@endpush
@endsection
