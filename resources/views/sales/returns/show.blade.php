@extends('layouts.app')
@section('title', __('app.sales_return_details'))
@php
$pageTitle = $return->reference;
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.sales'),     'url' => route('sales.index')],
    ['label' => __('app.returns'),   'url' => route('sales.returns.index')],
    ['label' => $return->reference,  'url' => ''],
];
$items = $return->items ?? collect();
@endphp

@section('content')

{{-- Top action bar --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('sales.returns.index') }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back') }}
    </a>

    @if($return->status === 'approved')
        <span class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 bg-emerald-50 px-4 py-2 rounded-lg border border-emerald-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ __('app.approved') }}
        </span>
    @elseif($return->status === 'rejected')
        <span class="inline-flex items-center gap-2 text-sm font-semibold text-rose-600 bg-rose-50 px-4 py-2 rounded-lg border border-rose-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            {{ __('app.rejected') }}
        </span>
    @else
        <form method="POST" action="{{ route('sales.returns.update-status', $return->id) }}"
              class="flex items-center gap-2">
            @csrf
            <select name="status" class="form-control py-1.5 text-sm">
                <option value="pending"  {{ $return->status === 'pending'  ? 'selected' : '' }}>{{ __('app.pending') }}</option>
                <option value="approved" {{ $return->status === 'approved' ? 'selected' : '' }}>{{ __('app.approved') }}</option>
                <option value="rejected" {{ $return->status === 'rejected' ? 'selected' : '' }}>{{ __('app.rejected') }}</option>
            </select>
            <button type="submit"
                    onclick="return confirm('{{ __('app.confirm_update_status') ?? 'Update status?' }}')"
                    class="btn btn-primary btn-sm">
                {{ __('app.update_status') }}
            </button>
        </form>
    @endif
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- LEFT: info + summary --}}
    <div class="space-y-5">

        {{-- Return info card --}}
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
                    <span class="text-sm text-slate-500 flex-shrink-0">{{ __('app.sale_ref') }}</span>
                    <a href="{{ route('sales.show', $return->sale_id) }}"
                       class="text-sm font-semibold text-indigo-600 hover:underline text-right">
                        {{ $return->sale->reference ?? '—' }}
                    </a>
                </div>
                <div class="flex items-start justify-between px-5 py-3 gap-3">
                    <span class="text-sm text-slate-500 flex-shrink-0">{{ __('app.customer') }}</span>
                    <span class="text-sm font-semibold text-slate-700 text-right">
                        {{ $return->sale->customer->name ?? __('app.walk_in') }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.return_date') }}</span>
                    <span class="text-sm font-semibold text-slate-700">
                        {{ $return->date ? \Carbon\Carbon::parse($return->date)->format('d M Y') : '—' }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.status') }}</span>
                    @php
                        $statusMap = [
                            'approved' => ['class' => 'badge-success', 'label' => __('app.approved')],
                            'rejected' => ['class' => 'badge-danger',  'label' => __('app.rejected')],
                            'pending'  => ['class' => 'badge-warning', 'label' => __('app.pending')],
                        ];
                        $sc = $statusMap[$return->status] ?? ['class' => 'badge-secondary', 'label' => $return->status];
                    @endphp
                    <span class="badge {{ $sc['class'] }}">{{ $sc['label'] }}</span>
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

        {{-- Reason --}}
        @if($return->reason)
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.reason') }}</h3></div>
            <div class="card-body">
                <p class="text-sm text-slate-600 leading-relaxed">{{ $return->reason }}</p>
            </div>
        </div>
        @endif

        {{-- Notes --}}
        @if($return->notes)
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.notes') }}</h3></div>
            <div class="card-body">
                <p class="text-sm text-slate-600 leading-relaxed">{{ $return->notes }}</p>
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
                            <th class="text-right w-24">{{ __('app.quantity') }}</th>
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
                                    <div class="text-sm font-semibold text-slate-800">
                                        {{ $item->product->name ?? '—' }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="mono text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md">
                                    {{ $item->product->sku ?? '—' }}
                                </span>
                            </td>
                            <td class="text-right text-sm font-bold text-rose-600">{{ $item->quantity }}</td>
                            <td class="text-right text-sm text-slate-600">
                                {{ number_format($item->price ?? 0, 2) }} DH
                            </td>
                            <td class="text-right text-sm font-bold text-slate-800">
                                {{ number_format(($item->quantity) * ($item->price ?? 0), 2) }} DH
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
