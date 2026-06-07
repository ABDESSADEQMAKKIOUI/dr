@extends('layouts.app')
@section('title', 'Edit Expense')
@php
$pageTitle = 'Edit Expense';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Expenses', 'url' => route('expenses.index')], ['label' => 'Edit', 'url' => '']];
@endphp
@section('content')
<div class="card max-w-3xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">Edit Expense: {{ $expense->reference }}</h3></div>
    <form method="POST" action="{{ route('expenses.update', $expense->id) }}" enctype="multipart/form-data" data-validate>
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group"><label class="form-label">Category *</label><select name="category_id" class="form-control" required><option value="">Select Category</option>@foreach($categories ?? [] as $cat)<option value="{{ $cat->id }}" {{ $expense->expense_category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Expense Date *</label><input type="date" name="date" value="{{ old('date', $expense->date ? $expense->date->format('Y-m-d') : '') }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Amount *</label><input type="number" step="0.01" name="amount" value="{{ old('amount', $expense->amount) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Payment Method *</label><select name="payment_method" class="form-control" required><option value="cash" {{ (old('payment_method') == 'cash' || ($expense->paymentMethod && $expense->paymentMethod->code == 'cash')) ? 'selected' : '' }}>Cash</option><option value="transfer" {{ (old('payment_method') == 'transfer' || ($expense->paymentMethod && $expense->paymentMethod->code == 'transfer')) ? 'selected' : '' }}>Bank Transfer</option><option value="card" {{ (old('payment_method') == 'card' || ($expense->paymentMethod && $expense->paymentMethod->code == 'card')) ? 'selected' : '' }}>Credit Card</option><option value="check" {{ (old('payment_method') == 'check' || ($expense->paymentMethod && $expense->paymentMethod->code == 'check')) ? 'selected' : '' }}>Check</option></select></div>
            <div class="form-group"><label class="form-label">Vendor/Supplier</label><input type="text" name="vendor" value="{{ old('vendor', $expense->vendor) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Reference</label><input type="text" name="reference" value="{{ old('reference', $expense->reference) }}" class="form-control"></div>
            <div class="form-group md:col-span-2"><label class="form-label">Description *</label><textarea name="description" rows="3" class="form-control" required>{{ old('description', $expense->description) }}</textarea></div>
            @if($expense->attachment)<div class="form-group md:col-span-2"><label class="form-label">Current Attachment</label><div class="flex items-center space-x-2"><a href="{{ Storage::url($expense->attachment) }}" target="_blank" class="text-blue-600 hover:underline">View Attachment</a><label class="flex items-center ml-4"><input type="checkbox" name="remove_attachment" value="1" class="w-4 h-4"><span class="ml-2 text-sm text-red-600">Remove</span></label></div></div>@endif
            <div class="form-group md:col-span-2"><label class="form-label">New Attachment</label><input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png"><small class="text-gray-500">Upload receipt or invoice (PDF, JPG, PNG - Max 5MB)</small></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('expenses.index') }}" class="btn btn-outline">Cancel</a><button type="submit" class="btn btn-primary">Update Expense</button></div>
    </form>
</div>
@endsection
