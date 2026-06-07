@extends('layouts.app')
@section('title', 'Customer Details')
@php
$pageTitle = 'Customer Details';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Customers', 'url' => route('customers.index')], ['label' => $customer->name, 'url' => '']];
@endphp
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="card text-center">
            <div class="w-24 h-24 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4">
                {{ strtoupper(substr($customer->name, 0, 2)) }}
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-1">{{ $customer->name }}</h3>
            @if($customer->company_name)<p class="text-gray-600 mb-2">{{ $customer->company_name }}</p>@endif
            <span class="badge {{ $customer->status ? 'badge-success' : 'badge-secondary' }}">{{ $customer->status ? 'Active' : 'Inactive' }}</span>
            <div class="mt-6 space-y-2">
                <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary w-full">Edit Customer</a>
                <a href="{{ route('reports.customer-detail', $customer->id) }}" class="btn btn-outline w-full">Customer Report</a>
                <form method="POST" action="{{ route('customers.destroy', $customer->id) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-full">Delete Customer</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="lg:col-span-2 space-y-6">
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Contact Information</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><dt class="text-sm font-medium text-gray-500">Email</dt><dd class="mt-1 text-sm text-gray-900">{{ $customer->email }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Phone</dt><dd class="mt-1 text-sm text-gray-900">{{ $customer->phone }}</dd></div>
                @if($customer->tax_number)<div><dt class="text-sm font-medium text-gray-500">Tax Number</dt><dd class="mt-1 text-sm text-gray-900">{{ $customer->tax_number }}</dd></div>@endif
                @if($customer->credit_limit)<div><dt class="text-sm font-medium text-gray-500">Credit Limit</dt><dd class="mt-1 text-sm text-gray-900">{{ number_format($customer->credit_limit, 2) }} DH</dd></div>@endif
                @if($customer->address)<div class="md:col-span-2"><dt class="text-sm font-medium text-gray-500">Address</dt><dd class="mt-1 text-sm text-gray-900">{{ $customer->address }} @if($customer->city), {{ $customer->city }} @endif @if($customer->country), {{ $customer->country }} @endif</dd></div> @endif
            </dl>
        </div>
        
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Sales Statistics</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg"><p class="text-blue-600 text-sm mb-1">Total Sales</p><h4 class="text-2xl font-bold text-blue-700">{{ $salesCount }}</h4></div>
                <div class="bg-green-50 p-4 rounded-lg"><p class="text-green-600 text-sm mb-1">Total Revenue</p><h4 class="text-2xl font-bold text-green-700">{{ number_format($totalRevenue, 2) }} DH</h4></div>
                <div class="bg-red-50 p-4 rounded-lg"><p class="text-red-600 text-sm mb-1">Balance Due</p><h4 class="text-2xl font-bold text-red-700">{{ number_format($balanceDue, 2) }} DH</h4></div>
            </div>
        </div>
        
        @if($customer->notes)
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Notes</h3>
            <p class="text-gray-700">{{ $customer->notes }}</p>
        </div>
        @endif
        
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Sales</h3>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Reference</th><th>Date</th><th>Total</th><th>Paid</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr>
                            <td class="font-semibold">{{ $sale->reference }}</td>
                            <td>{{ \Carbon\Carbon::parse($sale->date ?? $sale->created_at)->format('d M Y') }}</td>
                            <td>{{ number_format($sale->total_amount, 2) }} DH</td>
                            <td>{{ number_format($sale->paid_amount, 2) }} DH</td>
                            <td>
                                @if($sale->status == 'confirmed')<span class="badge badge-success">Confirmed</span>
                                @elseif($sale->status == 'draft')<span class="badge badge-secondary">Draft</span>
                                @elseif($sale->status == 'delivered')<span class="badge badge-info">Delivered</span>
                                @else<span class="badge badge-secondary">{{ $sale->status }}</span>@endif
                            </td>
                            <td><a href="{{ route('sales.show', $sale->id) }}" class="text-blue-600 hover:underline">View</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-gray-500 py-4">No sales yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
