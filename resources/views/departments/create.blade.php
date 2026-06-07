@extends('layouts.app')
@section('title', __('app.add_department') ?? 'Add Department')
@php
$pageTitle = __('app.add_department') ?? 'Add Department';
$breadcrumbs = [
    ['label' => __('app.dashboard'),  'url' => route('dashboard')],
    ['label' => __('app.employees'),  'url' => route('employees.index')],
    ['label' => __('app.departments') ?? 'Departments', 'url' => route('departments.index')],
    ['label' => __('app.create'), 'url' => ''],
];
@endphp

@section('content')
<div class="max-w-xl">
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="card-title">{{ __('app.add_department') ?? 'New Department' }}</h3>
            </div>
        </div>

        <form method="POST" action="{{ route('departments.store') }}" data-validate>
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
                           placeholder="{{ __('app.department_name_placeholder') ?? 'e.g. Sales, Engineering…' }}">
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
                <a href="{{ route('departments.index') }}" class="btn btn-outline">{{ __('app.cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
