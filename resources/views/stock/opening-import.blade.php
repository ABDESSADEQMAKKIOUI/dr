@extends('layouts.app')
@section('title', __('app.opening_stock_import'))
@php $pageTitle = __('app.opening_stock_import'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.stock')],['label'=>__('app.opening_stock_import')]]; @endphp
@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    {{-- Info Card --}}
    <div class="card">
        <div class="card-header"><h3 class="text-lg font-semibold">{{ __('app.opening_stock_import') }}</h3></div>
        <div class="p-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h4 class="text-blue-800 font-medium mb-2">CSV Format</h4>
                <p class="text-blue-700 text-sm mb-2">Your CSV file must have these columns:</p>
                <code class="text-sm bg-blue-100 px-2 py-1 rounded">product_sku, warehouse_id, quantity, cost_price</code>
                <div class="mt-3">
                    <a href="{{ route('stock.opening-import.sample') }}" class="btn btn-secondary btn-sm">
                        ⬇ {{ __('app.download_sample') }}
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('stock.opening-import.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">{{ __('app.select_file') }}</label>
                    <input type="file" name="file" accept=".csv,.xlsx,.xls" class="form-input" required>
                    @error('file') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-800">
                    <strong>Note:</strong> Each row creates a stock adjustment record. Products are matched by SKU.
                    Rows with invalid SKU or warehouse ID will be skipped.
                </div>

                <button type="submit" class="btn btn-primary">{{ __('app.import') }}</button>
            </form>
        </div>
    </div>

    {{-- Available Warehouses Reference --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold">{{ __('app.warehouses') }} (ID Reference)</h3></div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>ID</th><th>{{ __('app.name') }}</th></tr></thead>
                <tbody>
                    @foreach($warehouses as $wh)
                    <tr><td>{{ $wh->id }}</td><td>{{ $wh->name }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
