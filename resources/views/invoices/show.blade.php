@extends('layouts.app')
@section('title', __('app.invoice_details'))

@php
use App\Models\Setting;
$S = Setting::all()->pluck('value', 'key');
$accent      = $S['invoice_color'] ?? '#4F46E5';
$accentLight = 'bg-indigo-50';   // used for Tailwind classes, inline style for dynamic color

$pageTitle = $invoice->reference;
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.invoices'),  'url' => route('invoices.index')],
    ['label' => $invoice->reference, 'url' => ''],
];

$paid      = $invoice->paid_amount  ?? 0;
$total     = $invoice->total_amount ?? 0;
$due       = max(0, $total - $paid);
$isOverdue = $invoice->due_date && $invoice->due_date->isPast() && $due > 0;
$isPaid    = $paid >= $total && $total > 0;
$isPartial = !$isPaid && $paid > 0;

$statusConfig = match(true) {
    $isPaid    => ['badge' => 'badge-success', 'bg' => 'bg-emerald-100', 'icon' => 'text-emerald-600', 'label' => __('app.paid')],
    $isPartial => ['badge' => 'badge-warning', 'bg' => 'bg-amber-100',   'icon' => 'text-amber-600',   'label' => __('app.partial')],
    default    => ['badge' => 'badge-danger',  'bg' => 'bg-rose-100',    'icon' => 'text-rose-600',    'label' => __('app.unpaid')],
};

$items    = $invoice->items ?? collect();
$subtotal = $items->sum(fn($i) => ($i->quantity ?? 0) * ($i->price ?? 0));
@endphp

@section('content')

