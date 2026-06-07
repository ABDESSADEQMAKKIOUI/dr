@extends('layouts.app')
@section('title', 'Create Transaction')
@php
$pageTitle = 'Create Transaction';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Accounting', 'url' => '#'], ['label' => 'Transactions', 'url' => route('accounting.transactions.index')], ['label' => 'Create', 'url' => '']];
@endphp
@section('content')
<div class="card max-w-4xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">New Journal Entry</h3></div>
    <form method="POST" action="{{ route('accounting.transactions.store') }}" data-validate>
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="form-group"><label class="form-label">Transaction Date *</label><input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Reference</label><input type="text" name="reference" value="{{ old('reference') }}" class="form-control" placeholder="Auto-generated if empty"></div>
            <div class="form-group md:col-span-2"><label class="form-label">Description *</label><textarea name="description" rows="2" class="form-control" required>{{ old('description') }}</textarea></div>
        </div>
        <div class="border-t border-gray-200 pt-4">
            <h4 class="font-semibold text-gray-800 mb-3">Journal Entries</h4>
            <div id="entries-container"><div class="grid grid-cols-12 gap-2 mb-3 border-b pb-3"><div class="col-span-5"><label class="form-label text-sm">Debit Account</label><select name="entries[0][debit_account]" class="form-control"><option value="">Select Account</option>@foreach($accounts ?? [] as $acc)<option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>@endforeach</select></div><div class="col-span-5"><label class="form-label text-sm">Credit Account</label><select name="entries[0][credit_account]" class="form-control"><option value="">Select Account</option>@foreach($accounts ?? [] as $acc)<option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>@endforeach</select></div><div class="col-span-2"><label class="form-label text-sm">Amount *</label><input type="number" step="0.01" name="entries[0][amount]" class="form-control entry-amount" required></div></div></div>
            <button type="button" onclick="addEntry()" class="btn btn-outline btn-sm">+ Add Entry</button>
            <div class="mt-4 bg-gray-50 p-4 rounded-lg"><div class="flex justify-between text-lg"><span class="font-semibold">Total:</span><span id="total-amount" class="font-bold text-blue-600">0.00 DH</span></div><p class="text-sm text-gray-600 mt-2">Debits and Credits must balance</p></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('accounting.transactions.index') }}" class="btn btn-outline">Cancel</a><button type="submit" class="btn btn-primary">Post Entry</button></div>
    </form>
</div>
@push('scripts')
<script>
let entryCount = 1;
function addEntry() {
    const container = document.getElementById('entries-container');
    const entry = `<div class="grid grid-cols-12 gap-2 mb-3 border-b pb-3"><div class="col-span-5"><select name="entries[${entryCount}][debit_account]" class="form-control"><option value="">Select Account</option>@foreach($accounts ?? [] as $acc)<option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>@endforeach</select></div><div class="col-span-5"><select name="entries[${entryCount}][credit_account]" class="form-control"><option value="">Select Account</option>@foreach($accounts ?? [] as $acc)<option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>@endforeach</select></div><div class="col-span-1"><input type="number" step="0.01" name="entries[${entryCount}][amount]" class="form-control entry-amount" required></div><div class="col-span-1"><button type="button" onclick="this.closest('.grid').remove();updateTotal()" class="btn btn-danger btn-sm w-full">×</button></div></div>`;
    container.insertAdjacentHTML('beforeend', entry);
    entryCount++;
}
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('entries-container').addEventListener('input', function(e) {
        if (e.target.classList.contains('entry-amount')) {
            updateTotal();
        }
    });
});
function updateTotal() {
    let total = 0;
    document.querySelectorAll('.entry-amount').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    document.getElementById('total-amount').textContent = total.toFixed(2) + ' DH';
}
</script>
@endpush
@endsection
