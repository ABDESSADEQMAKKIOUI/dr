@extends('layouts.app')
@section('title', 'Create Expense')
@php
$pageTitle = 'Create Expense';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Expenses', 'url' => route('expenses.index')], ['label' => 'Create', 'url' => '']];
@endphp
@section('content')
<div class="card max-w-3xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">New Expense</h3></div>
    <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data" data-validate>
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group"><label class="form-label">Category *</label><select name="category_id" class="form-control" required><option value="">Select Category</option>@foreach($categories ?? [] as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select>@error('category_id')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">Expense Date *</label><input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Amount *</label><input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="form-control" required>@error('amount')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">Payment Method *</label><select name="payment_method" class="form-control" required><option value="cash">Cash</option><option value="transfer">Bank Transfer</option><option value="card">Credit Card</option><option value="check">Check</option></select></div>
            <div class="form-group"><label class="form-label">Vendor/Supplier</label><input type="text" name="vendor" value="{{ old('vendor') }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Reference</label><input type="text" name="reference" value="{{ old('reference') }}" class="form-control" placeholder="Invoice/Receipt number"></div>
            <div class="form-group md:col-span-2"><label class="form-label">Description *</label><textarea name="description" rows="3" class="form-control" required>{{ old('description') }}</textarea>@error('description')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group md:col-span-2"><label class="form-label">Attachment</label><input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png"><small class="text-gray-500">Upload receipt or invoice (PDF, JPG, PNG - Max 5MB)</small></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('expenses.index') }}" class="btn btn-outline">Cancel</a><button type="submit" class="btn btn-primary">Create Expense</button></div>
    </form>
</div>
@endsection
