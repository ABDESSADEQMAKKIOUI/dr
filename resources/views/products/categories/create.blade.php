@extends('layouts.app')
@section('title', __('app.add_category'))
@php
$pageTitle = __('app.add_category');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.products'), 'url' => route('products.index')], ['label' => __('app.categories'), 'url' => route('products.categories.index')], ['label' => __('app.create'), 'url' => '']];
@endphp
@section('content')
<div class="card max-w-2xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.add_category') }}</h3></div>
    <form method="POST" action="{{ route('products.categories.store') }}" data-validate>
        @csrf
        <div class="space-y-4">
            <div class="form-group"><label class="form-label">{{ __('app.name') }} *</label><input type="text" name="name" value="{{ old('name') }}" class="form-control" required>@error('name')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">{{ __('app.description') }}</label><textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('products.categories.index') }}" class="btn btn-outline">{{ __('app.cancel') }}</a><button type="submit" class="btn btn-primary">{{ __('app.add_category') }}</button></div>
    </form>
</div>
@endsection
