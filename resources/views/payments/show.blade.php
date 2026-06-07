@extends('layouts.app')
@section('title', 'Payment Details')
@php
$pageTitle = 'Payment Details';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Payments', 'url' => route('payments.index')], ['label' => $payment->reference, 'url' => '']];
@endphp
@section('content')
<div class="max-w-4xl mx-auto">
    <div class="card">
        <div class="card-header flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Payment Receipt</h3>
            <span class="badge badge-success">Completed</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div><h4 class="font-semibold text-gray-700 mb-2">Payment Information</h4><dl class="space-y-2"><div><dt class="text-sm text-gray-500">Reference</dt><dd class="text-sm font-semibold">{{ $payment->reference }}</dd></div><div><dt class="text-sm text-gray-500">Date</dt><dd class="text-sm">{{ $payment->payment_date->format('M d, Y') }}</dd></div><div><dt class="text-sm text-gray-500">Type</dt><dd class="text-sm">{{ ucfirst($payment->type) }}</dd></div><div><dt class="text-sm text-gray-500">Payment Method</dt><dd class="text-sm">{{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}</dd></div></dl></div>
            @if($payment->invoice)<div><h4 class="font-semibold text-gray-700 mb-2">Invoice Information</h4><dl class="space-y-2"><div><dt class="text-sm text-gray-500">Invoice Number</dt><dd class="text-sm"><a href="{{ route('invoices.show', $payment->invoice_id) }}" class="text-blue-600 hover:underline">{{ $payment->invoice->invoice_number }}</a></dd></div><div><dt class="text-sm text-gray-500">Customer</dt><dd class="text-sm">{{ $payment->invoice->customer->name }}</dd></div><div><dt class="text-sm text-gray-500">Invoice Total</dt><dd class="text-sm">{{ number_format($payment->invoice->total, 2) }} DH</dd></div><div><dt class="text-sm text-gray-500">Remaining Balance</dt><dd class="text-sm font-semibold {{ ($payment->invoice->total - $payment->invoice->paid) > 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($payment->invoice->total - $payment->invoice->paid, 2) }} DH</dd></div></dl></div>@endif
        </div>
        <div class="bg-blue-50 p-6 rounded-lg border border-blue-200"><div class="flex justify-between items-center"><span class="text-lg font-semibold text-blue-900">Amount Paid</span><span class="text-3xl font-bold text-blue-600">{{ number_format($payment->amount, 2) }} DH</span></div></div>
        @if($payment->notes)<div class="mt-6"><h4 class="font-semibold text-gray-700 mb-2">Notes</h4><p class="text-gray-700">{{ $payment->notes }}</p></div>@endif
        <div class="mt-6 flex justify-end space-x-2"><a href="{{ route('payments.index') }}" class="btn btn-outline">Back to Payments</a><a href="{{ route('payments.print', $payment->id) }}" target="_blank" class="btn btn-primary"><svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>Print Receipt</a></div>
    </div>
</div>
@endsection
