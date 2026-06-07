@extends('layouts.app')
@section('title', 'Supplier Details')
@php
$pageTitle = 'Supplier Details';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Suppliers', 'url' => route('suppliers.index')], ['label' => $supplier->name, 'url' => '']];
@endphp
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="card text-center">
            <div class="w-24 h-24 bg-gradient-to-br from-green-500 to-blue-500 rounded-full flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4">
                {{ strtoupper(substr($supplier->name, 0, 2)) }}
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-1">{{ $supplier->name }}</h3>
            @if($supplier->company_name)<p class="text-gray-600 mb-2">{{ $supplier->company_name }}</p>@endif
            <span class="badge {{ $supplier->status ? 'badge-success' : 'badge-secondary' }}">{{ $supplier->status ? 'Active' : 'Inactive' }}</span>
            <div class="mt-6 space-y-2">
                <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-primary w-full">Edit Supplier</a>
                <a href="{{ route('reports.supplier-detail', $supplier->id) }}" class="btn btn-outline w-full">Supplier Report</a>
                <form method="POST" action="{{ route('suppliers.destroy', $supplier->id) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-full">Delete Supplier</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="lg:col-span-2 space-y-6">
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Contact Information</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><dt class="text-sm font-medium text-gray-500">Email</dt><dd class="mt-1 text-sm text-gray-900">{{ $supplier->email }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Phone</dt><dd class="mt-1 text-sm text-gray-900">{{ $supplier->phone }}</dd></div>
                @if($supplier->tax_number)<div><dt class="text-sm font-medium text-gray-500">Tax Number</dt><dd class="mt-1 text-sm text-gray-900">{{ $supplier->tax_number }}</dd></div>@endif
                @if($supplier->website)<div><dt class="text-sm font-medium text-gray-500">Website</dt><dd class="mt-1"><a href="{{ $supplier->website }}" target="_blank" class="text-sm text-blue-600 hover:underline">{{ $supplier->website }}</a></dd></div>@endif
                @if($supplier->address)<div class="md:col-span-2"><dt class="text-sm font-medium text-gray-500">Address</dt><dd class="mt-1 text-sm text-gray-900">{{ $supplier->address }}{{ $supplier->city ? ', ' . $supplier->city : '' }}{{ $supplier->country ? ', ' . $supplier->country : '' }}</dd></div>@endif
            </dl>
        </div>
        
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Purchase Statistics</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg"><p class="text-blue-600 text-sm mb-1">Total Purchases</p><h4 class="text-2xl font-bold text-blue-700">{{ $purchasesCount }}</h4></div>
                <div class="bg-green-50 p-4 rounded-lg"><p class="text-green-600 text-sm mb-1">Total Spent</p><h4 class="text-2xl font-bold text-green-700">{{ number_format($totalSpent, 2) }} DH</h4></div>
                <div class="bg-red-50 p-4 rounded-lg"><p class="text-red-600 text-sm mb-1">Balance Due</p><h4 class="text-2xl font-bold text-red-700">{{ number_format($balanceDue, 2) }} DH</h4></div>
            </div>
        </div>
        
        @if($supplier->notes)
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Notes</h3>
            <p class="text-gray-700">{{ $supplier->notes }}</p>
        </div>
        @endif
        
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Purchases</h3>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Reference</th><th>Date</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($recentPurchases ?? [] as $purchase)
                        @php
                        $pDate = $purchase->date ?? $purchase->purchase_date ?? $purchase->created_at;
                        $pStatus = match($purchase->status ?? '') {
                            'received' => 'badge-success',
                            'ordered'  => 'badge-info',
                            default    => 'badge-secondary',
                        };
                        @endphp
                        <tr>
                            <td class="font-semibold">{{ $purchase->reference ?? '#'.$purchase->id }}</td>
                            <td>{{ $pDate ? \Carbon\Carbon::parse($pDate)->format('d M Y') : '—' }}</td>
                            <td>{{ number_format($purchase->total_amount ?? 0, 2) }} DH</td>
                            <td><span class="badge {{ $pStatus }}">{{ ucfirst($purchase->status ?? 'N/A') }}</span></td>
                            <td><a href="{{ route('purchases.show', $purchase->id) }}" class="text-blue-600 hover:underline">{{ __('app.view') }}</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-gray-500 py-4">No purchases yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
