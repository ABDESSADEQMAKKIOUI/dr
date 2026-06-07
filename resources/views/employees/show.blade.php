@extends('layouts.app')
@section('title', $employee->user->full_name ?? __('app.employee_details'))
@php
$pageTitle = $employee->user->full_name ?? __('app.employee_details');
$breadcrumbs = [
    ['label' => __('app.dashboard'),  'url' => route('dashboard')],
    ['label' => __('app.employees'),  'url' => route('employees.index')],
    ['label' => $employee->user->full_name ?? 'Employee', 'url' => ''],
];
@endphp

@section('content')

{{-- Top action bar --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('employees.index') }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back') }}
    </a>
    <div class="flex items-center gap-2">
        <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            {{ __('app.edit') }}
        </a>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- LEFT: profile card --}}
    <div class="space-y-5">

        {{-- Profile card --}}
        <div class="card">
            <div class="p-6 flex flex-col items-center text-center">
                {{-- Avatar --}}
                @if($employee->photo ?? false)
                    <img src="{{ asset('storage/' . $employee->photo) }}"
                         class="w-24 h-24 rounded-full object-cover ring-4 ring-indigo-100 mb-4"
                         alt="{{ $employee->user->full_name }}">
                @else
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-700 flex items-center justify-center text-white text-3xl font-black mb-4 ring-4 ring-indigo-100">
                        {{ strtoupper(substr($employee->user->full_name ?? 'E', 0, 2)) }}
                    </div>
                @endif

                <h3 class="text-lg font-bold text-slate-800">{{ $employee->user->full_name ?? '—' }}</h3>
                <p class="text-sm mt-1">
                    @if($employee->designation)
                        <a href="{{ route('designations.show', $employee->designation->id) }}"
                           class="text-violet-600 font-semibold hover:underline">{{ $employee->designation->name }}</a>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </p>
                <p class="text-xs text-slate-500 mt-0.5">
                    @if($employee->department)
                        <a href="{{ route('departments.show', $employee->department->id) }}"
                           class="text-indigo-500 hover:underline">{{ $employee->department->name }}</a>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </p>
                <div class="mt-3">
                    <span class="badge {{ $employee->is_active ? 'badge-success' : 'badge-secondary' }}">
                        {{ $employee->is_active ? __('app.active') : __('app.inactive') }}
                    </span>
                </div>
            </div>

            {{-- Key stats --}}
            <div class="border-t border-slate-100 grid grid-cols-2 divide-x divide-slate-100">
                <div class="px-4 py-3 text-center">
                    <div class="text-base font-bold text-emerald-600">{{ number_format($employee->salary ?? 0, 2) }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">{{ __('app.salary') }} (DH)</div>
                </div>
                <div class="px-4 py-3 text-center">
                    <div class="text-base font-bold text-slate-700">{{ number_format($employee->commission_rate ?? 0, 1) }}%</div>
                    <div class="text-xs text-slate-400 mt-0.5">{{ __('app.commission_rate') }}</div>
                </div>
            </div>
        </div>

        {{-- Contact info --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-sky-100 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.contact') ?? 'Contact' }}</h3>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-start justify-between px-5 py-3 gap-3">
                    <span class="text-sm text-slate-500 flex-shrink-0">{{ __('app.email') }}</span>
                    <span class="text-sm font-medium text-slate-700 text-right break-all">{{ $employee->user->email ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.phone') }}</span>
                    <span class="text-sm font-medium text-slate-700">{{ $employee->user->phone ?? '—' }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- RIGHT: details --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- Employment info --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.employment_information') }}</h3>
                </div>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-5">

                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('app.employee_code') }}</dt>
                    <dd class="mt-1">
                        <span class="mono text-sm font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg">
                            {{ $employee->employee_code }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('app.status') }}</dt>
                    <dd class="mt-1">
                        <span class="badge {{ $employee->is_active ? 'badge-success' : 'badge-secondary' }}">
                            {{ $employee->is_active ? __('app.active') : __('app.inactive') }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('app.department') }}</dt>
                    <dd class="mt-1 text-sm font-semibold">
                        @if($employee->department)
                            <a href="{{ route('departments.show', $employee->department->id) }}"
                               class="text-indigo-600 hover:underline">{{ $employee->department->name }}</a>
                        @else <span class="text-slate-400">—</span> @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('app.designation') }}</dt>
                    <dd class="mt-1 text-sm font-semibold">
                        @if($employee->designation)
                            <a href="{{ route('designations.show', $employee->designation->id) }}"
                               class="text-violet-600 hover:underline">{{ $employee->designation->name }}</a>
                        @else <span class="text-slate-400">—</span> @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('app.hire_date') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-700">
                        {{ $employee->hire_date ? $employee->hire_date->format('d M Y') : '—' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('app.tenure') }}</dt>
                    <dd class="mt-1 text-sm text-slate-600">
                        {{ $employee->hire_date ? $employee->hire_date->diffForHumans() : '—' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('app.salary') }}</dt>
                    <dd class="mt-1 text-sm font-bold text-emerald-600">
                        {{ number_format($employee->salary ?? 0, 2) }} DH
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('app.commission_rate') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-700">
                        {{ number_format($employee->commission_rate ?? 0, 2) }}%
                    </dd>
                </div>

            </div>
        </div>

        {{-- Personal info --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.personal_information') }}</h3>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.name') }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $employee->user->full_name ?? '—' }}</span>
                </div>
                <div class="flex items-start justify-between px-5 py-3 gap-3">
                    <span class="text-sm text-slate-500 flex-shrink-0">{{ __('app.email') }}</span>
                    <span class="text-sm text-slate-700 text-right break-all">{{ $employee->user->email ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.phone') }}</span>
                    <span class="text-sm text-slate-700">{{ $employee->user->phone ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.created_at') ?? 'Joined' }}</span>
                    <span class="text-sm text-slate-600">{{ $employee->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>

        {{-- Quick links --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ route('employees.attendance.index') }}"
               class="card p-4 flex items-center gap-4 hover:shadow-md transition group">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-200 transition">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-700 group-hover:text-indigo-600 transition">{{ __('app.attendance') }}</div>
                    <div class="text-xs text-slate-400">{{ __('app.view_attendance') ?? 'View attendance records' }}</div>
                </div>
                <svg class="w-4 h-4 text-slate-300 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            <a href="{{ route('employees.payroll.index') }}"
               class="card p-4 flex items-center gap-4 hover:shadow-md transition group">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-200 transition">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-700 group-hover:text-indigo-600 transition">{{ __('app.payroll') }}</div>
                    <div class="text-xs text-slate-400">{{ __('app.view_payroll') ?? 'View payroll history' }}</div>
                </div>
                <svg class="w-4 h-4 text-slate-300 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

    </div>
</div>

@endsection
