@extends('layouts.app')
@section('title', 'Edit Customer')
@php
$pageTitle = 'Edit Customer';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Customers', 'url' => route('customers.index')], ['label' => 'Edit', 'url' => '']];
@endphp
@section('content')
<div class="card max-w-3xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">Edit Customer: {{ $customer->name }}</h3></div>
    <form method="POST" action="{{ route('customers.update', $customer->id) }}" data-validate>
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group"><label class="form-label">Customer Name *</label><input type="text" name="name" value="{{ old('name', $customer->name) }}" class="form-control" required>@error('name')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">Company Name</label><input type="text" name="company_name" value="{{ old('company_name', $customer->company_name) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" value="{{ old('email', $customer->email) }}" class="form-control" required>@error('email')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">Phone *</label><input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-control" required>@error('phone')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">Tax Number</label><input type="text" name="tax_number" value="{{ old('tax_number', $customer->tax_number) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Credit Limit</label><input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" class="form-control"></div>
            <div class="form-group md:col-span-2"><label class="form-label">Address</label><textarea name="address" rows="2" class="form-control">{{ old('address', $customer->address) }}</textarea></div>
            <div class="form-group"><label class="form-label">City</label><input type="text" name="city" value="{{ old('city', $customer->city) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Country</label><input type="text" name="country" value="{{ old('country', $customer->country) }}" class="form-control"></div>
            <div class="form-group md:col-span-2"><label class="form-label">Notes</label><textarea name="notes" rows="3" class="form-control">{{ old('notes', $customer->notes) }}</textarea></div>
            <div class="form-group md:col-span-2"><label class="flex items-center"><input type="checkbox" name="status" value="1" {{ $customer->status ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded"><span class="ml-2 text-sm text-gray-700">Active</span></label></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('customers.index') }}" class="btn btn-outline">Cancel</a><button type="submit" class="btn btn-primary">Update Customer</button></div>
    </form>
</div>
@endsection
