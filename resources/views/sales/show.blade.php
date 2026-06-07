@extends('layouts.app')
@section('title', __('app.sale_details'))

@php
$pageTitle = $sale->reference;
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.sales'),     'url' => route('sales.index')],
    ['label' => $sale->reference,    'url' => ''],
];

$statusConfig = match($sale->status ?? '') {
    'confirmed'  => ['badge' => 'badge-success',   'bg' => 'bg-emerald-100', 'icon' => 'text-emerald-600', 'label' => __('app.confirmed')],
    'delivered'  => ['badge' => 'badge-info',       'bg' => 'bg-sky-100',     'icon' => 'text-sky-600',     'label' => __('app.delivered')],
    'draft'      => ['badge' => 'badge-secondary',  'bg' => 'bg-slate-100',   'icon' => 'text-slate-500',   'label' => __('app.draft')],
    default      => ['badge' => 'badge-secondary',  'bg' => 'bg-slate-100',   'icon' => 'text-slate-500',   'label' => ucfirst($sale->status ?? 'N/A')],
};

$items = $sale->items ?? collect();
$due   = max(0, ($sale->total_amount ?? 0) - ($sale->paid_amount ?? 0));
$date  = $sale->date ?? $sale->sale_date ?? $sale->created_at;
@endphp

@section('content')

{{-- ── Top action bar ──────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('sales.index') }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back') }}
    </a>
    <div class="flex items-center gap-2">
        <a href="{{ route('sales.print', $sale->id) }}" target="_blank" class="btn btn-ghost btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            {{ __('app.print_invoice') }}
        </a>
        <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            {{ __('app.edit') }}
        </a>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.sale_info') }}</h3>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.reference') }}</span>
                    <span class="mono text-sm font-semibold text-slate-700 bg-slate-100 px-2.5 py-0.5 rounded-lg">
                        {{ $sale->reference }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.status') }}</span>
                    <span class="badge {{ $statusConfig['badge'] }}">{{ $statusConfig['label'] }}</span>
                </div>
                <div class="flex items-start justify-between px-5 py-3 gap-3">
                    <span class="text-sm text-slate-500 flex-shrink-0">{{ __('app.customer') }}</span>
                    <span class="text-sm font-semibold text-slate-700 text-right">{{ $sale->customer->name ?? __('app.walk_in_customer') }}</span>
                </div>
                <div class="flex items-start justify-between px-5 py-3 gap-3">
                    <span class="text-sm text-slate-500 flex-shrink-0">{{ __('app.warehouse') }}</span>
                    <span class="text-sm font-semibold text-slate-700 text-right">{{ $sale->warehouse->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.date') }}</span>
                    <span class="text-sm font-semibold text-slate-700">
                        {{ $date ? \Carbon\Carbon::parse($date)->format('d M Y') : '—' }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.created_by') }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $sale->user->full_name ?? '—' }}</span>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 space-y-2">
                @if($sale->customer->email ?? false)
                <form method="POST" action="{{ route('sales.send-email', $sale) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ __('app.send_confirmation_email') }}
                    </button>
                </form>
                @endif
                <a href="{{ route('sales.receipt', $sale->id) }}" target="_blank" class="btn btn-ghost btn-sm w-full">
                    {{ __('app.receipt') }}
                </a>
            </div>
        </div>

        {{-- Financial summary --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('app.summary') }}</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @php $calcSubtotal = $items->sum(fn($i) => ($i->quantity ?? 0) * ($i->price ?? 0)); @endphp
                @if($calcSubtotal > 0)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.subtotal') }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ number_format($calcSubtotal, 2) }} DH</span>
                </div>
                @endif
                @if(($sale->tax_amount ?? 0) > 0)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.tax') }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ number_format($sale->tax_amount, 2) }} DH</span>
                </div>
                @endif
                @if(($sale->discount_amount ?? 0) > 0)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.discount') }}</span>
                    <span class="text-sm font-semibold text-rose-600">-{{ number_format($sale->discount_amount, 2) }} DH</span>
                </div>
                @endif
                @if(($sale->shipping_cost ?? 0) > 0)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.shipping') }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ number_format($sale->shipping_cost, 2) }} DH</span>
                </div>
                @endif
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm font-bold text-slate-700">{{ __('app.total') }}</span>
                    <span class="text-base font-bold text-indigo-600">{{ number_format($sale->total_amount ?? 0, 2) }} DH</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.paid') }}</span>
                    <span class="text-sm font-semibold text-emerald-600">{{ number_format($sale->paid_amount ?? 0, 2) }} DH</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.balance') }}</span>
                    <span class="text-sm font-bold {{ $due > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ number_format($due, 2) }} DH
                    </span>
                </div>
            </div>
        </div>

        @if($sale->notes)
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.notes') }}</h3></div>
            <div class="card-body">
                <p class="text-sm text-slate-600 leading-relaxed">{{ $sale->notes }}</p>
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
                            <th class="text-right w-28">{{ __('app.unit_price') }}</th>
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
                                {{ number_format($item->price ?? 0, 2) }} DH
                            </td>
                            <td class="text-right text-sm font-bold text-slate-800">
                                {{ number_format(($item->quantity ?? 0) * ($item->price ?? 0), 2) }} DH
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
                                    {{ number_format($sale->total_amount ?? 0, 2) }} DH
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
                    <p class="empty-state-desc">{{ __('app.no_items_in_purchase') ?? 'No products were added to this sale.' }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
