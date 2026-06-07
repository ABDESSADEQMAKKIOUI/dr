@extends('layouts.app')
@section('title', __('app.purchase_details') ?? 'Purchase Details')

@php
$pageTitle = $purchase->reference;
$breadcrumbs = [
    ['label' => __('app.dashboard'),  'url' => route('dashboard')],
    ['label' => __('app.purchases'),  'url' => route('purchases.index')],
    ['label' => $purchase->reference, 'url' => ''],
];

$statusConfig = match($purchase->status ?? '') {
    'received' => ['badge' => 'badge-success',   'bg' => 'bg-emerald-100', 'icon' => 'text-emerald-600', 'label' => __('app.received') ?? 'Received'],
    'ordered'  => ['badge' => 'badge-info',      'bg' => 'bg-sky-100',     'icon' => 'text-sky-600',     'label' => __('app.ordered')  ?? 'Ordered'],
    'draft'    => ['badge' => 'badge-secondary', 'bg' => 'bg-slate-100',   'icon' => 'text-slate-500',   'label' => __('app.draft')],
    default    => ['badge' => 'badge-secondary', 'bg' => 'bg-slate-100',   'icon' => 'text-slate-500',   'label' => ucfirst($purchase->status ?? 'N/A')],
};

$items  = $purchase->items ?? collect();
$due    = max(0, ($purchase->total_amount ?? 0) - ($purchase->paid_amount ?? 0));
$date   = $purchase->date ?? $purchase->purchase_date ?? $purchase->created_at;
@endphp

@section('content')

{{-- ── Top action bar ──────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('purchases.index') }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back') }}
    </a>
    <div class="flex items-center gap-2">
        <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            {{ __('app.edit') }}
        </a>
        <form method="POST" action="{{ route('purchases.destroy', $purchase->id) }}"
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

        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg {{ $statusConfig['bg'] }} flex items-center justify-center">
                        <svg class="w-4 h-4 {{ $statusConfig['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.purchase_info') ?? 'Purchase Info' }}</h3>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.reference') }}</span>
                    <span class="mono text-sm font-semibold text-slate-700 bg-slate-100 px-2.5 py-0.5 rounded-lg">
                        {{ $purchase->reference }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.status') }}</span>
                    <span class="badge {{ $statusConfig['badge'] }}">{{ $statusConfig['label'] }}</span>
                </div>
                <div class="flex items-start justify-between px-5 py-3 gap-3">
                    <span class="text-sm text-slate-500 flex-shrink-0">{{ __('app.supplier') }}</span>
                    <span class="text-sm font-semibold text-slate-700 text-right">{{ $purchase->supplier->name ?? '—' }}</span>
                </div>
                <div class="flex items-start justify-between px-5 py-3 gap-3">
                    <span class="text-sm text-slate-500 flex-shrink-0">{{ __('app.warehouse') }}</span>
                    <span class="text-sm font-semibold text-slate-700 text-right">{{ $purchase->warehouse->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.date') }}</span>
                    <span class="text-sm font-semibold text-slate-700">
                        {{ $date ? \Carbon\Carbon::parse($date)->format('d M Y') : '—' }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.created_by') }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $purchase->user->full_name ?? '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Financial summary --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('app.summary') ?? 'Summary' }}</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @if(($purchase->subtotal ?? 0) > 0)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.subtotal') }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ number_format($purchase->subtotal ?? 0, 2) }} DH</span>
                </div>
                @endif
                @if(($purchase->tax ?? 0) > 0)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.tax') }} ({{ $purchase->tax }}%)</span>
                    <span class="text-sm font-semibold text-slate-700">{{ number_format($purchase->tax_amount ?? 0, 2) }} DH</span>
                </div>
                @endif
                @if(($purchase->discount ?? 0) > 0)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.discount') }}</span>
                    <span class="text-sm font-semibold text-rose-600">-{{ number_format($purchase->discount ?? 0, 2) }} DH</span>
                </div>
                @endif
                @if(($purchase->shipping ?? 0) > 0)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.shipping') ?? 'Shipping' }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ number_format($purchase->shipping ?? 0, 2) }} DH</span>
                </div>
                @endif
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm font-bold text-slate-700">{{ __('app.total') }}</span>
                    <span class="text-base font-bold text-indigo-600">{{ number_format($purchase->total_amount ?? 0, 2) }} DH</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.paid') }}</span>
                    <span class="text-sm font-semibold text-emerald-600">{{ number_format($purchase->paid_amount ?? 0, 2) }} DH</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.balance') }}</span>
                    <span class="text-sm font-bold {{ $due > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ number_format($due, 2) }} DH
                    </span>
                </div>
            </div>
        </div>

        @if($purchase->notes)
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.notes') }}</h3></div>
            <div class="card-body">
                <p class="text-sm text-slate-600 leading-relaxed">{{ $purchase->notes }}</p>
            </div>
        </div>
        @endif

    </div>

    {{-- ── RIGHT: products table ────────────────────────────────────── --}}
    <div class="xl:col-span-2">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.products') }}</h3>
                </div>
                @if($items->count())
                <span class="badge badge-secondary">{{ $items->count() }} {{ __('app.items') }}</span>
                @endif
            </div>

            @if($items->count())
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-10">#</th>
                            <th>{{ __('app.product') }}</th>
                            <th>{{ __('app.sku') }}</th>
                            <th class="text-right w-20">{{ __('app.quantity') }}</th>
                            <th class="text-right w-28">{{ __('app.unit_price') ?? 'Unit Price' }}</th>
                            <th class="text-right w-28">{{ __('app.subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($item->product?->image)
                                        <img src="{{ asset('storage/' . $item->product->image) }}"
                                             class="w-9 h-9 rounded-lg object-cover ring-1 ring-slate-200"
                                             alt="{{ $item->product->name }}">
                                    @else
                                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center flex-shrink-0">
                                            <span class="text-white text-xs font-bold">
                                                {{ strtoupper(substr($item->product->name ?? 'P', 0, 2)) }}
                                            </span>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-semibold text-slate-800">{{ $item->product->name ?? '—' }}</div>
                                        @if($item->product?->category)
                                        <div class="text-xs text-slate-400">{{ $item->product->category->name }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="mono text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md">
                                    {{ $item->product->sku ?? '—' }}
                                </span>
                            </td>
                            <td class="text-right text-sm font-semibold text-slate-700">{{ $item->quantity }}</td>
                            <td class="text-right text-sm text-slate-600">
                                {{ number_format($item->price ?? $item->unit_price ?? 0, 2) }} DH
                            </td>
                            <td class="text-right text-sm font-bold text-slate-800">
                                {{ number_format(($item->quantity) * ($item->price ?? $item->unit_price ?? 0), 2) }} DH
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-sm font-semibold text-slate-600 text-right border-t border-slate-100">
                                {{ __('app.total') }}
                            </td>
                            <td class="px-4 py-3 text-right border-t border-slate-100">
                                <span class="text-base font-bold text-indigo-600">
                                    {{ number_format($purchase->total_amount ?? 0, 2) }} DH
                                </span>
                            </td>
                        </tr>
                    </tfoot>
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
                    <p class="empty-state-desc">{{ __('app.no_items_in_purchase') ?? 'No products were added to this purchase.' }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
