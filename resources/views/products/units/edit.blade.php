@extends('layouts.app')
@section('title', 'Edit Unit')
@php
$pageTitle = 'Edit Unit';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Products', 'url' => route('products.index')], ['label' => 'Units', 'url' => route('products.units.index')], ['label' => 'Edit', 'url' => '']];
@endphp
@section('content')
<div class="card max-w-2xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">Edit Unit: {{ $unit->name }}</h3></div>
    <form method="POST" action="{{ route('products.units.update', $unit->id) }}" data-validate>
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div class="form-group"><label class="form-label">Unit Name *</label><input type="text" name="name" value="{{ old('name', $unit->name) }}" class="form-control" required>@error('name')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">Short Name *</label><input type="text" name="short_name" value="{{ old('short_name', $unit->short_name) }}" class="form-control" required>@error('short_name')<span class="form-error">{{ $message }}</span>@enderror<span class="form-help">Abbreviation for this unit</span></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('products.units.index') }}" class="btn btn-outline">Cancel</a><button type="submit" class="btn btn-primary">Update Unit</button></div>
    </form>
</div>
@endsection
