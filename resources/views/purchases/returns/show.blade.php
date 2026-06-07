@extends('layouts.app')
@section('title', __('app.purchase_return_details'))
@php
$pageTitle = $return->reference;
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.purchases'),  'url' => route('purchases.index')],
    ['label' => __('app.returns'),    'url' => route('purchases.returns.index')],
    ['label' => $return->reference,   'url' => ''],
];
$items = $return->items ?? collect();
@endphp

@section('content')

<div class="flex items-center justify-between mb-6">
    <a href="{{ route('purchases.returns.index') }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back') }}
    </a>

    @if($return->isApplied())
        <span class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 bg-emerald-50 px-4 py-2 rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ __('app.applied') }} — {{ $return->applied_at->format('d M Y H:i') }}
        </span>
    @else
        <form method="POST" action="{{ route('purchases.returns.apply', $return->id) }}"
              onsubmit="return confirm('{{ __('app.confirm_apply_return') }}')">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                {{ __('app.apply_return') }}
            </button>
        </form>
    @endif
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- LEFT: info card --}}
    <div class="space-y-5">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.return_info') }}</h3>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.reference') }}</span>
                    <span class="mono text-sm font-semibold text-slate-700 bg-slate-100 px-2.5 py-0.5 rounded-lg">
                        {{ $return->reference }}
                    </span>
                </div>
                <div class="flex items-start justify-between px-5 py-3 gap-3">
                    <span class="text-sm text-slate-500 flex-shrink-0">{{ __('app.purchase_ref') }}</span>
                    <a href="{{ route('purchases.show', $return->purchase_id) }}"
                       class="text-sm font-semibold text-indigo-600 hover:underline text-right">
                        {{ $return->purchase->reference ?? '—' }}
                    </a>
                </div>
                <div class="flex items-start justify-between px-5 py-3 gap-3">
                    <span class="text-sm text-slate-500 flex-shrink-0">{{ __('app.supplier') }}</span>
                    <span class="text-sm font-semibold text-slate-700 text-right">
                        {{ $return->purchase->supplier->name ?? '—' }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.date') }}</span>
                    <span class="text-sm font-semibold text-slate-700">
                        {{ $return->date ? \Carbon\Carbon::parse($return->date)->format('d M Y') : '—' }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.created_by') }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $return->user->full_name ?? '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Summary --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.summary') }}</h3></div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm font-bold text-slate-700">{{ __('app.return_total') }}</span>
                    <span class="text-base font-bold text-rose-600">{{ number_format($return->total_amount ?? 0, 2) }} DH</span>
                </div>
            </div>
        </div>

        @if($return->notes)
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.notes') }}</h3></div>
            <div class="card-body">
                <p class="text-sm text-slate-600 leading-relaxed">{{ $return->notes }}</p>
            </div>
        </div>
        @endif

        @if($return->reason)
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.reason') }}</h3></div>
            <div class="card-body">
                <p class="text-sm text-slate-600 leading-relaxed">{{ $return->reason }}</p>
            </div>
        </div>
        @endif
    </div>

    {{-- RIGHT: returned products --}}
    <div class="xl:col-span-2">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.returned_products') }}</h3>
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
                                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-rose-400 to-rose-600 flex items-center justify-center flex-shrink-0">
                                            <span class="text-white text-xs font-bold">
                                                {{ strtoupper(substr($item->product->name ?? 'P', 0, 2)) }}
                                            </span>
                                        </div>
                                    @endif
                                    <div class="text-sm font-semibold text-slate-800">{{ $item->product->name ?? '—' }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="mono text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md">
                                    {{ $item->product->sku ?? '—' }}
                                </span>
                            </td>
                            <td class="text-right text-sm font-bold text-rose-600">{{ $item->quantity }}</td>
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
                                {{ __('app.return_total') }}
                            </td>
                            <td class="px-4 py-3 text-right border-t border-slate-100">
                                <span class="text-base font-bold text-rose-600">
                                    {{ number_format($return->total_amount ?? 0, 2) }} DH
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
                </div>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
