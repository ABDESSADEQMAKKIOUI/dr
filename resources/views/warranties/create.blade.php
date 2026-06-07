@extends('layouts.app')
@section('title', 'Add Warranty')
@php $pageTitle = 'Add Warranty'; $breadcrumbs = [['label'=>'Dashboard','url'=>route('dashboard')],['label'=>'Warranties','url'=>route('warranties.index')],['label'=>'Create']]; @endphp
@section('content')
<div class="card max-w-2xl mx-auto">
    <div class="card-header"><h3 class="text-lg font-semibold">New Warranty</h3></div>
    <form method="POST" action="{{ route('warranties.store') }}" class="p-6 space-y-4">
        @csrf
        <div>
            <label class="form-label">Product <span class="text-red-500">*</span></label>
            <select name="product_id" class="form-select" required>
                <option value="">Select product...</option>
                @foreach($products as $p)
                <option value="{{ $p->id }}" @selected(old('product_id')==$p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Customer</label>
            <select name="customer_id" class="form-select">
                <option value="">Select customer...</option>
                @foreach($customers as $c)
                <option value="{{ $c->id }}" @selected(old('customer_id')==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Serial Number</label>
            <input type="text" name="serial_number" value="{{ old('serial_number') }}" class="form-input">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Start Date <span class="text-red-500">*</span></label>
                <input type="date" name="start_date" value="{{ old('start_date', today()->toDateString()) }}" class="form-input" required>
            </div>
            <div>
                <label class="form-label">End Date <span class="text-red-500">*</span></label>
                <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-input" required>
            </div>
        </div>
        <div>
            <label class="form-label">Notes</label>
            <textarea name="notes" rows="3" class="form-input">{{ old('notes') }}</textarea>
        </div>
        @if($errors->any())
        <div class="text-red-600 text-sm">{{ $errors->first() }}</div>
        @endif
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Save Warranty</button>
            <a href="{{ route('warranties.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
