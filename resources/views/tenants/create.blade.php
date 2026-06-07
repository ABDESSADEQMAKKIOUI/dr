@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="{{ route('tenants.index') }}" class="text-gray-600 hover:text-gray-800 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-800">Create New Tenant</h1>
        </div>

        <form action="{{ route('tenants.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-8">
            @csrf

            <!-- Basic Information -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Basic Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Company Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required 
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
                            <input type="text" name="domain" value="{{ old('domain') }}" required 
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('domain') border-red-500 @enderror">
                            <span class="px-4 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-lg text-gray-600">
                                .yourdomain.com
                            </span>
                        </div>
                        @error('domain')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-500 text-sm mt-1">This will be the unique subdomain for this tenant</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Contact Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Contact Phone
                        </label>
                        <input type="text" name="phone" value="{{ old('phone') }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror">
                        @error('phone')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Company Logo
                        </label>
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
                            <option value="trial" {{ old('plan') == 'trial' ? 'selected' : '' }}>Trial (14 days)</option>
                            <option value="basic" {{ old('plan') == 'basic' ? 'selected' : '' }}>Basic - $29/month</option>
                            <option value="professional" {{ old('plan') == 'professional' ? 'selected' : '' }}>Professional - $79/month</option>
                            <option value="enterprise" {{ old('plan') == 'enterprise' ? 'selected' : '' }}>Enterprise - $199/month</option>
                        </select>
                        @error('plan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Max Users <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="max_users" value="{{ old('max_users', 10) }}" required min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('max_users') border-red-500 @enderror">
                        @error('max_users')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Storage Limit (GB) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="storage_limit" value="{{ old('storage_limit', 10) }}" required min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('storage_limit') border-red-500 @enderror">
                        @error('storage_limit')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Database Configuration -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Database Configuration</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Database Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="database_name" value="{{ old('database_name') }}" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('database_name') border-red-500 @enderror">
                        @error('database_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-500 text-sm mt-1">Auto-generated if left empty</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Database Type
                        </label>
                        <select name="database_type" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('database_type') border-red-500 @enderror">
                            <option value="mysql" {{ old('database_type', 'mysql') == 'mysql' ? 'selected' : '' }}>MySQL</option>
                            <option value="postgresql" {{ old('database_type') == 'postgresql' ? 'selected' : '' }}>PostgreSQL</option>
                            <option value="sqlite" {{ old('database_type') == 'sqlite' ? 'selected' : '' }}>SQLite</option>
                        </select>
                        @error('database_type')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Additional Settings -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Additional Settings</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select name="status" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('status') border-red-500 @enderror">
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="trial" {{ old('status') == 'trial' ? 'selected' : '' }}>Trial</option>
                            <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Trial Ends At
                        </label>
                        <input type="date" name="trial_ends_at" value="{{ old('trial_ends_at') }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('trial_ends_at') border-red-500 @enderror">
                        @error('trial_ends_at')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="auto_provision" value="1" {{ old('auto_provision', true) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Auto-provision database and setup tenant environment</span>
                        </label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="send_welcome_email" value="1" {{ old('send_welcome_email', true) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Send welcome email to tenant</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t">
                <a href="{{ route('tenants.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-save mr-2"></i>Create Tenant
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-generate database name from domain
document.querySelector('input[name="domain"]').addEventListener('input', function(e) {
    const domain = e.target.value;
    const dbNameInput = document.querySelector('input[name="database_name"]');
    if (!dbNameInput.value || dbNameInput.dataset.autoGenerated) {
        dbNameInput.value = 'tenant_' + domain.toLowerCase().replace(/[^a-z0-9]/g, '_');
        dbNameInput.dataset.autoGenerated = 'true';
    }
});

// Update plan limits based on selected plan
document.getElementById('plan').addEventListener('change', function(e) {
    const plan = e.target.value;
    const maxUsersInput = document.querySelector('input[name="max_users"]');
    const storageLimitInput = document.querySelector('input[name="storage_limit"]');
    
    const planLimits = {
        'trial': { users: 5, storage: 1 },
        'basic': { users: 10, storage: 10 },
        'professional': { users: 50, storage: 50 },
        'enterprise': { users: 999, storage: 500 }
    };
    
    if (planLimits[plan]) {
        maxUsersInput.value = planLimits[plan].users;
        storageLimitInput.value = planLimits[plan].storage;
    }
});
</script>
@endsection
