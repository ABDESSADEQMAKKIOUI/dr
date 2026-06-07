@extends('layouts.app')
@section('title', __('app.departments') ?? 'Departments')
@php
$pageTitle = __('app.departments') ?? 'Departments';
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.employees'),   'url' => route('employees.index')],
    ['label' => __('app.departments') ?? 'Departments', 'url' => ''],
];
@endphp

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-mini">
        <div class="stat-mini-icon bg-indigo-100">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value">{{ $departments->total() }}</div>
            <div class="stat-mini-label">{{ __('app.departments') ?? 'Departments' }}</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-emerald-100">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-emerald-600">{{ $departments->sum('employees_count') }}</div>
            <div class="stat-mini-label">{{ __('app.total_employees') ?? 'Total Employees' }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">{{ __('app.departments') ?? 'Departments' }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('app.manage_departments') ?? 'Manage your organisational departments' }}</p>
        </div>
        <a href="{{ route('departments.create') }}" class="btn btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.add_department') ?? 'Add Department' }}
        </a>
    </div>

    @if(session('success'))
    <div class="mx-5 mt-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mx-5 mt-4 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-10">#</th>
                    <th>{{ __('app.name') }}</th>
                    <th>{{ __('app.description') }}</th>
                    <th class="text-center w-32">{{ __('app.employees') }}</th>
                    <th class="w-24 text-center">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dept)
                <tr>
                    <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>
                    <td>
                        <a href="{{ route('departments.show', $dept->id) }}"
                           class="text-sm font-semibold text-indigo-600 hover:underline">
                            {{ $dept->name }}
                        </a>
                    </td>
                    <td class="text-sm text-slate-500 max-w-xs truncate">{{ $dept->description ?? '—' }}</td>
                    <td class="text-center">
                        <span class="badge badge-secondary">{{ $dept->employees_count }}</span>
                    </td>
                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('departments.show', $dept->id) }}"
                               class="action-btn action-btn-view" title="{{ __('app.view') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('departments.edit', $dept->id) }}"
                               class="action-btn action-btn-edit" title="{{ __('app.edit') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('departments.destroy', $dept->id) }}"
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
                    <td colspan="5">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <p class="empty-state-title">{{ __('app.no_results') }}</p>
                            <a href="{{ route('departments.create') }}" class="btn btn-primary btn-sm mt-4">
                                {{ __('app.add_department') ?? 'Add Department' }}
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($departments->hasPages())
    <div class="pagination">{{ $departments->links() }}</div>
    @endif
</div>
@endsection
