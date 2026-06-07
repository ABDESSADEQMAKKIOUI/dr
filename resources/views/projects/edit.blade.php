@extends('layouts.app')
@section('title', __('app.edit_project'))
@php $pageTitle = __('app.edit_project'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.projects'),'url'=>route('projects.index')],['label'=>$project->name, 'url'=>route('projects.show', $project)],['label'=>__('app.edit')]]; @endphp
@section('content')
<div class="max-w-4xl mx-auto">
    <div class="card shadow-xl border-t-4 border-blue-600">
        <div class="card-header bg-white"><h3 class="text-xl font-bold text-gray-800">{{ __('app.edit_project_settings') }}</h3></div>
        <form method="POST" action="{{ route('projects.update', $project) }}" class="p-8 space-y-6">
            @csrf @method('PUT')
            
            <div class="space-y-2">
                <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('app.name') }} <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $project->name) }}" class="form-input text-xl font-black" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="form-label font-bold text-gray-700">{{ __('app.customer') }}</label>
                    <select name="customer_id" class="form-select">
                        <option value="">-- {{ __('app.internal_project') }} --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id', $project->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label font-bold text-gray-700">{{ __('app.project_manager') }} *</label>
                    <select name="user_id" class="form-select" required>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $project->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="form-label font-bold text-gray-700">{{ __('app.status') }} *</label>
                    <select name="status" class="form-select font-bold">
                        @foreach(['planning', 'active', 'on_hold', 'completed', 'cancelled'] as $st)
                            <option value="{{ $st }}" {{ old('status', $project->status) == $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label font-bold text-gray-700">{{ __('app.budget') }}</label>
                    <input type="number" step="0.01" name="budget" value="{{ old('budget', $project->budget) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label font-bold text-gray-700">{{ __('app.deadline') }}</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $project->end_date ? $project->end_date->format('Y-m-d') : '') }}" class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.description') }}</label>
                <textarea name="description" rows="4" class="form-input">{{ old('description', $project->description) }}</textarea>
            </div>

            <div class="flex gap-4 pt-6 border-t">
                <button type="submit" class="btn btn-primary px-10">{{ __('app.update_settings') }}</button>
                <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">{{ __('app.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
