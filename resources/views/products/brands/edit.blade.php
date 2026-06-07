@extends('layouts.app')
@section('title', 'Edit Brand')
@php
$pageTitle = 'Edit Brand';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Products', 'url' => route('products.index')], ['label' => 'Brands', 'url' => route('products.brands.index')], ['label' => 'Edit', 'url' => '']];
@endphp
@section('content')
<div class="card max-w-2xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">Edit Brand: {{ $brand->name }}</h3></div>
    <form method="POST" action="{{ route('products.brands.update', $brand->id) }}" data-validate>
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div class="form-group"><label class="form-label">Brand Name *</label><input type="text" name="name" value="{{ old('name', $brand->name) }}" class="form-control" required>@error('name')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" rows="3" class="form-control">{{ old('description', $brand->description) }}</textarea></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('products.brands.index') }}" class="btn btn-outline">Cancel</a><button type="submit" class="btn btn-primary">Update Brand</button></div>
    </form>
</div>
@endsection
