@extends('layouts.app')
@section('title', 'Serial Numbers — ' . $product->name)
@php
$pageTitle = 'Serial Numbers: ' . $product->name;
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard')],
    ['label' => 'Products', 'url' => route('products.index')],
    ['label' => $product->name, 'url' => route('products.show', $product)],
    ['label' => 'Serial Numbers', 'url' => ''],
];
@endphp
@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="card text-center py-4">
        <p class="text-2xl font-bold text-gray-800">{{ $serials->total() }}</p>
        <p class="text-xs text-gray-500 mt-1">Total Serials</p>
    </div>
    <div class="card text-center py-4">
        <p class="text-2xl font-bold text-green-600">{{ $available }}</p>
        <p class="text-xs text-gray-500 mt-1">Available</p>
    </div>
    <div class="card text-center py-4">
        <p class="text-2xl font-bold text-blue-600">{{ $sold }}</p>
        <p class="text-xs text-gray-500 mt-1">Sold</p>
    </div>
    <div class="card text-center py-4">
        <p class="text-2xl font-bold text-orange-500">{{ $returned }}</p>
        <p class="text-xs text-gray-500 mt-1">Returned</p>
    </div>
</div>

<div class="card">
    <div class="card-header flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">Serial Numbers</h3>
        <a href="{{ route('products.show', $product) }}" class="btn btn-outline btn-sm">← Back to Product</a>
    </div>

    {{-- Filter --}}
    <form method="GET" class="p-4 border-b flex gap-3 items-end flex-wrap">
        <div>
            <label class="form-label text-xs">Status</label>
            <select name="status" class="form-control text-sm">
                <option value="">All</option>
                <option value="available"  {{ request('status') === 'available'  ? 'selected' : '' }}>Available</option>
                <option value="sold"       {{ request('status') === 'sold'       ? 'selected' : '' }}>Sold</option>
                <option value="returned"   {{ request('status') === 'returned'   ? 'selected' : '' }}>Returned</option>
            </select>
        </div>
        <div>
            <label class="form-label text-xs">Search Serial</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Serial number…" class="form-control text-sm">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        @if(request()->hasAny(['status','q']))
            <a href="{{ route('products.serials', $product) }}" class="btn btn-outline btn-sm">Clear</a>
        @endif
    </form>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Serial Number</th>
                    <th>Warehouse</th>
                    <th>Status</th>
                    <th>Sale / Purchase</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($serials as $serial)
                <tr>
                    <td class="text-gray-400 text-xs">{{ $serial->id }}</td>
                    <td class="font-mono font-semibold">{{ $serial->serial_number }}</td>
                    <td>{{ $serial->warehouse->name ?? '—' }}</td>
                    <td>
                        @php
                            $colors = ['available' => 'green', 'sold' => 'blue', 'returned' => 'orange'];
                            $c = $colors[$serial->status] ?? 'gray';
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-{{ $c }}-100 text-{{ $c }}-700 capitalize">
                            {{ $serial->status }}
                        </span>
                    </td>
                    <td class="text-xs text-gray-600">
                        @if($serial->sale_item_id)
                            Sale #{{ $serial->saleItem->sale_id ?? $serial->sale_item_id }}
                        @elseif($serial->purchase_item_id)
                            Purchase #{{ $serial->purchaseItem->purchase_id ?? $serial->purchase_item_id }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-xs text-gray-500">{{ $serial->created_at->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-gray-500 py-8">No serial numbers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $serials->withQueryString()->links() }}</div>
</div>
@endsection
