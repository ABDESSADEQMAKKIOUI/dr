@extends('layouts.app')
@section('title', $department->name)
@php
$pageTitle = $department->name;
$breadcrumbs = [
    ['label' => __('app.dashboard'),  'url' => route('dashboard')],
    ['label' => __('app.employees'),  'url' => route('employees.index')],
    ['label' => __('app.departments') ?? 'Departments', 'url' => route('departments.index')],
    ['label' => $department->name, 'url' => ''],
];
@endphp

@section('content')

{{-- Top bar --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('departments.index') }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back') }}
    </a>
    <div class="flex items-center gap-2">
        <a href="{{ route('departments.edit', $department) }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            {{ __('app.edit') }}
        </a>
        <form method="POST" action="{{ route('departments.destroy', $department) }}"
              onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ __('app.delete') }}
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Left: info --}}
    <div class="space-y-5">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ $department->name }}</h3>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.employees') }}</span>
                    <span class="badge badge-secondary">{{ $department->employees_count }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.created_at') ?? 'Created' }}</span>
                    <span class="text-sm text-slate-700">{{ $department->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>

        @if($department->description)
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.description') }}</h3></div>
            <div class="card-body">
                <p class="text-sm text-slate-600 leading-relaxed">{{ $department->description }}</p>
            </div>
        </div>
        @endif

        {{-- Quick links --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.quick_actions') ?? 'Quick Actions' }}</h3></div>
            <div class="p-4 space-y-2">
                <a href="{{ route('employees.create') }}?department_id={{ $department->id }}"
                   class="btn btn-primary btn-sm w-full justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('app.add_employee') }}
                </a>
                <a href="{{ route('employees.index') }}" class="btn btn-outline btn-sm w-full justify-center">
                    {{ __('app.all_employees') }}
                </a>
            </div>
        </div>
    </div>

    {{-- Right: employees table --}}
    <div class="xl:col-span-2">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.employees') }}</h3>
                </div>
                @if($employees->total())
                <span class="badge badge-secondary">{{ $employees->total() }}</span>
                @endif
            </div>

            @if($employees->count())
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-10">#</th>
                            <th>{{ __('app.name') }}</th>
                            <th>{{ __('app.designation') }}</th>
                            <th>{{ __('app.hire_date') }}</th>
                            <th>{{ __('app.salary') }}</th>
                            <th>{{ __('app.status') }}</th>
                            <th class="w-16 text-center">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $emp)
                        <tr>
                            <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center flex-shrink-0">
                                        <span class="text-white text-xs font-bold">
                                            {{ strtoupper(substr($emp->user->full_name ?? 'E', 0, 2)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-800">{{ $emp->user->full_name ?? '—' }}</div>
                                        <div class="text-xs text-slate-400">{{ $emp->employee_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-sm text-slate-600">{{ $emp->designation->name ?? '—' }}</td>
                            <td class="text-sm text-slate-600">
                                {{ $emp->hire_date ? $emp->hire_date->format('d M Y') : '—' }}
                            </td>
                            <td class="text-sm font-semibold text-emerald-600">
                                {{ number_format($emp->salary ?? 0, 2) }} DH
                            </td>
                            <td>
                                <span class="badge {{ $emp->is_active ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $emp->is_active ? __('app.active') : __('app.inactive') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('employees.show', $emp->id) }}"
                                   class="action-btn action-btn-view" title="{{ __('app.view') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($employees->hasPages())
            <div class="pagination">{{ $employees->links() }}</div>
            @endif
            @else
            <div class="card-body">
                <div class="empty-state py-10">
                    <div class="empty-state-icon">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <p class="empty-state-title">{{ __('app.no_employees_yet') ?? 'No employees yet' }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
