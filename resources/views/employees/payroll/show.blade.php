@extends('layouts.app')
@section('title', __('app.payroll_details'))
@php
$pageTitle = __('app.payroll_details');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.employees'),  'url' => route('employees.index')],
    ['label' => __('app.payroll'),    'url' => route('employees.payroll.index')],
    ['label' => ($payroll->employee->user->full_name ?? '') . ' — ' . date('M Y', mktime(0,0,0,$payroll->month,1,$payroll->year)), 'url' => ''],
];
@endphp

@section('content')
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('employees.payroll.index') }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back') }}
    </a>
    <div class="flex items-center gap-2">
        <button onclick="window.print()" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            {{ __('app.print') }}
        </button>
        @if($payroll->status === 'draft')
        <form method="POST" action="{{ route('employees.payroll.mark-paid', $payroll->id) }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm"
                    onclick="return confirm('{{ __('app.confirm_mark_paid') ?? 'Mark this payroll as paid?' }}')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ __('app.mark_as_paid') ?? 'Mark as Paid' }}
            </button>
        </form>
        @else
        <span class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 bg-emerald-50 px-4 py-2 rounded-lg border border-emerald-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ __('app.paid') }} — {{ $payroll->paid_at?->format('d M Y') }}
        </span>
        @endif
    </div>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Left: summary --}}
    <div class="space-y-5">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white font-black text-sm">
                        {{ strtoupper(substr($payroll->employee->user->full_name ?? 'E', 0, 2)) }}
                    </div>
                    <div>
                        <h3 class="card-title">{{ $payroll->employee->user->full_name ?? '—' }}</h3>
                        <p class="text-xs text-slate-400">{{ $payroll->employee->employee_code }}</p>
                    </div>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.department') }}</span>
                    <span class="text-sm font-medium text-slate-700">{{ $payroll->employee->department->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.designation') }}</span>
                    <span class="text-sm font-medium text-slate-700">{{ $payroll->employee->designation->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.pay_period') ?? 'Pay Period' }}</span>
                    <span class="text-sm font-bold text-slate-700">
                        {{ date('F', mktime(0,0,0,$payroll->month,1)) }} {{ $payroll->year }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.status') }}</span>
                    <span class="badge {{ $payroll->status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                        {{ $payroll->status === 'paid' ? __('app.paid') : __('app.draft') }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.generated_on') ?? 'Generated' }}</span>
                    <span class="text-sm text-slate-600">{{ $payroll->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>

        {{-- Net salary highlight --}}
        <div class="card bg-gradient-to-br from-indigo-600 to-indigo-800 text-white">
            <div class="p-6 text-center">
                <p class="text-indigo-200 text-sm mb-1">{{ __('app.net_salary') }}</p>
                <p class="text-4xl font-black">{{ number_format($payroll->net_salary, 2) }}</p>
                <p class="text-indigo-200 text-sm mt-1">DH</p>
            </div>
        </div>

        @if($payroll->notes)
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.notes') }}</h3></div>
            <div class="card-body"><p class="text-sm text-slate-600">{{ $payroll->notes }}</p></div>
        </div>
        @endif
    </div>

    {{-- Right: salary breakdown --}}
    <div class="xl:col-span-2">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.salary_breakdown') }}</h3></div>
            <div class="divide-y divide-slate-100">

                {{-- Basic --}}
                <div class="flex items-center justify-between px-5 py-3 bg-slate-50">
                    <span class="text-sm font-bold text-slate-700">{{ __('app.basic_salary') }}</span>
                    <span class="text-sm font-bold text-slate-800">{{ number_format($payroll->basic_salary, 2) }} DH</span>
                </div>

                {{-- Allowances header --}}
                <div class="px-5 py-2 bg-emerald-50">
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wide">{{ __('app.allowances') }}</span>
                </div>
                @if($payroll->housing_allowance > 0)
                <div class="flex items-center justify-between px-5 py-2.5 pl-8">
                    <span class="text-sm text-slate-600">{{ __('app.housing_allowance') }}</span>
                    <span class="text-sm font-semibold text-emerald-600">+{{ number_format($payroll->housing_allowance, 2) }} DH</span>
                </div>
                @endif
                @if($payroll->transport_allowance > 0)
                <div class="flex items-center justify-between px-5 py-2.5 pl-8">
                    <span class="text-sm text-slate-600">{{ __('app.transport_allowance') }}</span>
                    <span class="text-sm font-semibold text-emerald-600">+{{ number_format($payroll->transport_allowance, 2) }} DH</span>
                </div>
                @endif
                @if($payroll->overtime > 0)
                <div class="flex items-center justify-between px-5 py-2.5 pl-8">
                    <span class="text-sm text-slate-600">{{ __('app.overtime_pay') }}</span>
                    <span class="text-sm font-semibold text-emerald-600">+{{ number_format($payroll->overtime, 2) }} DH</span>
                </div>
                @endif
                @if($payroll->bonus > 0)
                <div class="flex items-center justify-between px-5 py-2.5 pl-8">
                    <span class="text-sm text-slate-600">{{ __('app.bonus') }}</span>
                    <span class="text-sm font-semibold text-emerald-600">+{{ number_format($payroll->bonus, 2) }} DH</span>
                </div>
                @endif
                <div class="flex items-center justify-between px-5 py-2.5 bg-emerald-50">
                    <span class="text-sm font-bold text-emerald-700">{{ __('app.total_allowances') }}</span>
                    <span class="text-sm font-bold text-emerald-600">+{{ number_format($payroll->allowances, 2) }} DH</span>
                </div>

                {{-- Deductions header --}}
                <div class="px-5 py-2 bg-rose-50">
                    <span class="text-xs font-bold text-rose-700 uppercase tracking-wide">{{ __('app.deductions') }}</span>
                </div>
                @if($payroll->tax > 0)
                <div class="flex items-center justify-between px-5 py-2.5 pl-8">
                    <span class="text-sm text-slate-600">{{ __('app.tax') }}</span>
                    <span class="text-sm font-semibold text-rose-600">-{{ number_format($payroll->tax, 2) }} DH</span>
                </div>
                @endif
                @if($payroll->social_security > 0)
                <div class="flex items-center justify-between px-5 py-2.5 pl-8">
                    <span class="text-sm text-slate-600">{{ __('app.social_security') }}</span>
                    <span class="text-sm font-semibold text-rose-600">-{{ number_format($payroll->social_security, 2) }} DH</span>
                </div>
                @endif
                @if($payroll->insurance > 0)
                <div class="flex items-center justify-between px-5 py-2.5 pl-8">
                    <span class="text-sm text-slate-600">{{ __('app.insurance') }}</span>
                    <span class="text-sm font-semibold text-rose-600">-{{ number_format($payroll->insurance, 2) }} DH</span>
                </div>
                @endif
                @if($payroll->other_deductions > 0)
                <div class="flex items-center justify-between px-5 py-2.5 pl-8">
                    <span class="text-sm text-slate-600">{{ __('app.other_deductions') }}</span>
                    <span class="text-sm font-semibold text-rose-600">-{{ number_format($payroll->other_deductions, 2) }} DH</span>
                </div>
                @endif
                <div class="flex items-center justify-between px-5 py-2.5 bg-rose-50">
                    <span class="text-sm font-bold text-rose-700">{{ __('app.total_deductions') }}</span>
                    <span class="text-sm font-bold text-rose-600">-{{ number_format($payroll->deductions, 2) }} DH</span>
                </div>

                {{-- Net --}}
                <div class="flex items-center justify-between px-5 py-4 bg-indigo-50">
                    <span class="text-base font-black text-indigo-800">{{ __('app.net_salary') }}</span>
                    <span class="text-xl font-black text-indigo-700">{{ number_format($payroll->net_salary, 2) }} DH</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
@media print {
    nav, .btn, form { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
}
</style>
@endpush
