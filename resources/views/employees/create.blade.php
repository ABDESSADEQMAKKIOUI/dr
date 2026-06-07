@extends('layouts.app')
@section('title', __('app.add_employee'))
@php
$pageTitle = __('app.add_employee');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.employees'), 'url' => route('employees.index')], ['label' => __('app.create'), 'url' => '']];
@endphp
@section('content')
<div class="card max-w-4xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.add_employee') }}</h3></div>
    <form method="POST" action="{{ route('employees.store') }}" data-validate>
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">{{ __('app.user') }} *</label>
                <select name="user_id" class="form-control" required>
                    <option value="">{{ __('app.select_user') }}</option>
                    @foreach($users ?? [] as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('app.employee_code') }}</label>
                <input type="text" name="employee_code" value="{{ old('employee_code') }}" class="form-control" placeholder="Auto-generated if empty">
                @error('employee_code')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('app.department') }}</label>
                <select name="department_id" class="form-control">
                    <option value="">{{ __('app.select') }}</option>
                    @foreach($departments ?? [] as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('app.designation') }}</label>
                <select name="designation_id" class="form-control">
                    <option value="">{{ __('app.select') }}</option>
                    @foreach($designations ?? [] as $desig)
                        <option value="{{ $desig->id }}" {{ old('designation_id') == $desig->id ? 'selected' : '' }}>
                            {{ $desig->name }}
                        </option>
                    @endforeach
                </select>
                @error('designation_id')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('app.hire_date') }} *</label>
                <input type="date" name="hire_date" value="{{ old('hire_date', date('Y-m-d')) }}" class="form-control" required>
                @error('hire_date')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('app.salary') }}</label>
                <input type="number" step="0.01" name="salary" value="{{ old('salary') }}" class="form-control" placeholder="0.00">
                @error('salary')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('app.commission_rate') }} (%)</label>
                <input type="number" step="0.01" name="commission_rate" value="{{ old('commission_rate') }}" class="form-control" placeholder="0.00" min="0" max="100">
                @error('commission_rate')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">{{ __('app.active') }}</span>
                </label>
            </div>
        </div>
        <div class="flex justify-end space-x-2 mt-6">
            <a href="{{ route('employees.index') }}" class="btn btn-outline">{{ __('app.cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('app.add_employee') }}</button>
        </div>
    </form>
</div>
@endsection
