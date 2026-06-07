@extends('layouts.app')
@section('title', 'Edit Account')
@php
$pageTitle = 'Edit Account';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Accounting', 'url' => '#'], ['label' => 'Accounts', 'url' => route('accounting.accounts.index')], ['label' => 'Edit', 'url' => '']];
$currentType = $account->accountType ? strtolower($account->accountType->name) : '';
@endphp
@section('content')
<div class="card max-w-3xl">
    <div class="card-header">
        <h3 class="text-lg font-semibold text-gray-800">Edit Account: {{ $account->name }}</h3>
    </div>
    <form method="POST" action="{{ route('accounting.accounts.update', $account->id) }}" data-validate>
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Account Code *</label>
                <input type="text" name="code" value="{{ old('code', $account->code) }}" class="form-control" required>
                @error('code')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Account Name *</label>
                <input type="text" name="name" value="{{ old('name', $account->name) }}" class="form-control" required>
                @error('name')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Account Type *</label>
                <select name="type" class="form-control" required>
                    <option value="">Select Type</option>
                    <option value="asset" {{ $currentType == 'asset' ? 'selected' : '' }}>Asset</option>
                    <option value="liability" {{ $currentType == 'liability' ? 'selected' : '' }}>Liability</option>
                    <option value="equity" {{ $currentType == 'equity' ? 'selected' : '' }}>Equity</option>
                    <option value="revenue" {{ $currentType == 'revenue' ? 'selected' : '' }}>Revenue</option>
                    <option value="expense" {{ $currentType == 'expense' ? 'selected' : '' }}>Expense</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Parent Account</label>
                <select name="parent_id" class="form-control">
                    <option value="">None (Root Account)</option>
                    @foreach($parentAccounts ?? [] as $acc)
                        @if($acc->id != $account->id)
                            <option value="{{ $acc->id }}" {{ $account->parent_id == $acc->id ? 'selected' : '' }}>
                                {{ $acc->code }} - {{ $acc->name }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description', $account->description) }}</textarea>
            </div>
            <div class="form-group md:col-span-2">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ $account->is_active ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
            </div>
        </div>
        <div class="flex justify-end space-x-2 mt-6">
            <a href="{{ route('accounting.accounts.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Account</button>
        </div>
    </form>
</div>
@endsection
