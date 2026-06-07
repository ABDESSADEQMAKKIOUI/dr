@extends('layouts.app')
@section('title', __('app.add_designation') ?? 'Add Designation')
@php
$pageTitle = __('app.add_designation') ?? 'Add Designation';
$breadcrumbs = [
    ['label' => __('app.dashboard'),  'url' => route('dashboard')],
    ['label' => __('app.employees'),  'url' => route('employees.index')],
    ['label' => __('app.designations') ?? 'Designations', 'url' => route('designations.index')],
    ['label' => __('app.create'), 'url' => ''],
];
@endphp

@section('content')
<div class="max-w-xl">
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="card-title">{{ __('app.add_designation') ?? 'New Designation' }}</h3>
            </div>
        </div>

        <form method="POST" action="{{ route('designations.store') }}" data-validate>
            @csrf
            <div class="p-6 space-y-4">

                @if($errors->any())
                <div class="px-4 py-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-sm">
                    {{ $errors->first() }}
                </div>
                @endif

                <div class="form-group">
                    <label class="form-label">{{ __('app.name') }} *</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="form-control" required autofocus
                           placeholder="{{ __('app.designation_name_placeholder') ?? 'e.g. Senior Developer, HR Manager…' }}">
                    @error('name')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('app.description') }}</label>
                    <textarea name="description" rows="3" class="form-control"
                              placeholder="{{ __('app.optional_description') ?? 'Optional description…' }}">{{ old('description') }}</textarea>
                    @error('description')<span class="form-error">{{ $message }}</span>@enderror
                </div>

            </div>
            <div class="flex justify-end gap-3 px-6 pb-6">
                <a href="{{ route('designations.index') }}" class="btn btn-outline">{{ __('app.cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
