<?php

namespace App\Http\Controllers;

use App\Services\PayrollService;
use App\Models\Payroll;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(protected PayrollService $payrollService) {}

    public function index(Request $request)
    {
        $payrolls = Payroll::with('employee.user')
            ->latest()
            ->paginate(15);
        return response()->json($payrolls);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|date_format:Y-m',
            'basic_salary' => 'required|numeric|min:0',
            'net_salary' => 'required|numeric|min:0',
        ]);

        $payroll = $this->payrollService->create($validated);
        return response()->json($payroll, 201);
    }

    public function show(Payroll $payroll)
    {
        return response()->json($payroll->load('employee.user', 'items'));
    }

    public function approve(Payroll $payroll)
    {
        $payroll = $this->payrollService->approve($payroll);
        return response()->json($payroll);
    }

    public function pay(Payroll $payroll)
    {
        $payroll = $this->payrollService->pay($payroll);
        return response()->json($payroll);
    }
}
