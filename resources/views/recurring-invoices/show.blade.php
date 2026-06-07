@extends('layouts.app')
@section('title', __('app.recurring_invoice_details'))
@php $pageTitle = __('app.recurring_invoice_details'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.recurring_invoices'),'url'=>route('recurring-invoices.index')],['label'=>__('app.view')]]; @endphp
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="card overflow-hidden shadow-2xl rounded-2xl border-none">
        {{-- Header --}}
        <div class="bg-indigo-900 p-8 text-white flex justify-between items-center">
            <div>
                <p class="text-[10px] font-black uppercase text-indigo-300 tracking-[0.2em] mb-1">{{ __('app.subscription_record') }}</p>
                <h2 class="text-3xl font-black italic tracking-tighter">{{ $recurringInvoice->customer->name ?? '—' }}</h2>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-black uppercase text-indigo-300 tracking-[0.2em] mb-1">{{ __('app.billing_frequency') }}</p>
                <p class="text-xl font-bold capitalize">{{ __('app.' . $recurringInvoice->frequency) }}</p>
            </div>
        </div>

        {{-- Stats Bar --}}
        <div class="p-8 bg-white grid grid-cols-2 md:grid-cols-4 gap-8 border-b">
            <div>
                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">{{ __('app.status') }}</label>
                <span class="badge @if($recurringInvoice->status == 'active') badge-success @elseif($recurringInvoice->status == 'paused') badge-warning @else badge-secondary @endif">
                    {{ __('app.' . $recurringInvoice->status) }}
                </span>
            </div>
            <div>
                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">{{ __('app.next_bill_date') }}</label>
                <p class="font-black text-gray-800">{{ $recurringInvoice->next_run_at ? $recurringInvoice->next_run_at->format('d/m/Y') : '—' }}</p>
            </div>
            <div>
                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">{{ __('app.auto_email') }}</label>
                <p class="font-black text-xs {{ $recurringInvoice->send_email ? 'text-green-600' : 'text-gray-400' }}">
                    {{ $recurringInvoice->send_email ? __('app.enabled') : __('app.disabled') }}
                </p>
            </div>
            <div>
                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">{{ __('app.cycle_amount') }}</label>
                <p class="text-xl font-black text-blue-600">{{ number_format($recurringInvoice->total, 2) }} DH</p>
            </div>
        </div>

        {{-- Details --}}
        <div class="p-8 space-y-8 bg-white">
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-4">{{ __('app.subscription_plan_items') }}</label>
                <div class="rounded-2xl border-2 border-gray-50 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left"><th class="px-6 py-4">{{ __('app.item') }}</th><th class="px-6 py-4 text-center">{{ __('app.qty') }}</th><th class="px-6 py-4 text-right">{{ __('app.price') }}</th></tr>
                        </thead>
                        <tbody>
                            @php $items = json_decode($recurringInvoice->items_data ?? '[]', true) ?: []; @endphp
                            @forelse($items as $i)
                            <tr class="border-b border-gray-50 italic">
                                <td class="px-6 py-4 font-bold text-gray-700">{{ __('app.product') }} ID: {{ $i['product_id'] }}</td>
                                <td class="px-6 py-4 text-center font-mono font-bold">{{ $i['quantity'] }}</td>
                                <td class="px-6 py-4 text-right font-black text-blue-600">{{ number_format($i['price'] ?? 0, 2) }} DH</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="p-6 text-center text-gray-400 italic">{{ __('app.no_items_defined_in_subscription_data') }}</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50/50">
                            <tr><td colspan="2" class="px-6 py-4 font-black uppercase text-right text-gray-400">{{ __('app.recurring_total') }}</td><td class="px-6 py-4 text-right font-black text-2xl text-indigo-900">{{ number_format($recurringInvoice->total, 2) }} DH</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($recurringInvoice->notes)
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">{{ __('app.terms_notes') }}</label>
                <p class="text-sm text-gray-600 italic bg-gray-50 p-4 rounded-xl border border-gray-200">{{ $recurringInvoice->notes }}</p>
            </div>
            @endif
        </div>

        <div class="p-8 bg-gray-50 flex gap-4">
            <a href="{{ route('recurring-invoices.edit', $recurringInvoice) }}" class="btn btn-primary px-10 font-bold">{{ __('app.manage_subscription') }}</a>
            <a href="{{ route('recurring-invoices.index') }}" class="btn btn-secondary px-10 font-bold">{{ __('app.back') }}</a>
        </div>
    </div>
</div>
@endsection
