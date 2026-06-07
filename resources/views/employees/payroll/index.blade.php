@extends('layouts.app')
@section('title', __('app.payroll_management'))
@php
$pageTitle = __('app.payroll_management');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.employees'),  'url' => route('employees.index')],
    ['label' => __('app.payroll'),    'url' => ''],
];
@endphp

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-mini">
        <div class="stat-mini-icon bg-indigo-100">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-indigo-600">{{ number_format($stats['total'] ?? 0, 2) }}</div>
            <div class="stat-mini-label">{{ __('app.total_payroll') ?? 'Total Payroll' }} (DH)</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-emerald-100">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-emerald-600">{{ number_format($stats['paid'] ?? 0, 2) }}</div>
            <div class="stat-mini-label">{{ __('app.paid') }} (DH)</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-amber-100">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-amber-600">{{ number_format($stats['pending'] ?? 0, 2) }}</div>
            <div class="stat-mini-label">{{ __('app.pending') }} (DH)</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon bg-violet-100">
            <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div>
            <div class="stat-mini-value text-violet-600">{{ $stats['employees'] ?? 0 }}</div>
            <div class="stat-mini-label">{{ __('app.employees') }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">{{ __('app.payroll_records') ?? 'Payroll Records' }}</h3>
        </div>
        <a href="{{ route('employees.payroll.create') }}" class="btn btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.generate_payroll') ?? 'Generate Payroll' }}
        </a>
    </div>

    {{-- Filters --}}
    <div class="filter-bar">
        <form method="GET" class="flex flex-wrap gap-3 w-full">
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
                <label class="filter-label">{{ __('app.month') ?? 'Month' }}</label>
                <select name="month" class="form-control py-2">
                    <option value="">{{ __('app.all') }}</option>
                    @for($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ request('month', date('n')) == $i ? 'selected' : '' }}>
                        {{ date('F', mktime(0,0,0,$i,1)) }}
                    </option>
                    @endfor
                </select>
            </div>
            <div class="filter-group w-28">
                <label class="filter-label">{{ __('app.year') ?? 'Year' }}</label>
                <select name="year" class="form-control py-2">
                    @for($y = date('Y'); $y >= date('Y') - 4; $y--)
                    <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label opacity-0">.</label>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('app.filter') }}</button>
                    <a href="{{ route('employees.payroll.index') }}" class="btn btn-ghost btn-sm text-slate-500">{{ __('app.reset') }}</a>
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
                    <th>{{ __('app.period') }}</th>
                    <th class="text-right">{{ __('app.basic_salary') }}</th>
                    <th class="text-right">{{ __('app.allowances') }}</th>
                    <th class="text-right">{{ __('app.deductions') }}</th>
                    <th class="text-right">{{ __('app.net_salary') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th class="w-16 text-center">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls as $payroll)
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-xs font-bold">{{ strtoupper(substr($payroll->employee->user->full_name ?? 'E', 0, 2)) }}</span>
                            </div>
                            <span class="text-sm font-semibold text-slate-800">{{ $payroll->employee->user->full_name ?? '—' }}</span>
                        </div>
                    </td>
                    <td class="text-sm text-slate-600 whitespace-nowrap">
                        {{ date('F', mktime(0,0,0,$payroll->month,1)) }} {{ $payroll->year }}
                    </td>
                    <td class="text-right text-sm text-slate-700">{{ number_format($payroll->basic_salary, 2) }} DH</td>
                    <td class="text-right text-sm text-emerald-600 font-semibold">+{{ number_format($payroll->allowances, 2) }}</td>
                    <td class="text-right text-sm text-rose-600 font-semibold">-{{ number_format($payroll->deductions, 2) }}</td>
                    <td class="text-right text-sm font-bold text-indigo-700">{{ number_format($payroll->net_salary, 2) }} DH</td>
                    <td>
                        <span class="badge {{ $payroll->status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                            {{ $payroll->status === 'paid' ? __('app.paid') : __('app.draft') }}
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('employees.payroll.show', $payroll->id) }}"
                           class="action-btn action-btn-view" title="{{ __('app.view') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state py-10">
                            <div class="empty-state-icon">
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <p class="empty-state-title">{{ __('app.no_payroll_records_found') ?? 'No payroll records found' }}</p>
                            <a href="{{ route('employees.payroll.create') }}" class="btn btn-primary btn-sm mt-4">
                                {{ __('app.generate_payroll') ?? 'Generate Payroll' }}
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payrolls->hasPages())
    <div class="pagination">{{ $payrolls->links() }}</div>
    @endif
</div>
@endsection
