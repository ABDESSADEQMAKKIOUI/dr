@extends('layouts.app')
@section('title', __('app.edit') . ' — ' . $designation->name)
@php
$pageTitle = __('app.edit') . ' — ' . $designation->name;
$breadcrumbs = [
    ['label' => __('app.dashboard'),  'url' => route('dashboard')],
    ['label' => __('app.employees'),  'url' => route('employees.index')],
    ['label' => __('app.designations') ?? 'Designations', 'url' => route('designations.index')],
    ['label' => $designation->name, 'url' => route('designations.show', $designation)],
    ['label' => __('app.edit'), 'url' => ''],
];
@endphp

@section('content')
<div class="max-w-xl">
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <h3 class="card-title">{{ __('app.edit') }} — {{ $designation->name }}</h3>
            </div>
        </div>

        <form method="POST" action="{{ route('designations.update', $designation) }}" data-validate>
            @csrf @method('PUT')
            <div class="p-6 space-y-4">

                @if($errors->any())
                <div class="px-4 py-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-sm">
                    {{ $errors->first() }}
                </div>
                @endif

                <div class="form-group">
                    <label class="form-label">{{ __('app.name') }} *</label>
                    <input type="text" name="name" value="{{ old('name', $designation->name) }}"
                           class="form-control" required autofocus>
                    @error('name')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('app.description') }}</label>
                    <textarea name="description" rows="3" class="form-control">{{ old('description', $designation->description) }}</textarea>
                    @error('description')<span class="form-error">{{ $message }}</span>@enderror
                </div>

            </div>
            <div class="flex justify-end gap-3 px-6 pb-6">
                <a href="{{ route('designations.show', $designation) }}" class="btn btn-outline">{{ __('app.cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('app.save_changes') ?? 'Save Changes' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
