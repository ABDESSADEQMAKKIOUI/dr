@extends('layouts.app')

@section('title', 'User Details')

@php
$pageTitle = 'User Details';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard')],
    ['label' => 'Users', 'url' => route('users.index')],
    ['label' => 'Details', 'url' => '']
];
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="card text-center">
            <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-1">{{ $user->name }}</h3>
            <p class="text-gray-600 mb-2">{{ $user->email }}</p>
            <span class="badge {{ $user->status ? 'badge-success' : 'badge-secondary' }}">
                {{ $user->status ? 'Active' : 'Inactive' }}
            </span>
            <div class="mt-6 space-y-2">
                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary w-full">Edit User</a>
                <form method="POST" action="{{ route('users.destroy', $user->id) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-full">Delete User</button>
                </form>
            </div>
        </div>
    </div>
    <div class="lg:col-span-2 space-y-6">
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">User Information</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Username</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->username }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Role</dt>
                    <dd class="mt-1"><span class="badge badge-primary">{{ $user->role->name ?? 'N/A' }}</span></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('M d, Y h:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Last Login</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : 'Never' }}</dd>
                </div>
            </dl>
        </div>
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Activity History</h3>
            <div class="space-y-3">
                @forelse($activities ?? [] as $activity)
                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm text-gray-900">{{ $activity->description }}</p>
                        <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-500 py-4">No activity recorded</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
