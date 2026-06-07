@extends('layouts.app')

@section('title', $product->name)

@php
$pageTitle = $product->name;
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.products'),  'url' => route('products.index')],
    ['label' => $product->name,      'url' => ''],
];

$margin     = $product->cost_price > 0
    ? round(($product->sale_price - $product->cost_price) / $product->sale_price * 100, 1)
    : 0;
$stockStatus = $product->stock_quantity == 0
    ? ['label' => __('app.out_of_stock'), 'class' => 'badge-danger']
    : ($product->stock_quantity <= $product->stock_alert
        ? ['label' => __('app.low_stock'),   'class' => 'badge-warning']
        : ['label' => __('app.in_stock'),     'class' => 'badge-success']);
@endphp

@section('content')

{{-- ── Top action bar ──────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('products.index') }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back_to_products') ?? 'Back to Products' }}
    </a>
    <div class="flex items-center gap-2">
        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            {{ __('app.edit') }}
        </a>
        <form method="POST" action="{{ route('products.destroy', $product->id) }}"
              onsubmit="return confirm('{{ __('app.confirm_delete') ?? 'Delete this product?' }}')">
            @csrf @method('DELETE')
            <button class="btn btn-danger btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ __('app.delete') }}
            </button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

{{-- ══════════════════════════════════════════════════════════════
     LEFT COLUMN — image + identity
══════════════════════════════════════════════════════════════════ --}}
<div class="space-y-5">

    {{-- Product image card --}}
    <div class="card overflow-hidden">
        {{-- Image --}}
        <div class="relative bg-gradient-to-br from-slate-100 to-slate-200" style="min-height:280px">
            @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}"
                     alt="{{ $product->name }}"
                     class="w-full h-72 object-contain p-4"
                     onerror="this.onerror=null;this.closest('.relative').innerHTML='<div class=\'w-full h-72 flex items-center justify-center\'><span class=\'text-6xl font-black text-slate-300\'>{{ strtoupper(substr($product->name,0,2)) }}</span></div>'">
            @else
                <div class="w-full h-72 flex items-center justify-center">
                    <div class="text-center">
                        <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center mx-auto mb-3">
                            <span class="text-white text-3xl font-black">{{ strtoupper(substr($product->name, 0, 2)) }}</span>
                        </div>
                        <p class="text-xs text-slate-400">{{ __('app.no_image') ?? 'No image' }}</p>
                    </div>
                </div>
            @endif

            {{-- Status badge overlay --}}
            <div class="absolute top-3 right-3">
                <span class="badge {{ $stockStatus['class'] }}">{{ $stockStatus['label'] }}</span>
            </div>

            {{-- Featured badge --}}
            @if($product->is_featured ?? false)
            <div class="absolute top-3 left-3">
                <span class="badge badge-primary">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    {{ __('app.featured') ?? 'Featured' }}
                </span>
            </div>
            @endif
        </div>

        {{-- Name + identifiers --}}
        <div class="p-5 border-t border-slate-100">
            <h2 class="text-xl font-bold text-slate-900 leading-tight mb-1">{{ $product->name }}</h2>
            @if($product->slug)
            <p class="text-xs text-slate-400 mb-3">{{ $product->slug }}</p>
            @endif

            <div class="flex flex-wrap gap-2 mt-3">
                <div class="flex items-center gap-1.5 bg-slate-100 rounded-lg px-3 py-1.5">
                    <span class="text-xs text-slate-500">SKU</span>
                    <span class="mono text-xs font-semibold text-slate-700">{{ $product->sku }}</span>
                </div>
                @if($product->barcode)
                <div class="flex items-center gap-1.5 bg-slate-100 rounded-lg px-3 py-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span class="mono text-xs font-semibold text-slate-700">{{ $product->barcode }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Classification --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('app.classification') ?? 'Classification' }}</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @php
            $meta = [
                [__('app.category'), $product->category->name ?? '—', 'text-indigo-600', 'bg-indigo-50'],
                [__('app.brand'),    $product->brand->name    ?? '—', 'text-purple-600', 'bg-purple-50'],
                [__('app.unit'),     $product->unit->name     ?? '—', 'text-sky-600',    'bg-sky-50'],
                [__('app.type'),     ucfirst($product->type ?? 'standard'), 'text-slate-600', 'bg-slate-100'],
            ];
            @endphp
            @foreach($meta as [$label, $value, $textColor, $bgColor])
            <div class="flex items-center justify-between px-5 py-3">
                <span class="text-sm text-slate-500">{{ $label }}</span>
                <span class="text-sm font-semibold {{ $textColor }} {{ $bgColor }} px-2.5 py-0.5 rounded-lg">{{ $value }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Flags (track stock / serial / warranty / expiry) --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('app.settings') ?? 'Settings' }}</h3>
        </div>
        <div class="card-body grid grid-cols-2 gap-3">
            @php
            $flags = [
                [__('app.track_stock')       ?? 'Track Stock',    $product->track_stock       ?? false],
                [__('app.serial_numbers')    ?? 'Serial Numbers', $product->has_serial_numbers ?? false],
                [__('app.warranty')          ?? 'Warranty',       $product->has_warranty       ?? false],
                [__('app.expiry_tracking')   ?? 'Expiry',         $product->has_expiry         ?? false],
                [__('app.active')            ?? 'Active',         $product->is_active          ?? true],
                [__('app.featured')          ?? 'Featured',       $product->is_featured        ?? false],
            ];
            @endphp
            @foreach($flags as [$label, $on])
            <div class="flex items-center gap-2 p-2.5 rounded-xl {{ $on ? 'bg-emerald-50' : 'bg-slate-50' }}">
                <div class="w-2 h-2 rounded-full {{ $on ? 'bg-emerald-500' : 'bg-slate-300' }}"></div>
                <span class="text-xs font-medium {{ $on ? 'text-emerald-700' : 'text-slate-500' }}">{{ $label }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     RIGHT COLUMNS (2/3)
══════════════════════════════════════════════════════════════════ --}}
<div class="xl:col-span-2 space-y-6">

    {{-- ── KPI stat row ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        {{-- Cost --}}
        <div class="card p-5 text-center">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">{{ __('app.cost_price') }}</p>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($product->cost_price, 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">DH</p>
        </div>
        {{-- Sale --}}
        <div class="card p-5 text-center border-indigo-200" style="border-color: #c7d2fe">
            <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wide mb-2">{{ __('app.sale_price') }}</p>
            <p class="text-2xl font-bold text-indigo-700">{{ number_format($product->sale_price, 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">DH</p>
        </div>
        {{-- Margin --}}
        <div class="card p-5 text-center border-{{ $margin >= 20 ? 'emerald' : 'amber' }}-200">
            <p class="text-xs font-semibold text-{{ $margin >= 20 ? 'emerald' : 'amber' }}-500 uppercase tracking-wide mb-2">{{ __('app.margin') ?? 'Margin' }}</p>
            <p class="text-2xl font-bold text-{{ $margin >= 20 ? 'emerald' : 'amber' }}-600">{{ $margin }}%</p>
            <p class="text-xs text-slate-400 mt-1">+{{ number_format($product->sale_price - $product->cost_price, 2) }} DH</p>
        </div>
        {{-- Stock --}}
        <div class="card p-5 text-center">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">{{ __('app.stock') }}</p>
            <p class="text-2xl font-bold {{ $product->stock_quantity == 0 ? 'text-rose-600' : ($product->stock_quantity <= $product->stock_alert ? 'text-amber-600' : 'text-emerald-600') }}">
                {{ $product->stock_quantity ?? 0 }}
            </p>
            <p class="text-xs text-slate-400 mt-1">{{ $product->unit->name ?? 'units' }}</p>
        </div>
    </div>

    {{-- ── Pricing details ───────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="card-title">{{ __('app.pricing') ?? 'Pricing & Tax' }}</h3>
            </div>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs text-slate-500 mb-1">{{ __('app.cost_price') }}</p>
                    <p class="text-lg font-bold text-slate-800">{{ number_format($product->cost_price, 2) }} <span class="text-sm font-normal text-slate-400">DH</span></p>
                </div>
                <div class="bg-indigo-50 rounded-xl p-4">
                    <p class="text-xs text-indigo-500 mb-1">{{ __('app.sale_price') }}</p>
                    <p class="text-lg font-bold text-indigo-800">{{ number_format($product->sale_price, 2) }} <span class="text-sm font-normal text-indigo-400">DH</span></p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs text-slate-500 mb-1">{{ __('app.tax') }} ({{ $product->tax_type ?? 'exclusive' }})</p>
                    <p class="text-lg font-bold text-slate-800">{{ $product->tax_rate ?? 0 }}<span class="text-sm font-normal text-slate-400">%</span></p>
                </div>
            </div>

            @if($product->cost_price > 0 && $product->sale_price > 0)
            <div class="mt-4 p-3 rounded-xl bg-gradient-to-r from-{{ $margin >= 20 ? 'emerald' : 'amber' }}-50 to-transparent border border-{{ $margin >= 20 ? 'emerald' : 'amber' }}-200 flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-{{ $margin >= 20 ? 'emerald' : 'amber' }}-500 flex-shrink-0"></div>
                <div class="text-sm">
                    <span class="text-slate-600">{{ __('app.profit_per_unit') ?? 'Profit per unit' }}: </span>
                    <strong class="text-{{ $margin >= 20 ? 'emerald' : 'amber' }}-700">+{{ number_format($product->sale_price - $product->cost_price, 2) }} DH</strong>
                    <span class="text-slate-400 ml-2">({{ $margin }}% margin)</span>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Stock & warehouses ────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                    </svg>
                </div>
                <h3 class="card-title">{{ __('app.stock_information') ?? 'Stock Information' }}</h3>
            </div>
            @if($product->has_serial_numbers ?? false)
            <a href="{{ route('products.serials', $product) }}" class="btn btn-outline btn-sm">
                {{ __('app.view_serials') ?? 'View Serials' }}
            </a>
            @endif
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div class="text-center p-4 bg-slate-50 rounded-xl">
                    <p class="text-xs text-slate-500 mb-1">{{ __('app.current_stock') ?? 'Current Stock' }}</p>
                    <p class="text-3xl font-black {{ $product->stock_quantity == 0 ? 'text-rose-600' : ($product->stock_quantity <= $product->stock_alert ? 'text-amber-600' : 'text-emerald-600') }}">
                        {{ $product->stock_quantity ?? 0 }}
                    </p>
                    <span class="badge {{ $stockStatus['class'] }} mt-1">{{ $stockStatus['label'] }}</span>
                </div>
                <div class="text-center p-4 bg-slate-50 rounded-xl">
                    <p class="text-xs text-slate-500 mb-1">{{ __('app.stock_alert_level') ?? 'Alert Level' }}</p>
                    <p class="text-3xl font-black text-amber-500">{{ $product->stock_alert ?? 0 }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ __('app.minimum_threshold') ?? 'Minimum threshold' }}</p>
                </div>
                <div class="text-center p-4 bg-slate-50 rounded-xl">
                    <p class="text-xs text-slate-500 mb-1">{{ __('app.total_sold') ?? 'Total Sold' }}</p>
                    <p class="text-3xl font-black text-indigo-600">{{ $totalSold ?? 0 }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ __('app.units') ?? 'units' }}</p>
                </div>
            </div>

            {{-- Per-warehouse stock --}}
            @if($product->warehouses && $product->warehouses->count() > 0)
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">{{ __('app.stock_by_warehouse') ?? 'Stock by Warehouse' }}</p>
                <div class="space-y-2">
                    @foreach($product->warehouses as $pw)
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-slate-700">{{ $pw->warehouse->name ?? 'Warehouse' }}</span>
                        </div>
                        <span class="text-sm font-bold text-slate-800">{{ $pw->quantity }} <span class="text-slate-400 font-normal text-xs">{{ $product->unit->name ?? '' }}</span></span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Expiry --}}
            @if($product->has_expiry && $product->expiry_date)
            <div class="mt-4 flex items-center gap-3 p-3 bg-rose-50 border border-rose-200 rounded-xl text-sm">
                <svg class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-rose-700">{{ __('app.expires_on') ?? 'Expires on' }}: <strong>{{ $product->expiry_date->format('d M Y') }}</strong></span>
            </div>
            @endif

            {{-- Warranty --}}
            @if($product->has_warranty && $product->warranty_duration)
            <div class="mt-3 flex items-center gap-3 p-3 bg-sky-50 border border-sky-200 rounded-xl text-sm">
                <svg class="w-4 h-4 text-sky-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span class="text-sky-700">{{ __('app.warranty') }}: <strong>{{ $product->warranty_duration }} {{ $product->warranty_type ?? 'months' }}</strong></span>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Description ───────────────────────────────────────────── --}}
    @if($product->description)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('app.description') }}</h3>
        </div>
        <div class="card-body">
            <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">{{ $product->description }}</p>
        </div>
    </div>
    @endif

    {{-- ── Recent sales ──────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <h3 class="card-title">{{ __('app.recent_sales') ?? 'Recent Sales' }}</h3>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400">{{ __('app.total_revenue') ?? 'Total revenue' }}: <strong class="text-indigo-600">{{ number_format($totalRevenue ?? 0, 2) }} DH</strong></span>
            </div>
        </div>
        @if(isset($recentSales) && $recentSales->count() > 0)
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ __('app.customer') }}</th>
                        <th>{{ __('app.sale_ref') ?? 'Sale Ref' }}</th>
                        <th class="text-right">{{ __('app.qty') }}</th>
                        <th class="text-right">{{ __('app.unit_price') }}</th>
                        <th class="text-right">{{ __('app.subtotal') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentSales as $item)
                    <tr>
                        <td class="text-slate-500 text-xs">{{ $item->created_at->format('d M Y') }}</td>
                        <td class="font-medium text-slate-700">{{ $item->sale->customer->name ?? '—' }}</td>
                        <td>
                            <a href="{{ route('sales.show', $item->sale_id) }}"
                               class="mono text-xs text-indigo-600 hover:underline">
                               {{ $item->sale->reference ?? '#'.$item->sale_id }}
                            </a>
                        </td>
                        <td class="text-right font-semibold">{{ $item->quantity }}</td>
                        <td class="text-right text-slate-600">{{ number_format($item->price ?? 0, 2) }} DH</td>
                        <td class="text-right font-bold text-emerald-600">{{ number_format($item->subtotal ?? 0, 2) }} DH</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state py-10">
            <div class="empty-state-icon">
                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <p class="empty-state-title">{{ __('app.no_sales_yet') ?? 'No sales yet' }}</p>
        </div>
        @endif
    </div>

    {{-- ── Recent purchases ──────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg bg-sky-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="card-title">{{ __('app.recent_purchases') ?? 'Recent Purchases' }}</h3>
            </div>
            <span class="text-xs text-slate-400">{{ __('app.total_received') ?? 'Total received' }}: <strong class="text-sky-600">{{ $totalPurchased ?? 0 }} {{ $product->unit->name ?? 'units' }}</strong></span>
        </div>
        @if(isset($recentPurchases) && $recentPurchases->count() > 0)
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ __('app.supplier') }}</th>
                        <th>{{ __('app.purchase_ref') ?? 'Purchase Ref' }}</th>
                        <th class="text-right">{{ __('app.qty') }}</th>
                        <th class="text-right">{{ __('app.unit_cost') ?? 'Unit Cost' }}</th>
                        <th class="text-right">{{ __('app.subtotal') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPurchases as $item)
                    <tr>
                        <td class="text-slate-500 text-xs">{{ $item->created_at->format('d M Y') }}</td>
                        <td class="font-medium text-slate-700">{{ $item->purchase->supplier->name ?? '—' }}</td>
                        <td>
                            <a href="{{ route('purchases.show', $item->purchase_id) }}"
                               class="mono text-xs text-sky-600 hover:underline">
                               {{ $item->purchase->reference ?? '#'.$item->purchase_id }}
                            </a>
                        </td>
                        <td class="text-right font-semibold">{{ $item->quantity }}</td>
                        <td class="text-right text-slate-600">{{ number_format($item->unit_cost ?? $item->price ?? 0, 2) }} DH</td>
                        <td class="text-right font-bold text-sky-600">{{ number_format($item->subtotal ?? 0, 2) }} DH</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state py-10">
            <div class="empty-state-icon">
                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4"/>
                </svg>
            </div>
            <p class="empty-state-title">{{ __('app.no_purchases_yet') ?? 'No purchases yet' }}</p>
        </div>
        @endif
    </div>

    {{-- ── Variants (if any) ─────────────────────────────────────── --}}
    @if($product->variants && $product->variants->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('app.variants') ?? 'Variants' }}</h3>
            <span class="badge badge-secondary">{{ $product->variants->count() }}</span>
        </div>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('app.variant') ?? 'Variant' }}</th>
                        <th>{{ __('app.sku') }}</th>
                        <th class="text-right">{{ __('app.price') }}</th>
                        <th class="text-right">{{ __('app.stock') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($product->variants as $variant)
                    <tr>
                        <td class="font-medium">{{ $variant->name ?? $variant->attribute_value }}</td>
                        <td><span class="mono bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md text-xs">{{ $variant->sku }}</span></td>
                        <td class="text-right font-semibold">{{ number_format($variant->sale_price ?? $product->sale_price, 2) }} DH</td>
                        <td class="text-right"><span class="font-bold {{ ($variant->stock_quantity ?? 0) > 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $variant->stock_quantity ?? 0 }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ── Meta (created by / dates) ─────────────────────────────── --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('app.record_info') ?? 'Record Info' }}</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-xs text-slate-400 mb-1">{{ __('app.created_at') }}</p>
                    <p class="font-medium text-slate-700">{{ $product->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-1">{{ __('app.updated_at') }}</p>
                    <p class="font-medium text-slate-700">{{ $product->updated_at->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-1">{{ __('app.created_by') }}</p>
                    <p class="font-medium text-slate-700">{{ $product->creator->name ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>

</div>{{-- end right col --}}
</div>{{-- end grid --}}

@endsection
