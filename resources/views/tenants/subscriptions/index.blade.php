@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('tenants.index') }}" class="text-gray-600 hover:text-gray-800 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Subscription History</h1>
            <p class="text-gray-600 mt-1">{{ $tenant->name ?? 'Acme Corporation' }}</p>
        </div>
    </div>

    <!-- Current Subscription -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-8 mb-8 text-white">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold mb-2">Current Plan</h2>
                <p class="text-3xl font-bold mb-4">{{ ucfirst($tenant->plan ?? 'Professional') }}</p>
                <div class="space-y-2">
                    <p class="flex items-center">
                        <i class="fas fa-users mr-3"></i>
                        {{ $tenant->max_users ?? 50 }} Users
                    </p>
                    <p class="flex items-center">
                        <i class="fas fa-database mr-3"></i>
                        {{ $tenant->storage_limit ?? 50 }} GB Storage
                    </p>
                    <p class="flex items-center">
                        <i class="fas fa-calendar mr-3"></i>
                        Renews on {{ $tenant->subscription_expires_at ?? now()->addMonth()->format('M d, Y') }}
                    </p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm opacity-80 mb-2">Monthly Cost</p>
                <p class="text-4xl font-bold">${{ $tenant->monthly_cost ?? 79 }}</p>
                <button class="mt-4 bg-white text-blue-600 px-6 py-2 rounded-lg font-semibold hover:bg-gray-100 transition duration-200">
                    <i class="fas fa-arrow-up mr-2"></i>Upgrade Plan
                </button>
            </div>
        </div>
    </div>

    <!-- Subscription Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Spent</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">${{ number_format($totalSpent ?? 1580, 0) }}</h3>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Invoices</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $totalInvoices ?? 20 }}</h3>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Plan Changes</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $planChanges ?? 3 }}</h3>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-exchange-alt text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Customer Since</p>
                    <h3 class="text-lg font-bold text-gray-800 mt-1">{{ $tenant->created_at ?? now()->subMonths(20)->format('M Y') }}</h3>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <i class="fas fa-calendar-check text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Types</option>
                    <option value="subscription" {{ request('type') == 'subscription' ? 'selected' : '' }}>Subscription</option>
                    <option value="upgrade" {{ request('type') == 'upgrade' ? 'selected' : '' }}>Upgrade</option>
                    <option value="downgrade" {{ request('type') == 'downgrade' ? 'selected' : '' }}>Downgrade</option>
                    <option value="renewal" {{ request('type') == 'renewal' ? 'selected' : '' }}>Renewal</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200 w-full">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Subscription History Timeline -->
    <div class="bg-white rounded-lg shadow-md p-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Subscription Timeline</h2>
        
        <div class="space-y-6">
            @forelse($subscriptions ?? [] as $subscription)
            <div class="flex">
                <div class="flex flex-col items-center mr-4">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full 
                        {{ ($subscription->type ?? 'renewal') == 'upgrade' ? 'bg-green-100' : '' }}
                        {{ ($subscription->type ?? 'renewal') == 'downgrade' ? 'bg-orange-100' : '' }}
                        {{ ($subscription->type ?? 'renewal') == 'renewal' ? 'bg-blue-100' : '' }}
                        {{ ($subscription->type ?? 'renewal') == 'subscription' ? 'bg-purple-100' : '' }}">
                        <i class="fas 
                            {{ ($subscription->type ?? 'renewal') == 'upgrade' ? 'fa-arrow-up text-green-600' : '' }}
                            {{ ($subscription->type ?? 'renewal') == 'downgrade' ? 'fa-arrow-down text-orange-600' : '' }}
                            {{ ($subscription->type ?? 'renewal') == 'renewal' ? 'fa-sync text-blue-600' : '' }}
                            {{ ($subscription->type ?? 'renewal') == 'subscription' ? 'fa-star text-purple-600' : '' }}">
                        </i>
                    </div>
                    @if(!$loop->last)
                    <div class="w-0.5 h-full bg-gray-300 mt-2"></div>
                    @endif
                </div>
                <div class="flex-1 pb-8">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="font-semibold text-gray-800">
                                {{ ucfirst($subscription->type ?? 'renewal') }} - {{ ucfirst($subscription->plan ?? 'professional') }} Plan
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $subscription->description ?? 'Monthly subscription renewal' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-800">${{ $subscription->amount ?? 79 }}</p>
                            <p class="text-sm text-gray-500">{{ $subscription->date ?? now()->subDays($loop->index * 30)->format('M d, Y') }}</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">
                            <i class="fas fa-receipt mr-1"></i>Invoice #{{ $subscription->invoice_number ?? 'INV-'.str_pad($loop->index + 1, 5, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="px-3 py-1 
                            {{ ($subscription->status ?? 'paid') == 'paid' ? 'bg-green-100 text-green-700' : '' }}
                            {{ ($subscription->status ?? 'paid') == 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ ($subscription->status ?? 'paid') == 'failed' ? 'bg-red-100 text-red-700' : '' }}
                            text-xs rounded-full">
                            <i class="fas 
                                {{ ($subscription->status ?? 'paid') == 'paid' ? 'fa-check-circle' : '' }}
                                {{ ($subscription->status ?? 'paid') == 'pending' ? 'fa-clock' : '' }}
                                {{ ($subscription->status ?? 'paid') == 'failed' ? 'fa-times-circle' : '' }}
                                mr-1"></i>
                            {{ ucfirst($subscription->status ?? 'paid') }}
                        </span>
                        @if($subscription->payment_method ?? false)
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">
                            <i class="fas fa-credit-card mr-1"></i>{{ $subscription->payment_method }}
                        </span>
                        @endif
                    </div>

                    <div class="mt-3 flex space-x-3">
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-800">
                            <i class="fas fa-download mr-1"></i>Download Invoice
                        </a>
                        <a href="#" class="text-sm text-purple-600 hover:text-purple-800">
                            <i class="fas fa-eye mr-1"></i>View Details
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-12">
                <i class="fas fa-history text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500">No subscription history found</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if(isset($subscriptions) && method_exists($subscriptions, 'links'))
    <div class="mt-6">
        {{ $subscriptions->links() }}
    </div>
    @endif

    <!-- Upcoming Renewal -->
    <div class="bg-blue-50 border-l-4 border-blue-400 p-6 mt-8 rounded-r-lg">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-400 text-xl mr-4 mt-1"></i>
            <div class="flex-1">
                <h3 class="font-semibold text-blue-900 mb-1">Next Renewal</h3>
                <p class="text-blue-800 text-sm">
                    Your subscription will automatically renew on <strong>{{ $tenant->subscription_expires_at ?? now()->addMonth()->format('F d, Y') }}</strong> 
                    for <strong>${{ $tenant->monthly_cost ?? 79 }}</strong>. 
                    The payment method on file will be charged.
                </p>
            </div>
            <button class="text-blue-600 hover:text-blue-800 text-sm font-semibold whitespace-nowrap">
                Update Payment Method
            </button>
        </div>
    </div>
</div>
@endsection
