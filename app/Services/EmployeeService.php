<?php

namespace App\Services;

use App\Models\Employee;

class EmployeeService
{
    public function list(array $filters = [])
    {
        $query = Employee::with('user', 'department', 'designation');

        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): Employee
    {
        return Employee::create([
            'user_id' => $data['user_id'],
            'employee_code' => $data['employee_code'] ?? $this->generateCode(),
            'department_id' => $data['department_id'],
            'designation_id' => $data['designation_id'],
            'joining_date' => $data['joining_date'],
            'salary' => $data['salary'],
            'employment_type' => $data['employment_type'] ?? 'full-time',
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update([
            'department_id' => $data['department_id'],
            'designation_id' => $data['designation_id'],
            'salary' => $data['salary'],
            'employment_type' => $data['employment_type'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $employee->fresh();
    }

    public function delete(Employee $employee): bool
    {
        return $employee->delete();
    }

    protected function generateCode(): string
    {
        return 'EMP-' . str_pad(Employee::count() + 1, 5, '0', STR_PAD_LEFT);
    }
}
