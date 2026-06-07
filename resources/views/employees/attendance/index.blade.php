@extends('layouts.app')
@section('title', __('app.attendance_management'))
@php
$pageTitle = __('app.attendance_management');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.employees'),  'url' => route('employees.index')],
    ['label' => __('app.attendance'), 'url' => ''],
];
@endphp

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-mini">
        <div class="stat-mini-icon bg-emerald-100">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-emerald-600">{{ $stats['present'] ?? 0 }}</div>
            <div class="stat-mini-label">{{ __('app.present_days') ?? 'Present' }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-rose-100">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-rose-600">{{ $stats['absent'] ?? 0 }}</div>
            <div class="stat-mini-label">{{ __('app.absent_days') ?? 'Absent' }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-amber-100">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-amber-600">{{ $stats['late'] ?? 0 }}</div>
            <div class="stat-mini-label">{{ __('app.late_arrivals') ?? 'Late' }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-sky-100">
            <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-sky-600">{{ $stats['leave'] ?? 0 }}</div>
            <div class="stat-mini-label">{{ __('app.on_leave') ?? 'Half day' }}</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Add attendance form --}}
    <div class="xl:col-span-1">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.add_attendance') ?? 'Add / Update Record' }}</h3>
                </div>
            </div>
            <form method="POST" action="{{ route('employees.attendance.store') }}" class="p-5 space-y-4" id="attendance-form">
                @csrf
                @if(session('success'))
                <div class="px-3 py-2 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded text-sm">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                <div class="px-3 py-2 bg-rose-50 border border-rose-200 text-rose-700 rounded text-sm">{{ $errors->first() }}</div>
                @endif

                {{-- Edit mode banner --}}
                <div id="edit-banner" class="hidden px-3 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded text-sm flex items-center justify-between">
                    <span>{{ __('app.editing_record') ?? 'Editing record' }}</span>
                    <button type="button" onclick="resetForm()" class="text-indigo-400 hover:text-indigo-700 text-xs underline">{{ __('app.cancel') }}</button>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('app.employee') }} *</label>
                    <select name="employee_id" id="f-employee" class="form-control" required>
                        <option value="">{{ __('app.select_employee') }}</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->user->full_name ?? '—' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('app.date') }} *</label>
                    <input type="date" name="date" id="f-date" value="{{ old('date', date('Y-m-d')) }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('app.status') }} *</label>
                    <select name="status" id="f-status" class="form-control" required>
                        <option value="present"  {{ old('status','present') == 'present'  ? 'selected' : '' }}>{{ __('app.present') ?? 'Present' }}</option>
                        <option value="absent"   {{ old('status') == 'absent'   ? 'selected' : '' }}>{{ __('app.absent') ?? 'Absent' }}</option>
                        <option value="late"     {{ old('status') == 'late'     ? 'selected' : '' }}>{{ __('app.late') ?? 'Late' }}</option>
                        <option value="half_day" {{ old('status') == 'half_day' ? 'selected' : '' }}>{{ __('app.half_day') ?? 'Half Day' }}</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="form-group">
                        <label class="form-label">{{ __('app.check_in') }}</label>
                        <input type="time" name="check_in" id="f-check-in" value="{{ old('check_in') }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('app.check_out') }}</label>
                        <input type="time" name="check_out" id="f-check-out" value="{{ old('check_out') }}" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('app.notes') }}</label>
                    <textarea name="notes" id="f-notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" id="f-submit" class="btn btn-primary flex-1">{{ __('app.save') }}</button>
                    <button type="button" id="f-reset" onclick="resetForm()" class="btn btn-outline hidden">{{ __('app.cancel') }}</button>
                </div>
            </form>
        </div>

        {{-- Filter card --}}
        <div class="card mt-4">
            <div class="card-header"><h3 class="card-title">{{ __('app.filter') }}</h3></div>
            <form method="GET" class="p-5 space-y-3">
                <div class="form-group">
                    <label class="form-label">{{ __('app.employee') }}</label>
                    <select name="employee_id" class="form-control">
                        <option value="">{{ __('app.all_employees') }}</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->user->full_name ?? '—' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('app.from_date') ?? 'From' }}</label>
                    <input type="date" name="from_date" value="{{ request('from_date', date('Y-m-01')) }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('app.to_date') ?? 'To' }}</label>
                    <input type="date" name="to_date" value="{{ request('to_date', date('Y-m-d')) }}" class="form-control">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-1">{{ __('app.filter') }}</button>
                    <a href="{{ route('employees.attendance.index') }}" class="btn btn-outline btn-sm">{{ __('app.reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Records table --}}
    <div class="xl:col-span-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('app.attendance_records') }}</h3>
                @if($attendance->total())
                <span class="badge badge-secondary">{{ $attendance->total() }}</span>
                @endif
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('app.date') }}</th>
                            <th>{{ __('app.employee') }}</th>
                            <th class="text-center">{{ __('app.check_in') }}</th>
                            <th class="text-center">{{ __('app.check_out') }}</th>
                            <th class="text-center">{{ __('app.work_hours') ?? 'Hours' }}</th>
                            <th>{{ __('app.status') }}</th>
                            <th class="w-24 text-center">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendance as $record)
                        @php
                        $statusCfg = match($record->status) {
                            'present'  => 'badge-success',
                            'absent'   => 'badge-danger',
                            'late'     => 'badge-warning',
                            'half_day' => 'badge-info',
                            default    => 'badge-secondary',
                        };
                        @endphp
                        <tr>
                            <td class="text-sm text-slate-600 whitespace-nowrap">
                                {{ $record->date->format('d M Y') }}
                            </td>
                            <td>
                                <div class="text-sm font-semibold text-slate-800">
                                    {{ $record->employee->user->full_name ?? '—' }}
                                </div>
                            </td>
                            <td class="text-center text-sm font-mono text-slate-600">
                                {{ $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('H:i') : '—' }}
                            </td>
                            <td class="text-center text-sm font-mono text-slate-600">
                                {{ $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('H:i') : '—' }}
                            </td>
                            <td class="text-center text-sm font-bold text-slate-700">
                                {{ $record->work_hours ? number_format($record->work_hours, 1) . 'h' : '—' }}
                            </td>
                            <td>
                                <span class="badge {{ $statusCfg }}">
                                    {{ __('app.' . $record->status) ?? ucfirst($record->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button"
                                            onclick="editRecord({{ $record->id }}, {{ $record->employee_id }}, '{{ $record->date->format('Y-m-d') }}', '{{ $record->status }}', '{{ $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('H:i') : '' }}', '{{ $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('H:i') : '' }}', {{ json_encode($record->notes ?? '') }})"
                                            class="action-btn action-btn-edit" title="{{ __('app.edit') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <form method="POST" action="{{ route('employees.attendance.destroy', $record->id) }}"
                                          onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-btn action-btn-delete" title="{{ __('app.delete') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state py-10">
                                    <div class="empty-state-icon">
                                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <p class="empty-state-title">{{ __('app.no_attendance_records_found') ?? 'No records found' }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($attendance->hasPages())
            <div class="pagination">{{ $attendance->links() }}</div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function editRecord(id, employeeId, date, status, checkIn, checkOut, notes) {
    document.getElementById('f-employee').value  = employeeId;
    document.getElementById('f-date').value      = date;
    document.getElementById('f-status').value    = status;
    document.getElementById('f-check-in').value  = checkIn;
    document.getElementById('f-check-out').value = checkOut;
    document.getElementById('f-notes').value     = notes;

    document.getElementById('edit-banner').classList.remove('hidden');
    document.getElementById('f-reset').classList.remove('hidden');
    document.getElementById('f-submit').textContent = '{{ __('app.update') ?? 'Update' }}';

    document.getElementById('attendance-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetForm() {
    document.getElementById('attendance-form').reset();
    document.getElementById('f-date').value = '{{ date('Y-m-d') }}';
    document.getElementById('f-status').value = 'present';
    document.getElementById('edit-banner').classList.add('hidden');
    document.getElementById('f-reset').classList.add('hidden');
    document.getElementById('f-submit').textContent = '{{ __('app.save') }}';
}
</script>
@endpush
