@extends('layouts.app')

@section('title', 'Create Role')

@php
$pageTitle = 'Create Role';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard')],
    ['label' => 'Roles', 'url' => route('roles.index')],
    ['label' => 'Create', 'url' => '']
];
@endphp

@section('content')
<div class="max-w-4xl">
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-800">Create New Role</h3>
        </div>
        
        <form method="POST" action="{{ route('roles.store') }}">
            @csrf
            
            <div class="p-6 space-y-6">
                <!-- Role Name -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="name" class="form-label">Role Name *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" 
                               class="form-control @error('name') is-invalid @enderror" 
                               required placeholder="e.g., manager, cashier, accountant">
                        @error('name')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" id="description" name="description" value="{{ old('description') }}" 
                               class="form-control @error('description') is-invalid @enderror"
                               placeholder="Brief description of this role">
                        @error('description')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
                
                <!-- Permissions Section -->
                <div class="border-t pt-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Permissions</h4>
                    <p class="text-sm text-gray-500 mb-4">Select the permissions for this role. Users with this role will have access to the selected features.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($permissions as $group => $groupPermissions)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h5 class="font-semibold text-gray-700 capitalize">{{ str_replace('_', ' ', $group) }}</h5>
                                <label class="flex items-center text-xs text-blue-600 cursor-pointer">
                                    <input type="checkbox" class="mr-1 select-all-checkbox" data-group="{{ $group }}">
                                    Select All
                                </label>
                            </div>
                            <div class="space-y-2">
                                @foreach($groupPermissions as $permission)
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="permissions[]" 
                                           value="{{ $permission->id }}" 
                                           data-group="{{ $group }}"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 permission-checkbox"
                                           {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ $permission->description }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="card-footer flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    <span class="font-medium" id="selected-count">0</span> permissions selected
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('roles.index') }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Role</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all functionality for each group
    document.querySelectorAll('.select-all-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const group = this.dataset.group;
            const isChecked = this.checked;
            document.querySelectorAll(`input[data-group="${group}"].permission-checkbox`).forEach(function(permCheckbox) {
                permCheckbox.checked = isChecked;
            });
            updateCount();
        });
    });
    
    // Update count on any checkbox change
    document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', updateCount);
    });
    
    function updateCount() {
        const count = document.querySelectorAll('.permission-checkbox:checked').length;
        document.getElementById('selected-count').textContent = count;
    }
});
</script>
@endpush
