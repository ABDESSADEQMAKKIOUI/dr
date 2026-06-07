@extends('layouts.app')
@section('title', __('app.generate_payroll') ?? 'Generate Payroll')
@php
$pageTitle = __('app.generate_payroll') ?? 'Generate Payroll';
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.employees'),  'url' => route('employees.index')],
    ['label' => __('app.payroll'),    'url' => route('employees.payroll.index')],
    ['label' => __('app.generate') ?? 'Generate', 'url' => ''],
];
@endphp

@section('content')
<div class="max-w-4xl">
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="card-title">{{ __('app.generate_payroll') ?? 'Generate Payroll' }}</h3>
            </div>
        </div>

        <form method="POST" action="{{ route('employees.payroll.store') }}" data-validate>
            @csrf
            <div class="p-6 space-y-6">

                @if($errors->any())
                <div class="px-4 py-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-sm">
                    {{ $errors->first() }}
                </div>
                @endif

                {{-- Employee + period --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="form-group md:col-span-1">
                        <label class="form-label">{{ __('app.employee') }} *</label>
                        <select name="employee_id" id="employee-select" class="form-control" required>
                            <option value="">{{ __('app.select_employee') }}</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}"
                                    data-salary="{{ $emp->salary }}"
                                    {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->user->full_name ?? '—' }}
                                @if($emp->designation) — {{ $emp->designation->name }} @endif
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('app.month') ?? 'Month' }} *</label>
                        <select name="month" class="form-control" required>
                            @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ old('month', date('n')) == $i ? 'selected' : '' }}>
                                {{ date('F', mktime(0,0,0,$i,1)) }}
                            </option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('app.year') ?? 'Year' }} *</label>
                        <select name="year" class="form-control" required>
                            @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                            <option value="{{ $y }}" {{ old('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('app.basic_salary') }} *</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="basic_salary" id="basic-salary"
                               value="{{ old('basic_salary') }}"
                               class="form-control pr-12" required>
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">DH</span>
                    </div>
                </div>

                {{-- Allowances --}}
                <div class="border border-emerald-200 rounded-xl overflow-hidden">
                    <div class="bg-emerald-50 px-5 py-3">
                        <h4 class="text-sm font-bold text-emerald-700 uppercase tracking-wide">{{ __('app.allowances') }}</h4>
                    </div>
                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach([
                            'housing_allowance'   => __('app.housing_allowance'),
                            'transport_allowance' => __('app.transport_allowance'),
                            'overtime'            => __('app.overtime_pay'),
                            'bonus'               => __('app.bonus'),
                        ] as $field => $label)
                        <div class="form-group">
                            <label class="form-label">{{ $label }}</label>
                            <div class="relative">
                                <input type="number" step="0.01" min="0" name="{{ $field }}"
                                       value="{{ old($field, 0) }}"
                                       class="form-control pr-10 allowance">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-emerald-400 text-xs font-bold">DH</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Deductions --}}
                <div class="border border-rose-200 rounded-xl overflow-hidden">
                    <div class="bg-rose-50 px-5 py-3">
                        <h4 class="text-sm font-bold text-rose-700 uppercase tracking-wide">{{ __('app.deductions') }}</h4>
                    </div>
                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach([
                            'tax'              => __('app.tax'),
                            'social_security'  => __('app.social_security'),
                            'insurance'        => __('app.insurance'),
                            'other_deductions' => __('app.other_deductions'),
                        ] as $field => $label)
                        <div class="form-group">
                            <label class="form-label">{{ $label }}</label>
                            <div class="relative">
                                <input type="number" step="0.01" min="0" name="{{ $field }}"
                                       value="{{ old($field, 0) }}"
                                       class="form-control pr-10 deduction">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-rose-400 text-xs font-bold">DH</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Live summary --}}
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-5 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">{{ __('app.basic_salary') }}</span>
                        <span id="display-basic" class="font-semibold text-slate-700">0.00 DH</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-emerald-600">{{ __('app.total_allowances') }}</span>
                        <span id="total-allowances" class="font-semibold text-emerald-600">+0.00 DH</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-rose-600">{{ __('app.total_deductions') }}</span>
                        <span id="total-deductions" class="font-semibold text-rose-600">-0.00 DH</span>
                    </div>
                    <div class="flex justify-between text-xl font-black border-t border-slate-200 pt-3 mt-3">
                        <span class="text-slate-800">{{ __('app.net_salary') }}</span>
                        <span id="net-salary" class="text-indigo-700">0.00 DH</span>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="form-group">
                    <label class="form-label">{{ __('app.notes') }}</label>
                    <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                </div>

            </div>
            <div class="flex justify-end gap-3 px-6 pb-6">
                <a href="{{ route('employees.payroll.index') }}" class="btn btn-outline">{{ __('app.cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('app.generate_payroll') ?? 'Generate' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('employee-select').addEventListener('change', function () {
    const salary = this.options[this.selectedIndex].dataset.salary || 0;
    document.getElementById('basic-salary').value = parseFloat(salary).toFixed(2);
    calculate();
});
document.getElementById('basic-salary').addEventListener('input', calculate);
document.querySelectorAll('.allowance, .deduction').forEach(el => el.addEventListener('input', calculate));

function calculate() {
    const basic = parseFloat(document.getElementById('basic-salary').value) || 0;
    let allowances = 0, deductions = 0;
    document.querySelectorAll('.allowance').forEach(el => allowances += parseFloat(el.value) || 0);
    document.querySelectorAll('.deduction').forEach(el => deductions += parseFloat(el.value) || 0);
    const net = basic + allowances - deductions;
    document.getElementById('display-basic').textContent    = basic.toFixed(2) + ' DH';
    document.getElementById('total-allowances').textContent = '+' + allowances.toFixed(2) + ' DH';
    document.getElementById('total-deductions').textContent = '-' + deductions.toFixed(2) + ' DH';
    document.getElementById('net-salary').textContent       = net.toFixed(2) + ' DH';
}
</script>
@endpush
