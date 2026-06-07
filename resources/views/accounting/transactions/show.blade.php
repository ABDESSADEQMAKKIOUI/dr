@extends('layouts.app')
@section('title', 'Transaction Details')
@php
$pageTitle = 'Transaction Details';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Accounting', 'url' => '#'], ['label' => 'Transactions', 'url' => route('accounting.transactions.index')], ['label' => 'Details', 'url' => '']];
@endphp
@section('content')
<div class="max-w-4xl mx-auto">
    <div class="card">
        <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">Transaction: {{ $transaction->reference }}</h3></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6"><div><dl class="space-y-2"><div><dt class="text-sm text-gray-500">Reference</dt><dd class="font-semibold">{{ $transaction->reference }}</dd></div><div><dt class="text-sm text-gray-500">Date</dt><dd>{{ $transaction->date->format('M d, Y') }}</dd></div><div><dt class="text-sm text-gray-500">Created By</dt><dd>{{ $transaction->user->full_name ?? 'System' }}</dd></div></dl></div><div><dl class="space-y-2"><div><dt class="text-sm text-gray-500">Description</dt><dd>{{ $transaction->description }}</dd></div></dl></div></div>
        <h4 class="font-semibold text-gray-800 mb-3">Journal Entries</h4>
        <div class="overflow-x-auto"><table class="table"><thead><tr><th>Account Code</th><th>Account Name</th><th>Debit</th><th>Credit</th></tr></thead>
        <tbody>@foreach($transaction->entries ?? [] as $entry)<tr><td class="font-mono">{{ $entry->account->code }}</td><td>{{ $entry->account->name }}</td><td class="font-semibold {{ $entry->type == 'debit' ? 'text-red-600' : '' }}">{{ $entry->type == 'debit' ? number_format($entry->amount, 2) . ' DH' : '-' }}</td><td class="font-semibold {{ $entry->type == 'credit' ? 'text-green-600' : '' }}">{{ $entry->type == 'credit' ? number_format($entry->amount, 2) . ' DH' : '-' }}</td></tr>@endforeach<tr class="bg-gray-50 font-semibold"><td colspan="2">Total</td><td class="text-red-600">{{ number_format($transaction->entries->where('type', 'debit')->sum('amount'), 2) }} DH</td><td class="text-green-600">{{ number_format($transaction->entries->where('type', 'credit')->sum('amount'), 2) }} DH</td></tr></tbody></table></div>
        <div class="mt-6 flex justify-end"><a href="{{ route('accounting.transactions.index') }}" class="btn btn-outline">Back to Transactions</a></div>
    </div>
</div>
@endsection
