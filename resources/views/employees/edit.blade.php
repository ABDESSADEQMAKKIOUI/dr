@extends('layouts.app')
@section('title', __('app.edit_employee'))
@php
$pageTitle = __('app.edit_employee');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.employees'), 'url' => route('employees.index')], ['label' => __('app.edit'), 'url' => '']];
@endphp
@section('content')
<div class="card max-w-4xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.edit_employee') }}: {{ $employee->name }}</h3></div>
    <form method="POST" action="{{ route('employees.update', $employee->id) }}" enctype="multipart/form-data" data-validate>
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group"><label class="form-label">{{ __('app.full_name') }} *</label><input type="text" name="name" value="{{ old('name', $employee->name) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">{{ __('app.employee_id') }} *</label><input type="text" name="employee_id" value="{{ old('employee_id', $employee->employee_id) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">{{ __('app.email') }} *</label><input type="email" name="email" value="{{ old('email', $employee->email) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">{{ __('app.phone') }} *</label><input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">{{ __('app.date_of_birth') }}</label><input type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">{{ __('app.gender') }}</label><select name="gender" class="form-control"><option value="">{{ __('app.select') }}</option><option value="male" {{ $employee->gender == 'male' ? 'selected' : '' }}>{{ __('app.male') }}</option><option value="female" {{ $employee->gender == 'female' ? 'selected' : '' }}>{{ __('app.female') }}</option><option value="other" {{ $employee->gender == 'other' ? 'selected' : '' }}>{{ __('app.other') }}</option></select></div>
            <div class="form-group"><label class="form-label">{{ __('app.department') }} *</label><select name="department" class="form-control" required><option value="">{{ __('app.select_department') }}</option><option value="Sales" {{ $employee->department == 'Sales' ? 'selected' : '' }}>Sales</option><option value="IT" {{ $employee->department == 'IT' ? 'selected' : '' }}>IT</option><option value="HR" {{ $employee->department == 'HR' ? 'selected' : '' }}>HR</option><option value="Finance" {{ $employee->department == 'Finance' ? 'selected' : '' }}>Finance</option><option value="Operations" {{ $employee->department == 'Operations' ? 'selected' : '' }}>Operations</option><option value="Marketing" {{ $employee->department == 'Marketing' ? 'selected' : '' }}>Marketing</option></select></div>
            <div class="form-group"><label class="form-label">{{ __('app.position') }} *</label><input type="text" name="position" value="{{ old('position', $employee->position) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">{{ __('app.hire_date') }} *</label><input type="date" name="hire_date" value="{{ old('hire_date', $employee->hire_date->format('Y-m-d')) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">{{ __('app.salary') }} *</label><input type="number" step="0.01" name="salary" value="{{ old('salary', $employee->salary) }}" class="form-control" required></div>
            <div class="form-group md:col-span-2"><label class="form-label">{{ __('app.address') }}</label><textarea name="address" rows="2" class="form-control">{{ old('address', $employee->address) }}</textarea></div>
            <div class="form-group"><label class="form-label">{{ __('app.emergency_contact_name') }}</label><input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">{{ __('app.emergency_contact_phone') }}</label><input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}" class="form-control"></div>
            @if($employee->photo)<div class="form-group md:col-span-2"><label class="form-label">{{ __('app.current_photo') }}</label><div class="flex items-center space-x-4"><img src="{{ Storage::url($employee->photo) }}" class="w-20 h-20 rounded-full object-cover"><label class="flex items-center"><input type="checkbox" name="remove_photo" value="1" class="w-4 h-4"><span class="ml-2 text-sm text-red-600">{{ __('app.remove') }}</span></label></div></div>@endif
            <div class="form-group md:col-span-2"><label class="form-label">{{ __('app.new_profile_photo') }}</label><input type="file" name="photo" class="form-control" accept="image/*"></div>
            <div class="form-group md:col-span-2"><label class="form-label">{{ __('app.status') }}</label><select name="status" class="form-control"><option value="active" {{ $employee->status == 'active' ? 'selected' : '' }}>{{ __('app.active') }}</option><option value="on_leave" {{ $employee->status == 'on_leave' ? 'selected' : '' }}>{{ __('app.on_leave') }}</option><option value="inactive" {{ $employee->status == 'inactive' ? 'selected' : '' }}>{{ __('app.inactive') }}</option></select></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('employees.index') }}" class="btn btn-outline">{{ __('app.cancel') }}</a><button type="submit" class="btn btn-primary">{{ __('app.update_employee') }}</button></div>
    </form>
</div>
@endsection
