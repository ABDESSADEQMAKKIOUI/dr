@extends('layouts.app')
@section('title', __('app.inventory'))
@php
$pageTitle = __('app.inventory');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.stock'), 'url' => '#'], ['label' => __('app.inventory'), 'url' => '']];
@endphp
@section('content')
<div class="card">
    <div class="card-header flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">{{ __('app.inventory') }}</h3>
        <a href="{{ route('stock.inventory.count') }}" class="btn btn-primary btn-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            {{ __('app.stock_count') }}
        </a>
    </div>
    
    <!-- Stats Cards -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg">
            <p class="text-blue-600 text-sm mb-1">{{ __('app.total') }} {{ __('app.products') }}</p>
            <h4 class="text-2xl font-bold text-blue-700">{{ $stats['total_products'] ?? 0 }}</h4>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
            <p class="text-green-600 text-sm mb-1">{{ __('app.total') }} {{ __('app.value') }}</p>
            <h4 class="text-2xl font-bold text-green-700">{{ number_format($stats['total_value'] ?? 0, 2) }} DH</h4>
        </div>
        <div class="bg-slate-50 p-4 rounded-lg">
            <p class="text-slate-600 text-sm mb-1">{{ __('app.total_purchase_value') ?? 'Total Purchase Value' }}</p>
            <h4 class="text-2xl font-bold text-slate-700">{{ number_format($stats['purchase_total'] ?? 0, 2) }} DH</h4>
        </div>
        <div class="bg-slate-50 p-4 rounded-lg">
            <p class="text-slate-600 text-sm mb-1">{{ __('app.total_sale_value') ?? 'Total Sale Value' }}</p>
            <h4 class="text-2xl font-bold text-slate-700">{{ number_format($stats['sale_total'] ?? 0, 2) }} DH</h4>
        </div>
        <div class="bg-orange-50 p-4 rounded-lg">
            <p class="text-orange-600 text-sm mb-1">{{ __('app.low_stock') }}</p>
            <h4 class="text-2xl font-bold text-orange-700">{{ $stats['low_stock'] ?? 0 }}</h4>
        </div>
        <div class="bg-red-50 p-4 rounded-lg">
            <p class="text-red-600 text-sm mb-1">{{ __('app.out_of_stock') }}</p>
            <h4 class="text-2xl font-bold text-red-700">{{ $stats['out_of_stock'] ?? 0 }}</h4>
        </div>
    </div>
    
    <!-- Filters -->
    <form method="GET" action="{{ route('stock.inventory.index') }}" id="filterForm" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <select name="warehouse" class="form-control" onchange="document.getElementById('filterForm').submit()">
                <option value="">{{ __('app.all_warehouses') }}</option>
                @foreach($warehouses ?? [] as $w)
                    <option value="{{ $w->id }}" {{ request('warehouse') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="category" class="form-control" onchange="document.getElementById('filterForm').submit()">
                <option value="">{{ __('app.all_categories') }}</option>
                @foreach($categories ?? [] as $c)
                    <option value="{{ $c->id }}" {{ request('category') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('app.search_products') }}..." class="form-control">
        </div>
        <div>
            <button type="submit" class="btn btn-primary w-full">{{ __('app.filter') }}</button>
        </div>
    </form>
    
    <!-- Products Table -->
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('app.product') }}</th>
                    <th>{{ __('app.sku') }}</th>
                    <th>{{ __('app.category') }}</th>
                    <th>{{ __('app.stock') }}</th>
                    <th>{{ __('app.stock_alert') }}</th>
                    <th>{{ __('app.price') }}</th>
                    <th>{{ __('app.total') }}</th>
                    <th>{{ __('app.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products ?? [] as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="font-medium">{{ $product->name }}</td>
                    <td>{{ $product->sku ?? '-' }}</td>
                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                    <td class="font-semibold {{ $product->stock_quantity <= $product->stock_alert ? 'text-red-600' : 'text-green-600' }}">
                        {{ $product->stock_quantity ?? 0 }}
                    </td>
                    <td>{{ $product->stock_alert ?? 0 }}</td>
                    <td>{{ number_format($product->sale_price ?? 0, 2) }} DH</td>
                    <td>{{ number_format(($product->stock_quantity ?? 0) * ($product->sale_price ?? 0), 2) }} DH</td>
                    <td>
                        @if(($product->stock_quantity ?? 0) <= 0)
                            <span class="badge badge-danger">{{ __('app.out_of_stock') }}</span>
                        @elseif(($product->stock_quantity ?? 0) <= ($product->stock_alert ?? 0))
                            <span class="badge badge-warning">{{ __('app.low_stock') }}</span>
                        @else
                            <span class="badge badge-success">{{ __('app.in_stock') }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-gray-500 py-8">{{ __('app.no_results') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if(isset($products) && $products->hasPages())
    <div class="mt-4">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection
