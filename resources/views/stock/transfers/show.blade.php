@extends('layouts.app')
@section('title', __('app.transfer_details'))

@php
$pageTitle = __('app.transfer_details');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.stock'),      'url' => '#'],
    ['label' => __('app.transfers'),  'url' => route('stock.transfers.index')],
    ['label' => $transfer->reference, 'url' => ''],
];

$statusConfig = match($transfer->status) {
    'pending'  => ['badge' => 'badge-warning', 'bg' => 'bg-amber-100',  'icon' => 'text-amber-600',  'label' => __('app.pending')],
    'sent'     => ['badge' => 'badge-info',    'bg' => 'bg-sky-100',    'icon' => 'text-sky-600',    'label' => __('app.sent')],
    'received' => ['badge' => 'badge-success', 'bg' => 'bg-emerald-100','icon' => 'text-emerald-600','label' => __('app.received')],
    default    => ['badge' => 'badge-secondary','bg' => 'bg-slate-100',  'icon' => 'text-slate-500',  'label' => ucfirst($transfer->status)],
};

$items = $transfer->relationLoaded('items') ? $transfer->items : $transfer->items()->with('product')->get();
@endphp

@section('content')

{{-- ── Top action bar ──────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('stock.transfers.index') }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back') }}
    </a>
    <div class="flex items-center gap-2">
        <a href="{{ route('stock.transfers.edit', $transfer) }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            {{ __('app.edit') }}
        </a>
        <form action="{{ route('stock.transfers.destroy', $transfer) }}" method="POST"
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.transfer_info') }}</h3>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                {{-- Reference --}}
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.reference') }}</span>
                    <span class="mono text-sm font-semibold text-slate-700 bg-slate-100 px-2.5 py-0.5 rounded-lg">
                        {{ $transfer->reference }}
                    </span>
                </div>
                {{-- From warehouse --}}
                <div class="flex items-start justify-between px-5 py-3 gap-3">
                    <span class="text-sm text-slate-500 flex-shrink-0">{{ __('app.from_warehouse') }}</span>
                    <span class="text-sm font-semibold text-slate-700 text-right">
                        {{ $transfer->fromWarehouse->name ?? '—' }}
                    </span>
                </div>
                {{-- To warehouse --}}
                <div class="flex items-start justify-between px-5 py-3 gap-3">
                    <span class="text-sm text-slate-500 flex-shrink-0">{{ __('app.to_warehouse') }}</span>
                    <span class="text-sm font-semibold text-indigo-600 text-right">
                        {{ $transfer->toWarehouse->name ?? '—' }}
                    </span>
                </div>
                {{-- Status --}}
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.status') }}</span>
                    <span class="badge {{ $statusConfig['badge'] }}">{{ $statusConfig['label'] }}</span>
                </div>
                {{-- Date --}}
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.date') }}</span>
                    <span class="text-sm font-semibold text-slate-700">
                        {{ ($transfer->date ? \Carbon\Carbon::parse($transfer->date) : $transfer->created_at)->format('d M Y') }}
                    </span>
                </div>
                {{-- Created by --}}
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.created_by') }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $transfer->user->full_name ?? '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Notes card --}}
        @if($transfer->notes)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('app.notes') }}</h3>
            </div>
            <div class="card-body">
                <p class="text-sm text-slate-600 leading-relaxed">{{ $transfer->notes }}</p>
            </div>
        </div>
        @endif

        {{-- Transfer route visual --}}
        <div class="card card-body">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">{{ __('app.route') ?? 'Route' }}</p>
            <div class="flex items-center gap-3">
                <div class="flex-1 text-center">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center mx-auto mb-1">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-700 leading-tight">{{ $transfer->fromWarehouse->name ?? '—' }}</p>
                    <p class="text-xs text-slate-400">{{ __('app.source') ?? 'Source' }}</p>
                </div>
                <div class="flex flex-col items-center gap-1">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                    <span class="text-xs text-slate-400">{{ $items->count() }} {{ __('app.items') }}</span>
                </div>
                <div class="flex-1 text-center">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center mx-auto mb-1">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-indigo-700 leading-tight">{{ $transfer->toWarehouse->name ?? '—' }}</p>
                    <p class="text-xs text-slate-400">{{ __('app.destination') ?? 'Destination' }}</p>
                </div>
            </div>
        </div>

    </div>

    {{-- ── RIGHT: transferred products ────────────────────────────── --}}
    <div class="xl:col-span-2">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.products_transferred') }}</h3>
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
                            <th class="w-32 text-right">{{ __('app.quantity') }}</th>
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
                                        <div class="text-sm font-semibold text-slate-800">
                                            {{ $item->product->name ?? '—' }}
                                        </div>
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
                            <td class="text-right">
                                <span class="text-base font-bold text-indigo-600">{{ $item->quantity }}</span>
                                <span class="text-xs text-slate-400 ml-1">{{ $item->product?->unit?->short_name ?? '' }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-sm font-semibold text-slate-600 text-right border-t border-slate-100">
                                {{ __('app.total') }}
                            </td>
                            <td class="px-4 py-3 text-right border-t border-slate-100">
                                <span class="text-base font-bold text-slate-900">{{ $items->sum('quantity') }}</span>
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
                    <p class="empty-state-desc">{{ __('app.no_items_in_transfer') ?? 'No products were added to this transfer.' }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