{{-- ── Top action bar ── --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('invoices.index') }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back') }}
    </a>
    <div class="flex items-center gap-2">
        @if(Route::has('invoices.print'))
        <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="btn btn-ghost btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            {{ __('app.print') }}
        </a>
        @endif
        @if(Route::has('invoices.download'))
        <a href="{{ route('invoices.download', $invoice->id) }}" class="btn btn-ghost btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            {{ __('app.download_pdf') }}
        </a>
        @endif
        @if($due > 0)
        <a href="{{ route('payments.create', ['invoice' => $invoice->id]) }}" class="btn btn-success btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            {{ __('app.record_payment') }}
        </a>
        @endif
        <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            {{ __('app.edit') }}
        </a>
    </div>
</div>

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">{{ session('success') }}</div>
@endif

{{-- ── Accent header band ── --}}
<div class="rounded-2xl overflow-hidden mb-6 shadow-sm" style="background:{{ $accent }}">
    <div class="px-6 py-5 flex items-center justify-between">
        <div>
            <p class="text-sm font-medium" style="color:rgba(255,255,255,.7)">{{ __('app.invoice_number') }}</p>
            <p class="text-2xl font-black text-white font-mono mt-0.5">{{ $invoice->reference }}</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1.5 rounded-full text-sm font-bold
                {{ $isPaid ? 'bg-white text-emerald-700' : ($isPartial ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                {{ $statusConfig['label'] }}
            </span>
            @if($isOverdue)
            <span class="px-3 py-1.5 rounded-full text-sm font-bold bg-white text-rose-700">{{ __('app.overdue') }}</span>
            @endif
        </div>
    </div>
    <div class="grid grid-cols-3 divide-x bg-black bg-opacity-10" style="border-color:rgba(255,255,255,.15)">
        <div class="px-6 py-3">
            <p class="text-xs" style="color:rgba(255,255,255,.65)">{{ __('app.invoice_date') ?? 'Date' }}</p>
            <p class="font-semibold text-white mt-0.5">{{ $invoice->date ? $invoice->date->format('d M Y') : '—' }}</p>
        </div>
        <div class="px-6 py-3">
            <p class="text-xs" style="color:rgba(255,255,255,.65)">{{ __('app.due_date') }}</p>
            <p class="font-semibold text-white mt-0.5">{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '—' }}</p>
        </div>
        <div class="px-6 py-3">
            <p class="text-xs" style="color:rgba(255,255,255,.65)">{{ __('app.customer') }}</p>
            <p class="font-semibold text-white mt-0.5">{{ $invoice->customer->name ?? '—' }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── LEFT ── --}}
    <div class="space-y-5">

        {{-- Invoice info --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg {{ $statusConfig['bg'] }} flex items-center justify-center">
                        <svg class="w-4 h-4 {{ $statusConfig['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.invoice_info') }}</h3>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.invoice_number') }}</span>
                    <span class="mono text-sm font-semibold text-slate-700 bg-slate-100 px-2.5 py-0.5 rounded-lg">{{ $invoice->reference }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.status') }}</span>
                    <span class="badge {{ $statusConfig['badge'] }}">{{ $statusConfig['label'] }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.customer') }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $invoice->customer->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.invoice_date') ?? 'Date' }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $invoice->date ? $invoice->date->format('d M Y') : '—' }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.due_date') }}</span>
                    <span class="text-sm font-semibold {{ $isOverdue ? 'text-rose-600' : 'text-slate-700' }}">
                        {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '—' }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.created_by') }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $invoice->user->full_name ?? $invoice->user->name ?? '—' }}</span>
                </div>
            </div>
            @if($invoice->customer->email ?? false)
            <div class="px-5 py-4 border-t border-slate-100">
                <form method="POST" action="{{ route('invoices.send-email', $invoice) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ __('app.send_by_email') }}
                    </button>
                </form>
            </div>
            @endif
        </div>

        {{-- Financial summary --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.summary') }}</h3></div>
            <div class="divide-y divide-slate-100">
                @if($subtotal > 0 && ($invoice->tax_amount ?? 0) > 0)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.subtotal') }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ number_format($subtotal, 2) }} DH</span>
                </div>
                @endif
                @if(($invoice->tax_amount ?? 0) > 0)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.tax') }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ number_format($invoice->tax_amount, 2) }} DH</span>
                </div>
                @endif
                @if(($invoice->discount_amount ?? 0) > 0)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.discount') }}</span>
                    <span class="text-sm font-semibold text-rose-600">-{{ number_format($invoice->discount_amount, 2) }} DH</span>
                </div>
                @endif
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm font-bold text-slate-700">{{ __('app.total') }}</span>
                    <span class="text-base font-bold" style="color:{{ $accent }}">{{ number_format($total, 2) }} DH</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.paid') }}</span>
                    <span class="text-sm font-semibold text-emerald-600">{{ number_format($paid, 2) }} DH</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3 {{ $due > 0 ? 'bg-rose-50' : 'bg-emerald-50' }} rounded-b-xl">
                    <span class="text-sm font-bold {{ $due > 0 ? 'text-rose-700' : 'text-emerald-700' }}">{{ __('app.balance_due') }}</span>
                    <span class="text-base font-black {{ $due > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ number_format($due, 2) }} DH</span>
                </div>
            </div>
        </div>

        @if($invoice->notes)
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.notes') }}</h3></div>
            <div class="card-body"><p class="text-sm text-slate-600 leading-relaxed">{{ $invoice->notes }}</p></div>
        </div>
        @endif

        @if($invoice->terms)
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.invoice_terms') ?? 'Terms' }}</h3></div>
            <div class="card-body"><p class="text-sm text-slate-600 leading-relaxed">{{ $invoice->terms }}</p></div>
        </div>
        @endif

    </div>

    {{-- ── RIGHT ── --}}
    <div class="xl:col-span-2 space-y-6">

        {{-- Products table --}}
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
                                        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                                             style="background:{{ $accent }}22">
                                            <span class="text-xs font-bold" style="color:{{ $accent }}">
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
                            <td class="text-right text-sm text-slate-600">{{ number_format($item->price ?? 0, 2) }} DH</td>
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
                                <span class="text-base font-bold" style="color:{{ $accent }}">
                                    {{ number_format($total, 2) }} DH
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

        {{-- Payment history --}}
        @if(($invoice->payments ?? collect())->count())
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.payment_history') }}</h3>
                </div>
                <span class="badge badge-secondary">{{ $invoice->payments->count() }}</span>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('app.date') }}</th>
                            <th>{{ __('app.amount') }}</th>
                            <th>{{ __('app.method') }}</th>
                            <th>{{ __('app.reference') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->payments as $payment)
                        <tr>
                            <td class="text-sm text-slate-600">{{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y') }}</td>
                            <td class="text-sm font-bold text-emerald-600">{{ number_format($payment->amount, 2) }} DH</td>
                            <td>
                                <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md font-medium">
                                    {{ ucfirst($payment->payment_method ?? '—') }}
                                </span>
                            </td>
                            <td class="mono text-xs text-slate-500">{{ $payment->reference ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

</div>
@endsection
