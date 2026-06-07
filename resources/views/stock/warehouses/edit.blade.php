@extends('layouts.app')
@section('title', __('app.edit_warehouse'))
@php
$pageTitle = __('app.edit_warehouse');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.stock'), 'url' => '#'], ['label' => __('app.warehouses'), 'url' => route('stock.warehouses.index')], ['label' => __('app.edit'), 'url' => '']];
@endphp
@section('content')
<div class="card max-w-3xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.edit_warehouse') }}: {{ $warehouse->name }}</h3></div>
    <form method="POST" action="{{ route('stock.warehouses.update', $warehouse->id) }}" data-validate>
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group"><label class="form-label">{{ __('app.name') }} *</label><input type="text" name="name" value="{{ old('name', $warehouse->name) }}" class="form-control" required>@error('name')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">{{ __('app.manager') }}</label><select name="manager_id" class="form-control"><option value="">{{ __('app.select') }}</option>@foreach($users ?? [] as $user)<option value="{{ $user->id }}" {{ $warehouse->manager_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">{{ __('app.phone') }}</label><input type="text" name="phone" value="{{ old('phone', $warehouse->phone) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">{{ __('app.email') }}</label><input type="email" name="email" value="{{ old('email', $warehouse->email) }}" class="form-control"></div>
            <div class="form-group md:col-span-2"><label class="form-label">{{ __('app.address') }} *</label><textarea name="location" rows="2" class="form-control" required>{{ old('location', $warehouse->location) }}</textarea></div>
            <div class="form-group md:col-span-2"><label class="form-label">{{ __('app.description') }}</label><textarea name="description" rows="3" class="form-control">{{ old('description', $warehouse->description) }}</textarea></div>
            <div class="form-group"><label class="flex items-center"><input type="checkbox" name="status" value="1" {{ $warehouse->status ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded"><span class="ml-2 text-sm text-gray-700">{{ __('app.active') }}</span></label></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('stock.warehouses.index') }}" class="btn btn-outline">{{ __('app.cancel') }}</a><button type="submit" class="btn btn-primary">{{ __('app.save') }}</button></div>
    </form>
</div>
@endsection
