@extends('layouts.app')
@section('title', __('app.edit_company'))
@php $pageTitle = __('app.edit_company'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.companies'),'url'=>route('companies.index')],['label'=>__('app.edit')]]; @endphp
@section('content')
<div class="card max-w-2xl mx-auto">
    <div class="card-header"><h3 class="text-lg font-semibold">{{ __('app.edit_company') }}: {{ $company->name }}</h3></div>
    <form method="POST" action="{{ route('companies.update', $company) }}" enctype="multipart/form-data" class="p-6 space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="form-label">{{ __('app.name') }} <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $company->name) }}" class="form-input" required>
            @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">{{ __('app.email') }}</label>
                <input type="email" name="email" value="{{ old('email', $company->email) }}" class="form-input">
            </div>
            <div>
                <label class="form-label">{{ __('app.phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="form-input">
            </div>
        </div>
        <div>
            <label class="form-label">{{ __('app.address') }}</label>
            <textarea name="address" rows="2" class="form-input">{{ old('address', $company->address) }}</textarea>
        </div>
        <div>
            <label class="form-label">{{ __('app.logo') }}</label>
            @if($company->logo)<img src="{{ asset('storage/'.$company->logo) }}" class="w-16 h-16 rounded mb-2 object-cover">@endif
            <input type="file" name="logo" accept="image/*" class="form-input">
        </div>
        <div class="flex gap-3 pt-4 border-t">
            <button type="submit" class="btn btn-primary">{{ __('app.update') }}</button>
            <a href="{{ route('companies.index') }}" class="btn btn-secondary">{{ __('app.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
