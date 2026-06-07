@extends('layouts.app')
@section('title', 'Record Payment')
@php
$pageTitle = 'Record Payment';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Payments', 'url' => route('payments.index')], ['label' => 'Create', 'url' => '']];
@endphp
@section('content')
<div class="card max-w-3xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">New Payment</h3></div>
    <form method="POST" action="{{ route('payments.store') }}" data-validate>
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group"><label class="form-label">Payment Type *</label><select name="type" id="payment-type" class="form-control" required><option value="sale">Sale Payment</option><option value="expense">Expense Payment</option><option value="other">Other</option></select></div>
            <div class="form-group" id="invoice-group"><label class="form-label">Invoice *</label><select name="invoice_id" class="form-control"><option value="">Select Invoice</option>@foreach($invoices ?? [] as $inv)<option value="{{ $inv->id }}" data-balance="{{ $inv->total - $inv->paid }}">{{ $inv->invoice_number }} - {{ $inv->customer->name }} (Due: {{ number_format($inv->total - $inv->paid, 2) }} DH)</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Payment Date *</label><input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Amount *</label><input type="number" step="0.01" name="amount" id="amount-input" value="{{ old('amount') }}" class="form-control" required>@error('amount')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">Payment Method *</label><select name="payment_method" class="form-control" required><option value="cash">Cash</option><option value="bank_transfer">Bank Transfer</option><option value="credit_card">Credit Card</option><option value="check">Check</option><option value="mobile_payment">Mobile Payment</option></select></div>
            <div class="form-group"><label class="form-label">Reference</label><input type="text" name="reference" value="{{ old('reference') }}" class="form-control" placeholder="Transaction reference"></div>
            <div class="form-group md:col-span-2"><label class="form-label">Notes</label><textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea></div>
            <div class="form-group md:col-span-2"><label class="flex items-center"><input type="checkbox" name="send_receipt" value="1" class="w-4 h-4 text-blue-600 border-gray-300 rounded"><span class="ml-2 text-sm text-gray-700">Send receipt to customer via email</span></label></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('payments.index') }}" class="btn btn-outline">Cancel</a><button type="submit" class="btn btn-primary">Record Payment</button></div>
    </form>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const invoiceSelect = document.querySelector('select[name="invoice_id"]');
    const amountInput = document.getElementById('amount-input');
    
    if (invoiceSelect) {
        invoiceSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const balance = selectedOption.dataset.balance;
            if (balance) {
                amountInput.value = balance;
            }
        });
    }
});
</script>
@endpush
@endsection
