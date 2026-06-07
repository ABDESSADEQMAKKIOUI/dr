@extends('layouts.app')
@section('title', __('app.leave_details') ?? 'Leave Details')
@php
$pageTitle = ($leave->employee->user->full_name ?? 'Employee') . ' — ' . $leave->type;
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.employees'),  'url' => route('employees.index')],
    ['label' => __('app.leaves') ?? 'Leaves', 'url' => route('employees.leaves.index')],
    ['label' => $pageTitle, 'url' => ''],
];
@endphp

@section('content')

<div class="flex items-center justify-between mb-6">
    <a href="{{ route('employees.leaves.index') }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back') }}
    </a>

    @if($leave->status === 'pending')
    <form method="POST" action="{{ route('employees.leaves.update-status', $leave->id) }}"
          class="flex items-center gap-2">
        @csrf
        <select name="status" class="form-control py-1.5 text-sm w-36">
            <option value="approved">{{ __('app.approved') }}</option>
            <option value="rejected">{{ __('app.rejected') }}</option>
        </select>
        <button type="submit"
                onclick="return confirm('{{ __('app.confirm_update_status') ?? 'Update status?' }}')"
                class="btn btn-primary btn-sm">
            {{ __('app.update_status') }}
        </button>
    </form>
    @elseif($leave->status === 'approved')
    <span class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 bg-emerald-50 px-4 py-2 rounded-lg border border-emerald-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ __('app.approved') }}
    </span>
    @else
    <span class="inline-flex items-center gap-2 text-sm font-semibold text-rose-600 bg-rose-50 px-4 py-2 rounded-lg border border-rose-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        {{ __('app.rejected') }}
    </span>
    @endif
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Left --}}
    <div class="space-y-5">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-sky-400 to-sky-600 flex items-center justify-center text-white font-black text-sm">
                        {{ strtoupper(substr($leave->employee->user->full_name ?? 'E', 0, 2)) }}
                    </div>
                    <div>
                        <h3 class="card-title">{{ $leave->employee->user->full_name ?? '—' }}</h3>
                        <p class="text-xs text-slate-400">{{ $leave->employee->employee_code }}</p>
                    </div>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.department') }}</span>
                    <span class="text-sm font-medium text-slate-700">{{ $leave->employee->department->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.designation') }}</span>
                    <span class="text-sm font-medium text-slate-700">{{ $leave->employee->designation->name ?? '—' }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.leave_details') ?? 'Leave Details' }}</h3></div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.leave_type') ?? 'Type' }}</span>
                    <span class="text-xs bg-sky-100 text-sky-700 px-2 py-0.5 rounded-full font-semibold">{{ $leave->type }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.start_date') ?? 'From' }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $leave->start_date->format('d M Y') }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.end_date') ?? 'To' }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $leave->end_date->format('d M Y') }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.days') ?? 'Days' }}</span>
                    <span class="badge badge-secondary">{{ $leave->days }} {{ __('app.days') ?? 'days' }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.status') }}</span>
                    @php $sc = match($leave->status) { 'approved' => 'badge-success', 'rejected' => 'badge-danger', default => 'badge-warning' }; @endphp
                    <span class="badge {{ $sc }}">{{ __('app.' . $leave->status) }}</span>
                </div>
                @if($leave->approver)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.approved_by') ?? 'Processed by' }}</span>
                    <span class="text-sm font-medium text-slate-700">{{ $leave->approver->full_name ?? '—' }}</span>
                </div>
                @endif
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.submitted_on') ?? 'Submitted' }}</span>
                    <span class="text-sm text-slate-600">{{ $leave->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Right --}}
    <div class="xl:col-span-2 space-y-5">
        @if($leave->reason)
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.reason') }}</h3></div>
            <div class="card-body">
                <p class="text-sm text-slate-600 leading-relaxed">{{ $leave->reason }}</p>
            </div>
        </div>
        @endif

        {{-- Timeline --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.timeline') ?? 'Timeline' }}</h3></div>
            <div class="p-5">
                <div class="flex items-center gap-4">
                    <div class="flex flex-col items-center">
                        <div class="w-3 h-3 rounded-full bg-indigo-500"></div>
                        <div class="w-0.5 h-12 bg-slate-200 mt-1"></div>
                    </div>
                    <div class="pb-8">
                        <p class="text-sm font-semibold text-slate-700">{{ __('app.submitted') ?? 'Submitted' }}</p>
                        <p class="text-xs text-slate-400">{{ $leave->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex flex-col items-center">
                        <div class="w-3 h-3 rounded-full {{ $leave->status === 'pending' ? 'bg-slate-300' : ($leave->status === 'approved' ? 'bg-emerald-500' : 'bg-rose-500') }}"></div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold {{ $leave->status === 'pending' ? 'text-slate-400' : 'text-slate-700' }}">
                            {{ $leave->status === 'approved' ? __('app.approved') : ($leave->status === 'rejected' ? __('app.rejected') : __('app.awaiting_approval') ?? 'Awaiting Approval') }}
                        </p>
                        @if($leave->updated_at && $leave->status !== 'pending')
                        <p class="text-xs text-slate-400">{{ $leave->updated_at->format('d M Y, H:i') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
