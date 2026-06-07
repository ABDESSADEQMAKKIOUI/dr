<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class PayrollWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payroll::with(['employee.user', 'employee.department', 'employee.designation']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        } else {
            $query->where('year', date('Y'));
        }

        $payrolls = $query->latest()->paginate(20);
        $employees = Employee::with('user')->where('is_active', true)->get();

        $all = $query->get();
        $stats = [
            'total'     => $all->sum('net_salary'),
            'paid'      => $all->where('status', 'paid')->sum('net_salary'),
            'pending'   => $all->where('status', 'draft')->sum('net_salary'),
            'employees' => $all->pluck('employee_id')->unique()->count(),
        ];

        return view('employees.payroll.index', compact('payrolls', 'employees', 'stats'));
    }

    public function create(): View
    {
        $employees = Employee::with(['user', 'designation'])->where('is_active', true)->get();
        return view('employees.payroll.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'        => 'required|exists:employees,id',
            'month'              => 'required|integer|between:1,12',
            'year'               => 'required|integer|min:2000',
            'basic_salary'       => 'required|numeric|min:0',
            'housing_allowance'  => 'nullable|numeric|min:0',
            'transport_allowance'=> 'nullable|numeric|min:0',
            'overtime'           => 'nullable|numeric|min:0',
            'bonus'              => 'nullable|numeric|min:0',
            'tax'                => 'nullable|numeric|min:0',
            'social_security'    => 'nullable|numeric|min:0',
            'insurance'          => 'nullable|numeric|min:0',
            'other_deductions'   => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
        ]);

        // Prevent duplicate payroll for same employee/month/year
        if (Payroll::where('employee_id', $request->employee_id)
            ->where('month', $request->month)
            ->where('year', $request->year)->exists()) {
            return back()->withInput()->withErrors(['employee_id' => __('app.payroll_already_exists') ?? 'Payroll already generated for this period.']);
        }

        $allowances = ($request->housing_allowance ?? 0)
            + ($request->transport_allowance ?? 0)
            + ($request->overtime ?? 0)
            + ($request->bonus ?? 0);

        $deductions = ($request->tax ?? 0)
            + ($request->social_security ?? 0)
            + ($request->insurance ?? 0)
            + ($request->other_deductions ?? 0);

        $netSalary = $request->basic_salary + $allowances - $deductions;

        $periodStart = Carbon::createFromDate($request->year, $request->month, 1)->startOfMonth();
        $periodEnd   = $periodStart->copy()->endOfMonth();

        $payroll = Payroll::create([
            'employee_id'         => $request->employee_id,
            'month'               => $request->month,
            'year'                => $request->year,
            'period_start'        => $periodStart,
            'period_end'          => $periodEnd,
            'basic_salary'        => $request->basic_salary,
            'housing_allowance'   => $request->housing_allowance ?? 0,
            'transport_allowance' => $request->transport_allowance ?? 0,
            'overtime'            => $request->overtime ?? 0,
            'bonus'               => $request->bonus ?? 0,
            'allowances'          => $allowances,
            'tax'                 => $request->tax ?? 0,
            'social_security'     => $request->social_security ?? 0,
            'insurance'           => $request->insurance ?? 0,
            'other_deductions'    => $request->other_deductions ?? 0,
            'deductions'          => $deductions,
            'net_salary'          => $netSalary,
            'notes'               => $request->notes,
            'status'              => 'draft',
            'user_id'             => auth()->id(),
        ]);

        return redirect()->route('employees.payroll.show', $payroll->id)
            ->with('success', __('app.payroll_generated') ?? 'Payroll generated successfully.');
    }

    public function show(Payroll $payroll): View
    {
        $payroll->load(['employee.user', 'employee.department', 'employee.designation']);
        return view('employees.payroll.show', compact('payroll'));
    }

    public function markPaid(Payroll $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', __('app.already_paid') ?? 'Already marked as paid.');
        }

        $payroll->update(['status' => 'paid', 'paid_at' => now()]);

        // Accounting entry: Debit 641 Salaires, Credit 512 Banque
        $this->createAccountingEntry($payroll);

        return back()->with('success', __('app.payroll_marked_paid') ?? 'Payroll marked as paid.');
    }

    private function createAccountingEntry(Payroll $payroll): void
    {
        // Find salary expense account (class 6) and bank account (class 5)
        $salaryAccount = Account::where('code', 'like', '641%')->first()
            ?? Account::where('pcg_class', 6)->first();
        $bankAccount   = Account::where('code', 'like', '512%')->first()
            ?? Account::where('code', 'like', '530%')->first()
            ?? Account::where('pcg_class', 5)->first();

        if (! $salaryAccount || ! $bankAccount) {
            return; // Accounts not configured, skip silently
        }

        $ref = 'PAY-' . $payroll->id . '-' . $payroll->year . '-' . str_pad($payroll->month, 2, '0', STR_PAD_LEFT);
        $description = 'Salaire ' . ($payroll->employee->user->full_name ?? '')
            . ' — ' . date('F', mktime(0, 0, 0, $payroll->month, 1)) . ' ' . $payroll->year;

        // Debit salary expense
        \App\Models\Transaction::create([
            'account_id'  => $salaryAccount->id,
            'type'        => 'debit',
            'amount'      => $payroll->net_salary,
            'description' => $description,
            'reference'   => $ref,
            'date'        => now()->toDateString(),
            'user_id'     => auth()->id(),
        ]);

        // Credit bank
        \App\Models\Transaction::create([
            'account_id'  => $bankAccount->id,
            'type'        => 'credit',
            'amount'      => $payroll->net_salary,
            'description' => $description,
            'reference'   => $ref,
            'date'        => now()->toDateString(),
            'user_id'     => auth()->id(),
        ]);
    }
}
