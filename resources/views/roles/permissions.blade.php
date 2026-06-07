@extends('layouts.app')

@section('title', 'Manage Permissions')

@php
$pageTitle = 'Permissions Management';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard')],
    ['label' => 'Roles', 'url' => route('roles.index')],
    ['label' => 'Permissions', 'url' => '']
];
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Roles List (Left Sidebar) -->
    <div class="lg:col-span-1">
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-800">Roles</h3>
            </div>
            <div class="space-y-1">
                @foreach($roles ?? [] as $roleItem)
                <a href="{{ route('roles.permissions', ['role' => $roleItem->id]) }}" 
                   class="block px-4 py-3 rounded-lg {{ isset($role) && $role->id == $roleItem->id ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 text-gray-700' }}">
                    <div class="font-medium">{{ $roleItem->name }}</div>
                    <div class="text-xs {{ isset($role) && $role->id == $roleItem->id ? 'text-blue-100' : 'text-gray-500' }}">
                        {{ $roleItem->permissions_count ?? 0 }} permissions
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Permissions Matrix (Main Content) -->
    <div class="lg:col-span-3">
        @if(isset($role))
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">{{ $role->name }} Permissions</h3>
                    <p class="text-sm text-gray-600 mt-1">Manage permissions for this role</p>
                </div>
                <span class="badge badge-primary">{{ $role->permissions->count() }} permissions</span>
            </div>
            
            <form method="POST" action="{{ route('roles.permissions', $role->id) }}">
                @csrf
                
                <!-- Permission Groups -->
                <div class="space-y-6">
                    @foreach($permissionGroups ?? [] as $group => $permissions)
                    <div>
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h4 class="font-semibold text-gray-800">{{ ucfirst($group) }}</h4>
                                <label class="flex items-center text-sm">
                                    <input type="checkbox" 
                                           class="group-select w-4 h-4 text-blue-600 border-gray-300 rounded"
                                           data-group="{{ $group }}"
                                           onchange="toggleGroup(this, '{{ $group }}')">
                                    <span class="ml-2 text-gray-600">Select All</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($permissions as $permission)
                                <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer group-{{ $group }}">
                                    <input type="checkbox" 
                                           name="permissions[]" 
                                           value="{{ $permission->id }}"
                                           {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 permission-{{ $group }}">
                                    <div class="ml-3 flex-1">
                                        <span class="text-sm font-medium text-gray-800">{{ $permission->name }}</span>
                                        @if($permission->description)
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $permission->description }}</p>
                                        @endif
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Action Buttons -->
                <div class="border-t border-gray-200 px-4 py-4 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div class="space-x-2">
                            <button type="button" 
                                    onclick="document.querySelectorAll('input[type=checkbox][name^=permissions]').forEach(cb => cb.checked = true)"
                                    class="btn btn-outline btn-sm">
                                Select All Permissions
                            </button>
                            <button type="button" 
                                    onclick="document.querySelectorAll('input[type=checkbox][name^=permissions]').forEach(cb => cb.checked = false)"
                                    class="btn btn-outline btn-sm">
                                Deselect All
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Permissions
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @else
        <div class="card">
            <div class="flex flex-col items-center justify-center py-12">
                <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Select a Role</h3>
                <p class="text-gray-600">Choose a role from the left to manage its permissions</p>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function toggleGroup(checkbox, group) {
    const checkboxes = document.querySelectorAll('.permission-' + group);
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

// Auto-check group select if all permissions are selected
document.addEventListener('DOMContentLoaded', function() {
    const groups = @json(array_keys($permissionGroups ?? []));
    groups.forEach(group => {
        const groupCheckboxes = document.querySelectorAll('.permission-' + group);
        const groupSelect = document.querySelector('[data-group="' + group + '"]');
        
        if (groupCheckboxes.length > 0 && groupSelect) {
            const allChecked = Array.from(groupCheckboxes).every(cb => cb.checked);
            groupSelect.checked = allChecked;
            
            // Add change listeners
            groupCheckboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    const allChecked = Array.from(groupCheckboxes).every(cb => cb.checked);
                    groupSelect.checked = allChecked;
                });
            });
        }
    });
});
</script>
@endpush
@endsection
