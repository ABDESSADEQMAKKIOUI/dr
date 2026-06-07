@extends('layouts.app')
@section('title', __('app.new_leave_request') ?? 'New Leave Request')
@php
$pageTitle = __('app.new_leave_request') ?? 'New Leave Request';
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.employees'),  'url' => route('employees.index')],
    ['label' => __('app.leaves') ?? 'Leaves', 'url' => route('employees.leaves.index')],
    ['label' => __('app.create'), 'url' => ''],
];
@endphp

@section('content')
<div class="max-w-xl">
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="card-title">{{ __('app.new_leave_request') ?? 'New Leave Request' }}</h3>
            </div>
        </div>

        <form method="POST" action="{{ route('employees.leaves.store') }}" data-validate>
            @csrf
            <div class="p-6 space-y-4">

                @if($errors->any())
                <div class="px-4 py-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-sm">
                    {{ $errors->first() }}
                </div>
                @endif

                <div class="form-group">
                    <label class="form-label">{{ __('app.employee') }} *</label>
                    <select name="employee_id" class="form-control" required>
                        <option value="">{{ __('app.select_employee') }}</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->user->full_name ?? '—' }}
                        </option>
                        @endforeach
                    </select>
                    @error('employee_id')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('app.leave_type') ?? 'Leave Type' }} *</label>
                    <select name="type" class="form-control" required>
                        <option value="">{{ __('app.select') }}</option>
                        @foreach(['Annual Leave', 'Sick Leave', 'Maternity Leave', 'Paternity Leave', 'Unpaid Leave', 'Emergency Leave', 'Other'] as $type)
                        <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('type')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">{{ __('app.start_date') ?? 'Start Date' }} *</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="form-control" required id="start-date">
                        @error('start_date')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('app.end_date') ?? 'End Date' }} *</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-control" required id="end-date">
                        @error('end_date')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="px-4 py-2 bg-sky-50 border border-sky-200 rounded-lg text-sm text-sky-700">
                    {{ __('app.estimated_days') ?? 'Estimated working days' }}: <strong id="days-count">—</strong>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('app.reason') }}</label>
                    <textarea name="reason" rows="3" class="form-control"
                              placeholder="{{ __('app.leave_reason_placeholder') ?? 'Reason for leave request…' }}">{{ old('reason') }}</textarea>
                </div>

            </div>
            <div class="flex justify-end gap-3 px-6 pb-6">
                <a href="{{ route('employees.leaves.index') }}" class="btn btn-outline">{{ __('app.cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('app.submit') ?? 'Submit Request' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function countWeekdays(start, end) {
    let count = 0, cur = new Date(start);
    const e = new Date(end);
    while (cur <= e) {
        const d = cur.getDay();
        if (d !== 0 && d !== 6) count++;
        cur.setDate(cur.getDate() + 1);
    }
    return count;
}
function updateDays() {
    const s = document.getElementById('start-date').value;
    const e = document.getElementById('end-date').value;
    if (s && e && s <= e) {
        document.getElementById('days-count').textContent = countWeekdays(s, e) + ' {{ __('app.days') ?? 'days' }}';
    } else {
        document.getElementById('days-count').textContent = '—';
    }
}
document.getElementById('start-date').addEventListener('change', updateDays);
document.getElementById('end-date').addEventListener('change', updateDays);
</script>
@endpush
