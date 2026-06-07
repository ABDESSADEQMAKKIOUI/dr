@extends('layouts.app')
@section('title', 'General Ledger')
@php
$pageTitle = 'General Ledger';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Accounting', 'url' => '#'], ['label' => 'Journals', 'url' => '']];
@endphp
@section('content')
<div class="card mb-6">
    <div class="card-header flex justify-between items-center"><h3 class="text-lg font-semibold text-gray-800">General Ledger</h3><button onclick="window.print()" class="btn btn-outline btn-sm"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>Export</button></div>
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div><label class="form-label">Account</label><select name="account_id" class="form-control"><option value="">All Accounts</option>@foreach($accounts ?? [] as $acc)<option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->code }} - {{ $acc->name }}</option>@endforeach</select></div>
        <div><label class="form-label">From Date</label><input type="date" name="from_date" value="{{ request('from_date', date('Y-m-01')) }}" class="form-control"></div>
        <div><label class="form-label">To Date</label><input type="date" name="to_date" value="{{ request('to_date', date('Y-m-d')) }}" class="form-control"></div>
        <div class="flex items-end"><button type="submit" class="btn btn-primary w-full">Generate</button></div>
    </form>
</div>

<div class="card">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Journal Entries</h3>
    <div class="overflow-x-auto"><table class="table"><thead><tr><th>Date</th><th>Reference</th><th>Account</th><th>Description</th><th>Debit</th><th>Credit</th><th>Balance</th></tr></thead>
    <tbody>@php $runningBalance = 0; @endphp@forelse($entries ?? [] as $entry)@php if($entry->type == 'debit') { $runningBalance += $entry->amount; } else { $runningBalance -= $entry->amount; } @endphp<tr><td>{{ $entry->date->format('M d, Y') }}</td><td class="font-mono text-sm">{{ $entry->transaction->reference }}</td><td class="text-sm">{{ $entry->account->code }} - {{ $entry->account->name }}</td><td>{{ $entry->transaction->description }}</td><td class="font-semibold {{ $entry->type == 'debit' ? 'text-red-600' : '' }}">{{ $entry->type == 'debit' ? number_format($entry->amount, 2) . ' DH' : '-' }}</td><td class="font-semibold {{ $entry->type == 'credit' ? 'text-green-600' : '' }}">{{ $entry->type == 'credit' ? number_format($entry->amount, 2) . ' DH' : '-' }}</td><td class="font-semibold">{{ number_format($runningBalance, 2) }} DH</td></tr>@empty<tr><td colspan="7" class="text-center text-gray-500 py-8">No journal entries found</td></tr>@endforelse @if(count($entries ?? []) > 0)<tr class="bg-gray-50 font-semibold"><td colspan="4">Total</td><td class="text-red-600">{{ number_format($entries->where('type', 'debit')->sum('amount'), 2) }} DH</td><td class="text-green-600">{{ number_format($entries->where('type', 'credit')->sum('amount'), 2) }} DH</td><td>{{ number_format($runningBalance, 2) }} DH</td></tr>@endif</tbody></table></div>
</div>
@endsection
