@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="{{ route('tenants.index') }}" class="text-gray-600 hover:text-gray-800 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-800">Edit Tenant: {{ $tenant->name ?? 'Tenant Name' }}</h1>
        </div>

        <form action="{{ route('tenants.update', $tenant->id ?? 1) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-8">
            @csrf
            @method('PUT')

            <!-- Tenant Status Alert -->
            @if(($tenant->status ?? 'active') == 'suspended')
            <div class="mb-6 bg-orange-50 border-l-4 border-orange-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-orange-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-orange-700">
                            This tenant is currently suspended. Users cannot access their account.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Basic Information -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Basic Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Company Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $tenant->name ?? 'Acme Corporation') }}" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Domain/Subdomain <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center">
                            <input type="text" name="domain" value="{{ old('domain', $tenant->domain ?? 'acme') }}" required 
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('domain') border-red-500 @enderror">
                            <span class="px-4 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-lg text-gray-600">
                                .yourdomain.com
                            </span>
                        </div>
                        @error('domain')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-yellow-600 text-sm mt-1">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            Changing the domain may require DNS updates
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Contact Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $tenant->email ?? 'contact@acme.com') }}" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Contact Phone
                        </label>
                        <input type="text" name="phone" value="{{ old('phone', $tenant->phone ?? '+1 234 567 8900') }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror">
                        @error('phone')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Company Logo
                        </label>
                        @if($tenant->logo ?? false)
                        <div class="mb-3">
                            <img src="{{ $tenant->logo }}" alt="Current Logo" class="h-16 w-16 object-cover rounded">
                            <label class="flex items-center mt-2">
                                <input type="checkbox" name="remove_logo" value="1" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-red-600">Remove current logo</span>
                            </label>
                        </div>
                        @endif
                        <input type="file" name="logo" accept="image/*" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('logo') border-red-500 @enderror">
                        @error('logo')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Subscription Plan -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Subscription Plan</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Plan Type <span class="text-red-500">*</span>
                        </label>
                        <select name="plan" id="plan" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('plan') border-red-500 @enderror">
                            <option value="">Select Plan</option>
                            <option value="trial" {{ old('plan', $tenant->plan ?? 'professional') == 'trial' ? 'selected' : '' }}>Trial (14 days)</option>
                            <option value="basic" {{ old('plan', $tenant->plan ?? 'professional') == 'basic' ? 'selected' : '' }}>Basic - $29/month</option>
                            <option value="professional" {{ old('plan', $tenant->plan ?? 'professional') == 'professional' ? 'selected' : '' }}>Professional - $79/month</option>
                            <option value="enterprise" {{ old('plan', $tenant->plan ?? 'professional') == 'enterprise' ? 'selected' : '' }}>Enterprise - $199/month</option>
                        </select>
                        @error('plan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Max Users <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="max_users" value="{{ old('max_users', $tenant->max_users ?? 50) }}" required min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('max_users') border-red-500 @enderror">
                        @error('max_users')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-500 text-sm mt-1">Current: {{ $tenant->users_count ?? 12 }} users</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Storage Limit (GB) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="storage_limit" value="{{ old('storage_limit', $tenant->storage_limit ?? 50) }}" required min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('storage_limit') border-red-500 @enderror">
                        @error('storage_limit')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-500 text-sm mt-1">Used: {{ $tenant->storage_used ?? '2.5' }} GB</p>
                    </div>
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Usage Statistics</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Active Users</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $tenant->users_count ?? 12 }} / {{ $tenant->max_users ?? 50 }}</p>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ (($tenant->users_count ?? 12) / ($tenant->max_users ?? 50)) * 100 }}%"></div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Storage Used</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $tenant->storage_used ?? '2.5' }} / {{ $tenant->storage_limit ?? 50 }} GB</p>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $tenant->storage_percentage ?? 5 }}%"></div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Monthly Cost</p>
                        <p class="text-2xl font-bold text-gray-800">${{ $tenant->monthly_cost ?? 79 }}</p>
                        <p class="text-sm text-green-600 mt-1">
                            <i class="fas fa-check-circle mr-1"></i>Active subscription
                        </p>
                    </div>
                </div>
            </div>

            <!-- Status & Settings -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Status & Settings</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select name="status" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('status') border-red-500 @enderror">
                            <option value="active" {{ old('status', $tenant->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="trial" {{ old('status', $tenant->status ?? 'active') == 'trial' ? 'selected' : '' }}>Trial</option>
                            <option value="suspended" {{ old('status', $tenant->status ?? 'active') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="inactive" {{ old('status', $tenant->status ?? 'active') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Subscription Expires
                        </label>
                        <input type="date" name="subscription_expires_at" value="{{ old('subscription_expires_at', $tenant->subscription_expires_at ?? now()->addMonth()->format('Y-m-d')) }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('subscription_expires_at') border-red-500 @enderror">
                        @error('subscription_expires_at')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Database Name
                        </label>
                        <input type="text" value="{{ $tenant->database_name ?? 'tenant_acme' }}" disabled
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
                        <p class="text-gray-500 text-sm mt-1">Database name cannot be changed after creation</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Additional Actions
                        </label>
                        <div class="space-y-2">
                            <button type="button" onclick="clearCache()" class="text-blue-600 hover:text-blue-800 text-sm">
                                <i class="fas fa-sync-alt mr-2"></i>Clear Tenant Cache
                            </button>
                            <span class="mx-3 text-gray-300">|</span>
                            <a href="{{ route('tenants.subscriptions', $tenant->id ?? 1) }}" class="text-purple-600 hover:text-purple-800 text-sm">
                                <i class="fas fa-credit-card mr-2"></i>View Subscription History
                            </a>
                            <span class="mx-3 text-gray-300">|</span>
                            <button type="button" onclick="resetDatabase()" class="text-orange-600 hover:text-orange-800 text-sm">
                                <i class="fas fa-database mr-2"></i>Reset Database
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-between items-center pt-6 border-t">
                <button type="button" onclick="deleteTenant()" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-trash mr-2"></i>Delete Tenant
                </button>
                <div class="flex space-x-4">
                    <a href="{{ route('tenants.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-save mr-2"></i>Update Tenant
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function clearCache() {
    if (confirm('Are you sure you want to clear the cache for this tenant?')) {
        // Implement cache clearing logic
        alert('Cache cleared successfully');
    }
}

function resetDatabase() {
    if (confirm('WARNING: This will reset the database and delete all tenant data. Are you sure?')) {
        if (confirm('This action cannot be undone. Type YES to confirm.')) {
            // Implement database reset logic
            alert('Database reset initiated');
        }
    }
}

function deleteTenant() {
    if (confirm('WARNING: This will permanently delete the tenant and all associated data. Are you sure?')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>

<form id="deleteForm" action="{{ route('tenants.destroy', $tenant->id ?? 1) }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endsection
