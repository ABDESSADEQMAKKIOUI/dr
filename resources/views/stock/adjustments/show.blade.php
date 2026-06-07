@extends('layouts.app')
@section('title', __('app.adjustment_details'))

@php
$pageTitle = __('app.adjustment_details');
$breadcrumbs = [
    ['label' => __('app.dashboard'),   'url' => route('dashboard')],
    ['label' => __('app.stock'),        'url' => '#'],
    ['label' => __('app.adjustments'), 'url' => route('stock.adjustments.index')],
    ['label' => $adjustment->reference,'url' => ''],
];

$typeConfig = match($adjustment->type) {
    'addition'    => ['badge' => 'badge-success', 'sign' => '+', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-100', 'icon_color' => 'text-emerald-600'],
    'subtraction' => ['badge' => 'badge-warning', 'sign' => '-', 'color' => 'text-amber-600',   'bg' => 'bg-amber-100',   'icon_color' => 'text-amber-600'],
    'damage'      => ['badge' => 'badge-danger',  'sign' => '-', 'color' => 'text-rose-600',    'bg' => 'bg-rose-100',    'icon_color' => 'text-rose-600'],
    default       => ['badge' => 'badge-secondary','sign' => '-', 'color' => 'text-slate-600',   'bg' => 'bg-slate-100',   'icon_color' => 'text-slate-500'],
};
@endphp

@section('content')

{{-- ── Top action bar ──────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('stock.adjustments.index') }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back') }}
    </a>
    <div class="flex items-center gap-2">
        <a href="{{ route('stock.adjustments.edit', $adjustment) }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            {{ __('app.edit') }}
        </a>
        <form action="{{ route('stock.adjustments.destroy', $adjustment) }}" method="POST"
              onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ __('app.delete') }}
            </button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── LEFT: info card ─────────────────────────────────────────── --}}
    <div class="space-y-5">

        {{-- Info card --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg {{ $typeConfig['bg'] }} flex items-center justify-center">
                        <svg class="w-4 h-4 {{ $typeConfig['icon_color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.adjustment_info') }}</h3>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                {{-- Reference --}}
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.reference') }}</span>
                    <span class="mono text-sm font-semibold text-slate-700 bg-slate-100 px-2.5 py-0.5 rounded-lg">
                        {{ $adjustment->reference }}
                    </span>
                </div>
                {{-- Warehouse --}}
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.warehouse') }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $adjustment->warehouse->name ?? '—' }}</span>
                </div>
                {{-- Type --}}
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.type') }}</span>
                    <span class="badge {{ $typeConfig['badge'] }}">{{ __('app.' . $adjustment->type) }}</span>
                </div>
                {{-- Date --}}
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.date') }}</span>
                    <span class="text-sm font-semibold text-slate-700">
                        {{ ($adjustment->date ? \Carbon\Carbon::parse($adjustment->date) : $adjustment->created_at)->format('d M Y') }}
                    </span>
                </div>
                {{-- Created by --}}
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.created_by') }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $adjustment->user->full_name ?? '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Reason card --}}
        @if($adjustment->reason)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('app.reason') }}</h3>
            </div>
            <div class="card-body">
                <p class="text-sm text-slate-600 leading-relaxed">{{ $adjustment->reason }}</p>
            </div>
        </div>
        @endif

    </div>

    {{-- ── RIGHT: adjusted products ────────────────────────────────── --}}
    <div class="xl:col-span-2">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.products_adjusted') }}</h3>
                </div>
                @php
                    $items = $adjustment->notes ? array_filter(explode('; ', $adjustment->notes)) : [];
                @endphp
                @if(count($items))
                <span class="badge badge-secondary">{{ count($items) }} {{ __('app.items') }}</span>
                @endif
            </div>

            @if(count($items))
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-10">#</th>
                            <th>{{ __('app.product') }}</th>
                            <th class="w-32 text-right">{{ __('app.quantity') }}</th>
                            <th>{{ __('app.notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $index => $item)
                            @php
                                preg_match('/Product ID: (\d+), Qty: (\d+)(.*)/i', $item, $matches);
                                $productId = $matches[1] ?? null;
                                $quantity  = $matches[2] ?? 0;
                                $note      = isset($matches[3]) ? trim($matches[3], ' -') : '';
                                $product   = $productId ? \App\Models\Product::find($productId) : null;
                            @endphp
                            <tr>
                                <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        @if($product?->image)
                                            <img src="{{ asset('storage/' . $product->image) }}"
                                                 class="w-9 h-9 rounded-lg object-cover ring-1 ring-slate-200"
                                                 alt="{{ $product->name }}">
                                        @else
                                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center flex-shrink-0">
                                                <span class="text-white text-xs font-bold">
                                                    {{ strtoupper(substr($product->name ?? 'P', 0, 2)) }}
                                                </span>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-semibold text-slate-800">
                                                {{ $product->name ?? __('app.product') . ' #' . $productId }}
                                            </div>
                                            @if($product?->sku)
                                            <div class="mono text-xs text-slate-400">{{ $product->sku }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <span class="text-base font-bold {{ $typeConfig['color'] }}">
                                        {{ $typeConfig['sign'] }}{{ $quantity }}
                                    </span>
                                </td>
                                <td class="text-sm text-slate-500">{{ $note ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="card-body">
                <div class="empty-state py-10">
                    <div class="empty-state-icon">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <p class="empty-state-title">{{ __('app.no_results') }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
