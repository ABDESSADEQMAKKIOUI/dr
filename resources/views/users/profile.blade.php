@extends('layouts.app')

@section('title', 'My Profile')

@php
$pageTitle = 'My Profile';
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="card text-center">
            <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-1">{{ auth()->user()->name }}</h3>
            <p class="text-gray-600 mb-2">{{ auth()->user()->email }}</p>
            <span class="badge badge-primary">{{ auth()->user()->role->name ?? 'User' }}</span>
        </div>
    </div>
    <div class="lg:col-span-2">
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Update Profile</h3>
            <form method="POST" action="{{ route('users.profile.update') }}" data-validate>
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="current_password" class="form-label">Current Password (to save changes)</label>
                        <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror">
                        @error('current_password')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <h4 class="font-semibold text-gray-800 mb-3">Change Password</h4>
                        <div class="space-y-3">
                            <div class="form-group">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                @error('password')<span class="form-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end mt-6">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
