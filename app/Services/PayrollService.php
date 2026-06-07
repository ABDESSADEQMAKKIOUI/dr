<?php

namespace App\Services;

use App\Models\Payroll;
use App\Models\Employee;

class PayrollService
{
    public function create(array $data): Payroll
    {
        $payroll = Payroll::create([
            'employee_id' => $data['employee_id'],
            'month' => $data['month'],
            'basic_salary' => $data['basic_salary'],
            'allowances' => $data['allowances'] ?? 0,
            'bonuses' => $data['bonuses'] ?? 0,
            'deductions' => $data['deductions'] ?? 0,
            'net_salary' => $data['net_salary'],
            'status' => $data['status'] ?? 'draft',
        ]);

        // Créer les items de paie
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $payroll->items()->create($item);
            }
        }

        return $payroll->fresh('items');
    }

    public function calculatePayroll(Employee $employee, string $month): array
    {
        $basicSalary = $employee->salary;
        $allowances = 0; // À calculer selon règles
        $bonuses = 0;
        $deductions = 0;

        $netSalary = $basicSalary + $allowances + $bonuses - $deductions;

        return [
            'employee_id' => $employee->id,
            'month' => $month,
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'bonuses' => $bonuses,
            'deductions' => $deductions,
            'net_salary' => $netSalary,
        ];
    }

    public function approve(Payroll $payroll): Payroll
    {
        $payroll->update(['status' => 'approved']);
        return $payroll;
    }

    public function pay(Payroll $payroll): Payroll
    {
        $payroll->update(['status' => 'paid', 'paid_at' => now()]);
        return $payroll;
    }
}
