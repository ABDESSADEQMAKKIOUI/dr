@extends('layouts.app')
@section('title', __('app.leave_management') ?? 'Leave Management')
@php
$pageTitle = __('app.leave_management') ?? 'Leave Management';
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.employees'),  'url' => route('employees.index')],
    ['label' => __('app.leaves') ?? 'Leaves', 'url' => ''],
];
@endphp

@section('content')

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-mini">
        <div class="stat-mini-icon bg-amber-100">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-amber-600">{{ $stats['pending'] }}</div>
            <div class="stat-mini-label">{{ __('app.pending') }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-emerald-100">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-emerald-600">{{ $stats['approved'] }}</div>
            <div class="stat-mini-label">{{ __('app.approved') }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-rose-100">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-rose-600">{{ $stats['rejected'] }}</div>
            <div class="stat-mini-label">{{ __('app.rejected') }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-slate-100">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value">{{ $stats['total'] }}</div>
            <div class="stat-mini-label">{{ __('app.total') }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('app.leave_requests') ?? 'Leave Requests' }}</h3>
        <a href="{{ route('employees.leaves.create') }}" class="btn btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.new_leave_request') ?? 'New Request' }}
        </a>
    </div>

    {{-- Filters --}}
    <div class="filter-bar">
        <form method="GET" class="flex flex-wrap gap-3">
            <div class="filter-group w-44">
                <label class="filter-label">{{ __('app.employee') }}</label>
                <select name="employee_id" class="form-control py-2">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->user->full_name ?? '—' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group w-36">
                <label class="filter-label">{{ __('app.status') }}</label>
                <select name="status" class="form-control py-2">
                    <option value="">{{ __('app.all') }}</option>
                    <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>{{ __('app.pending') }}</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('app.approved') }}</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('app.rejected') }}</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label opacity-0">.</label>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('app.filter') }}</button>
                    <a href="{{ route('employees.leaves.index') }}" class="btn btn-ghost btn-sm text-slate-500">{{ __('app.reset') }}</a>
                </div>
            </div>
        </form>
    </div>

    @if(session('success'))
    <div class="mx-5 mt-2 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('app.employee') }}</th>
                    <th>{{ __('app.leave_type') ?? 'Type' }}</th>
                    <th>{{ __('app.start_date') ?? 'From' }}</th>
                    <th>{{ __('app.end_date') ?? 'To' }}</th>
                    <th class="text-center">{{ __('app.days') ?? 'Days' }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th class="w-20 text-center">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                @php
                $sc = match($leave->status) {
                    'approved' => 'badge-success',
                    'rejected' => 'badge-danger',
                    default    => 'badge-warning',
                };
                @endphp
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-sky-400 to-sky-600 flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-xs font-bold">{{ strtoupper(substr($leave->employee->user->full_name ?? 'E', 0, 2)) }}</span>
                            </div>
                            <span class="text-sm font-semibold text-slate-800">{{ $leave->employee->user->full_name ?? '—' }}</span>
                        </div>
                    </td>
                    <td><span class="text-xs bg-sky-100 text-sky-700 px-2 py-0.5 rounded-full font-medium">{{ $leave->type }}</span></td>
                    <td class="text-sm text-slate-600">{{ $leave->start_date->format('d M Y') }}</td>
                    <td class="text-sm text-slate-600">{{ $leave->end_date->format('d M Y') }}</td>
                    <td class="text-center">
                        <span class="badge badge-secondary">{{ $leave->days }}</span>
                    </td>
                    <td><span class="badge {{ $sc }}">{{ __('app.' . $leave->status) }}</span></td>
                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('employees.leaves.show', $leave->id) }}"
                               class="action-btn action-btn-view" title="{{ __('app.view') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @if($leave->status === 'pending')
                            <form method="POST" action="{{ route('employees.leaves.destroy', $leave->id) }}"
                                  onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn action-btn-delete" title="{{ __('app.delete') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
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
                            <p class="empty-state-title">{{ __('app.no_results') }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($leaves->hasPages())
    <div class="pagination">{{ $leaves->links() }}</div>
    @endif
</div>
@endsection
