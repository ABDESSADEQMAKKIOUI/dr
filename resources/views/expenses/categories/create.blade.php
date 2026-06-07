@extends('layouts.app')
@section('title', 'Create Expense Category')
@php
$pageTitle = 'Create Expense Category';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Expenses', 'url' => route('expenses.index')], ['label' => 'Categories', 'url' => route('expenses.categories.index')], ['label' => 'Create', 'url' => '']];
@endphp
@section('content')
<div class="card max-w-2xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">New Expense Category</h3></div>
    <form method="POST" action="{{ route('expenses.categories.store') }}" data-validate>
        @csrf
        <div class="form-group"><label class="form-label">Category Name *</label><input type="text" name="name" value="{{ old('name') }}" class="form-control" required>@error('name')<span class="form-error">{{ $message }}</span>@enderror</div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea></div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('expenses.categories.index') }}" class="btn btn-outline">Cancel</a><button type="submit" class="btn btn-primary">Create Category</button></div>
    </form>
</div>
@endsection
