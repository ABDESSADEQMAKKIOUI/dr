@extends('layouts.app')

@section('title', __('app.users'))

@php
$pageTitle = __('app.users');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.users'), 'url' => route('users.index')]
];
@endphp

@section('content')
<div class="card">
    <div class="card-header flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">{{ __('app.all_users') }}</h3>
        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            {{ __('app.add_user') }}
        </a>
    </div>
    
    <!-- Filters -->
    <div class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <input type="text" id="search" placeholder="{{ __('app.search') }} {{ __('app.users') }}..." class="form-control">
        </div>
        <div>
            <select class="form-control">
                <option value="">{{ __('app.all') }} {{ __('app.roles_permissions') }}</option>
                <option value="admin">Admin</option>
                <option value="manager">Manager</option>
                <option value="staff">Staff</option>
            </select>
        </div>
        <div>
            <select class="form-control">
                <option value="">{{ __('app.all_status') }}</option>
                <option value="active">{{ __('app.active') }}</option>
                <option value="inactive">{{ __('app.inactive') }}</option>
            </select>
        </div>
        <div>
            <button class="btn btn-outline w-full">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                {{ __('app.filter') }}
            </button>
        </div>
    </div>
    
    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('app.name') }}</th>
                    <th>{{ __('app.email') }}</th>
                    <th>{{ __('app.role') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th>{{ __('app.last_login') }}</th>
                    <th>{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users ?? [] as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold mr-2">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <span class="font-medium">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge badge-primary">{{ $user->role }}</span>
                    </td>
                    <td>
                        @if($user->status === 'active')
                            <span class="badge badge-success">{{ __('app.active') }}</span>
                        @else
                            <span class="badge badge-secondary">{{ __('app.inactive') }}</span>
                        @endif
                    </td>
                    <td>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : __('app.no_data') }}</td>
                    <td>
                        <div class="flex space-x-2">
                            <a href="{{ route('users.show', $user->id) }}" class="text-blue-600 hover:text-blue-800" title="{{ __('app.view') }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            <a href="{{ route('users.edit', $user->id) }}" class="text-green-600 hover:text-green-800" title="{{ __('app.edit') }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('users.destroy', $user->id) }}" class="inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" title="{{ __('app.delete') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-gray-500 py-8">
                        {{ __('app.no_results') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if(isset($users) && $users->hasPages())
    <div class="mt-4">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
