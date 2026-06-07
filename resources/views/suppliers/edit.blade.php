@extends('layouts.app')
@section('title', 'Edit Supplier')
@php
$pageTitle = 'Edit Supplier';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Suppliers', 'url' => route('suppliers.index')], ['label' => 'Edit', 'url' => '']];
@endphp
@section('content')
<div class="card max-w-3xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">Edit Supplier: {{ $supplier->name }}</h3></div>
    <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}" data-validate>
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group"><label class="form-label">Supplier Name *</label><input type="text" name="name" value="{{ old('name', $supplier->name) }}" class="form-control" required>@error('name')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">Company Name</label><input type="text" name="company_name" value="{{ old('company_name', $supplier->company_name) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="form-control" required>@error('email')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">Phone *</label><input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="form-control" required>@error('phone')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">Tax Number</label><input type="text" name="tax_number" value="{{ old('tax_number', $supplier->tax_number) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Website</label><input type="url" name="website" value="{{ old('website', $supplier->website) }}" class="form-control"></div>
            <div class="form-group md:col-span-2"><label class="form-label">Address</label><textarea name="address" rows="2" class="form-control">{{ old('address', $supplier->address) }}</textarea></div>
            <div class="form-group"><label class="form-label">City</label><input type="text" name="city" value="{{ old('city', $supplier->city) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Country</label><input type="text" name="country" value="{{ old('country', $supplier->country) }}" class="form-control"></div>
            <div class="form-group md:col-span-2"><label class="form-label">Notes</label><textarea name="notes" rows="3" class="form-control">{{ old('notes', $supplier->notes) }}</textarea></div>
            <div class="form-group md:col-span-2"><label class="flex items-center"><input type="checkbox" name="status" value="1" {{ $supplier->status ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded"><span class="ml-2 text-sm text-gray-700">Active</span></label></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('suppliers.index') }}" class="btn btn-outline">Cancel</a><button type="submit" class="btn btn-primary">Update Supplier</button></div>
    </form>
</div>
@endsection
