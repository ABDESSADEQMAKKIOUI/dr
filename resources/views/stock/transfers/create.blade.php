@extends('layouts.app')
@section('title', __('app.stock_transfers'))
@php
$pageTitle = __('app.stock_transfers');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.stock'), 'url' => '#'], ['label' => __('app.transfers'), 'url' => route('stock.transfers.index')], ['label' => __('app.create'), 'url' => '']];
@endphp
@section('content')
<div class="card max-w-4xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.new') }} {{ __('app.transfer') }}</h3></div>
    <form method="POST" action="{{ route('stock.transfers.store') }}" data-validate>
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="form-group"><label class="form-label">{{ __('app.from') }} {{ __('app.warehouse') }} *</label><select name="from_warehouse_id" class="form-control" required><option value="">{{ __('app.select_warehouse') }}</option>@foreach($warehouses ?? [] as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">{{ __('app.to') }} {{ __('app.warehouse') }} *</label><select name="to_warehouse_id" class="form-control" required><option value="">{{ __('app.select_warehouse') }}</option>@foreach($warehouses ?? [] as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">{{ __('app.date') }} *</label><input type="date" name="transfer_date" value="{{ old('transfer_date', date('Y-m-d')) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">{{ __('app.status') }}</label><select name="status" class="form-control"><option value="pending">{{ __('app.pending') }}</option><option value="in_transit">{{ __('app.in_transit') }}</option><option value="completed">{{ __('app.completed') }}</option></select></div>
            <div class="form-group md:col-span-2"><label class="form-label">{{ __('app.notes') }}</label><textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea></div>
        </div>
        <div class="border-t border-gray-200 pt-4">
            <h4 class="font-semibold text-gray-800 mb-3">{{ __('app.products') }}</h4>
            <div id="products-container"><div class="grid grid-cols-12 gap-2 mb-2"><div class="col-span-7"><select name="products[0][product_id]" class="form-control" required><option value="">{{ __('app.select_product') }}</option>@foreach($products ?? [] as $p)<option value="{{ $p->id }}">{{ $p->name }} ({{ __('app.stock') }}: {{ $p->stock }})</option>@endforeach</select></div><div class="col-span-5"><input type="number" name="products[0][quantity]" placeholder="{{ __('app.quantity') }}" class="form-control" required></div></div></div>
            <button type="button" onclick="addProductRow()" class="btn btn-outline btn-sm mt-2">+ {{ __('app.add_item') }}</button>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('stock.transfers.index') }}" class="btn btn-outline">{{ __('app.cancel') }}</a><button type="submit" class="btn btn-primary">{{ __('app.submit') }}</button></div>
    </form>
</div>
@push('scripts')
<script>
let rowCount = 1;
function addProductRow() {
    const container = document.getElementById('products-container');
    const row = `<div class="grid grid-cols-12 gap-2 mb-2"><div class="col-span-7"><select name="products[${rowCount}][product_id]" class="form-control" required><option value="">{{ __('app.select_product') }}</option>@foreach($products ?? [] as $p)<option value="{{ $p->id }}">{{ $p->name }} ({{ __('app.stock') }}: {{ $p->stock }})</option>@endforeach</select></div><div class="col-span-4"><input type="number" name="products[${rowCount}][quantity]" placeholder="{{ __('app.quantity') }}" class="form-control" required></div><div class="col-span-1"><button type="button" onclick="this.parentElement.parentElement.remove()" class="btn btn-danger btn-sm w-full">×</button></div></div>`;
    container.insertAdjacentHTML('beforeend', row);
    rowCount++;
}
</script>
@endpush
@endsection
