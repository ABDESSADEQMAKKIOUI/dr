<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $employees = Employee::with(['user', 'department'])->paginate(15);
        return view('employees.index', compact('employees'));
    }

    public function create(): View
    {
        $users = \App\Models\User::whereDoesntHave('employee')->get();
        $departments = \App\Models\Department::all();
        $designations = \App\Models\Designation::all();
        return view('employees.create', compact('users', 'departments', 'designations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:employees',
            'employee_code' => 'nullable|string|max:50|unique:employees',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'hire_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        // Generate employee code if not provided
        if (empty($validated['employee_code'])) {
            $validated['employee_code'] = 'EMP-' . str_pad(Employee::count() + 1, 5, '0', STR_PAD_LEFT);
        }

        Employee::create($validated);
        return redirect()->route('employees.index')->with('success', __('app.created_success'));
    }

    public function show(Employee $employee): View
    {
        $employee->load('user', 'department', 'designation');
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        $employee->load('user', 'department', 'designation');
        $users = \App\Models\User::whereDoesntHave('employee')->orWhere('id', $employee->user_id)->get();
        $departments = \App\Models\Department::all();
        $designations = \App\Models\Designation::all();
        return view('employees.edit', compact('employee', 'users', 'departments', 'designations'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:employees,user_id,' . $employee->id,
            'employee_code' => 'nullable|string|max:50|unique:employees,employee_code,' . $employee->id,
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'hire_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $employee->update($validated);
        return redirect()->route('employees.index')->with('success', __('app.updated_success'));
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success', __('app.deleted_success'));
    }

    public function attendance(): View
    {
        return view('employees.attendance.index');
    }

    public function payroll(): View
    {
        return view('employees.payroll.index');
    }
}
