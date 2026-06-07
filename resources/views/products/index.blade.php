@extends('layouts.app')

@section('title', __('app.products'))

@php
$pageTitle = __('app.products');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.products'), 'url' => route('products.index')]
];
@endphp

@section('content')

@push('styles')
<style>
@media print {
    /* Hide all page chrome except the product table */
    body * {
        visibility: hidden !important;
    }

    .table-wrapper,
    .table-wrapper * {
        visibility: visible !important;
    }

    .table-wrapper {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
    }

    .no-print,
    .filter-bar,
    .pagination,
    .stat-mini,
    .card-header .btn,
    .card-header .btn-outline,
    .card-header .btn-primary,
    .action-btn,
    .product-checkbox,
    #select-all,
    #products-table th:first-child,
    #products-table td:first-child,
    #products-table th:last-child,
    #products-table td:last-child {
        display: none !important;
    }

    #products-table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    #products-table th,
    #products-table td {
        border: 1px solid #ddd !important;
        padding: 0.6rem !important;
    }
}
</style>
@endpush

{{-- ── Stats row ─────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Total --}}
    <div class="stat-mini">
        <div class="stat-mini-icon bg-indigo-100">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value">{{ $products->total() ?? 0 }}</div>
            <div class="stat-mini-label">{{ __('app.total_products') ?? 'Total Products' }}</div>
        </div>
    </div>
    {{-- In Stock --}}
    <div class="stat-mini">
        <div class="stat-mini-icon bg-emerald-100">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-emerald-600">{{ $inStockCount ?? 0 }}</div>
            <div class="stat-mini-label">{{ __('app.in_stock') }}</div>
        </div>
    </div>
    {{-- Low Stock --}}
    <div class="stat-mini">
        <div class="stat-mini-icon bg-amber-100">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-amber-600">{{ $lowStockCount ?? 0 }}</div>
            <div class="stat-mini-label">{{ __('app.low_stock') }}</div>
        </div>
    </div>
    {{-- Out of Stock --}}
    <div class="stat-mini">
        <div class="stat-mini-icon bg-rose-100">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-rose-600">{{ $outOfStockCount ?? 0 }}</div>
            <div class="stat-mini-label">{{ __('app.out_of_stock') }}</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6 no-print">
    <div class="stat-mini">
        <div class="stat-mini-icon bg-slate-100">
            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value">{{ number_format($inventoryPurchaseValue ?? 0, 2) }} DH</div>
            <div class="stat-mini-label">{{ __('app.total_purchase_value') ?? 'Total Purchase Value' }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-slate-100">
            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value">{{ number_format($inventorySaleValue ?? 0, 2) }} DH</div>
            <div class="stat-mini-label">{{ __('app.total_sale_value') ?? 'Total Sale Value' }}</div>
        </div>
    </div>
</div>

{{-- ── Main card ─────────────────────────────────────────────────── --}}
<div class="card">

    {{-- Card header --}}
    <div class="card-header">
        <div>
            <h3 class="card-title">{{ __('app.products') }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('app.manage_your_products') ?? 'Manage your product catalogue' }}</p>
        </div>
        <div class="flex items-center gap-2 no-print">
            {{-- Print list --}}
            <button type="button" onclick="window.print()"
                    class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7H5a2 2 0 00-2 2v8a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2zM5 7V5a2 2 0 012-2h10a2 2 0 012 2v2"/>
                </svg>
                {{ __('app.print_list') ?? 'Print List' }}
            </button>
            {{-- Print barcodes (hidden until selection) --}}
            <button id="print-barcodes-btn"
                    onclick="printSelectedBarcodes()"
                    class="btn btn-outline btn-sm hidden">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                {{ __('app.print_barcodes') }} <span class="bg-white/20 text-xs rounded-full px-1.5 py-0.5 font-bold" id="selected-count">0</span>
            </button>
            <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('app.add_product') }}
            </a>
        </div>
    </div>

    {{-- ── Filter bar ──────────────────────────────────────────────── --}}
    <form id="product-filter-form" method="GET" action="{{ route('products.index') }}" class="filter-bar">
        {{-- Search --}}
        <div class="filter-group flex-1 min-w-48">
            <label class="filter-label">{{ __('app.search') }}</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                </svg>
                <input type="text" id="search" name="search"
                       value="{{ request('search') }}"
                       placeholder="{{ __('app.search_products') ?? 'Name, SKU, barcode…' }}"
                       class="form-control pl-9 py-2">
            </div>
        </div>
        {{-- Category --}}
        <div class="filter-group w-44">
            <label class="filter-label">{{ __('app.category') }}</label>
            <select id="filter-category" name="category_id" class="form-control py-2" onchange="this.form.submit()">
                <option value="">{{ __('app.all_categories') }}</option>
                @foreach($categories ?? [] as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        {{-- Brand --}}
        <div class="filter-group w-36">
            <label class="filter-label">{{ __('app.brand') }}</label>
            <select id="filter-brand" name="brand_id" class="form-control py-2" onchange="this.form.submit()">
                <option value="">{{ __('app.all_brands') }}</option>
                @foreach($brands ?? [] as $brand)
                <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                @endforeach
            </select>
        </div>
        {{-- Status --}}
        <div class="filter-group w-36">
            <label class="filter-label">{{ __('app.status') }}</label>
            <select id="filter-status" name="status" class="form-control py-2" onchange="this.form.submit()">
                <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>{{ __('app.all') }}</option>
                <option value="in_stock" {{ request('status') === 'in_stock' ? 'selected' : '' }}>{{ __('app.in_stock') }}</option>
                <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>{{ __('app.low_stock') }}</option>
                <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>{{ __('app.out_of_stock') }}</option>
            </select>
        </div>
        {{-- Reset --}}
        <div class="filter-group">
            <label class="filter-label opacity-0">.</label>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-ghost btn-sm text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    {{ __('app.filter') ?? 'Filter' }}
                </button>
                <a href="{{ route('products.index') }}" class="btn btn-ghost btn-sm text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    {{ __('app.reset') ?? 'Reset' }}
                </a>
            </div>
        </div>
    </form>

    {{-- ── Table ────────────────────────────────────────────────────── --}}
    <div class="table-wrapper">
        <table class="table" id="products-table">
            <thead>
                <tr>
                    <th class="w-10">
                        <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)">
                    </th>
                    <th class="w-16">{{ __('app.image') }}</th>
                    <th>{{ __('app.name') }}</th>
                    <th>{{ __('app.sku') }}</th>
                    <th>{{ __('app.category') }}</th>
                    <th>{{ __('app.cost') ?? 'Cost' }}</th>
                    <th>{{ __('app.sale_price') }}</th>
                    <th class="w-24">{{ __('app.stock') }}</th>
                    <th class="w-28">{{ __('app.status') }}</th>
                    <th class="w-24 text-center">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody id="products-tbody">
                @forelse($products ?? [] as $product)
                <tr data-name="{{ strtolower($product->name) }} {{ strtolower($product->sku) }} {{ strtolower($product->barcode ?? '') }}"
                    data-category="{{ $product->category_id }}"
                    data-brand="{{ $product->brand_id }}"
                    data-status="{{ $product->stock_quantity == 0 ? 'out_of_stock' : ($product->stock_quantity <= $product->stock_alert ? 'low_stock' : 'in_stock') }}">

                    {{-- Checkbox --}}
                    <td>
                        <input type="checkbox" class="product-checkbox" value="{{ $product->id }}"
                               onchange="updateSelection()">
                    </td>

                    {{-- Image --}}
                    <td>
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}"
                                 alt="{{ $product->name }}"
                                 class="w-11 h-11 rounded-xl object-cover ring-1 ring-slate-200">
                        @else
                            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center">
                                <span class="text-white text-xs font-bold">{{ strtoupper(substr($product->name, 0, 2)) }}</span>
                            </div>
                        @endif
                    </td>

                    {{-- Name --}}
                    <td>
                        <div class="font-semibold text-slate-800">{{ $product->name }}</div>
                        @if($product->barcode)
                            <div class="mono text-slate-400 mt-0.5">{{ $product->barcode }}</div>
                        @endif
                    </td>

                    {{-- SKU --}}
                    <td><span class="mono bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md">{{ $product->sku }}</span></td>

                    {{-- Category --}}
                    <td class="text-slate-600">{{ $product->category->name ?? '—' }}</td>

                    {{-- Cost price --}}
                    <td class="text-slate-500">{{ number_format($product->cost_price, 2) }} DH</td>

                    {{-- Sale price --}}
                    <td class="font-semibold text-slate-800">{{ number_format($product->sale_price, 2) }} DH</td>

                    {{-- Stock --}}
                    <td>
                        <span class="font-bold {{ $product->stock_quantity == 0 ? 'text-rose-600' : ($product->stock_quantity <= $product->stock_alert ? 'text-amber-600' : 'text-emerald-600') }}">
                            {{ $product->stock_quantity }}
                        </span>
                        <span class="text-slate-400 text-xs ml-1">{{ $product->unit->name ?? '' }}</span>
                    </td>

                    {{-- Status badge --}}
                    <td>
                        @if($product->stock_quantity == 0)
                            <span class="badge badge-danger">{{ __('app.out_of_stock') }}</span>
                        @elseif($product->stock_quantity <= $product->stock_alert)
                            <span class="badge badge-warning">{{ __('app.low_stock') }}</span>
                        @else
                            <span class="badge badge-success">{{ __('app.in_stock') }}</span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('products.show', $product->id) }}"
                               class="action-btn action-btn-view" title="{{ __('app.view') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('products.edit', $product->id) }}"
                               class="action-btn action-btn-edit" title="{{ __('app.edit') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('products.destroy', $product->id) }}"
                                  class="inline" onsubmit="return confirmDelete(this)">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn action-btn-delete" title="{{ __('app.delete') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <p class="empty-state-title">{{ __('app.no_products_found') }}</p>
                            <p class="empty-state-desc">{{ __('app.add_your_first_product') ?? 'Add your first product to get started.' }}</p>
                            <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm mt-4">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.add_product') }}
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if(isset($products) && $products->hasPages())
    <div class="pagination">
        {{ $products->links() }}
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
/* ── Selection ─────────────────────────────────────────────────── */
function toggleSelectAll(el) {
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = el.checked);
    updateSelection();
}

function updateSelection() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    const btn     = document.getElementById('print-barcodes-btn');
    document.getElementById('selected-count').textContent = checked.length;
    btn.classList.toggle('hidden', checked.length === 0);

    // Highlight selected rows
    document.querySelectorAll('.product-checkbox').forEach(cb => {
        cb.closest('tr').classList.toggle('selected', cb.checked);
    });
}

function printSelectedBarcodes() {
    const ids = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => cb.value);
    if (!ids.length) return;
    window.open(`{{ route('barcodes.print') }}?${ids.map(id => `ids[]=${id}`).join('&')}&size=a4`, '_blank');
}

/* ── Delete confirm ────────────────────────────────────────────── */
function confirmDelete(form) {
    return confirm('{{ __("app.confirm_delete") ?? "Are you sure you want to delete this product?" }}');
}
</script>
@endpush
