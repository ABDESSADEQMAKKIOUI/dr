<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'month', 'year', 'period_start', 'period_end',
        'basic_salary', 'allowances', 'deductions', 'net_salary',
        'housing_allowance', 'transport_allowance', 'overtime', 'bonus',
        'tax', 'social_security', 'insurance', 'other_deductions',
        'notes', 'status', 'paid_at', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date', 'period_end' => 'date',
            'basic_salary' => 'decimal:2', 'allowances' => 'decimal:2',
            'deductions' => 'decimal:2', 'net_salary' => 'decimal:2',
            'housing_allowance' => 'decimal:2', 'transport_allowance' => 'decimal:2',
            'overtime' => 'decimal:2', 'bonus' => 'decimal:2',
            'tax' => 'decimal:2', 'social_security' => 'decimal:2',
            'insurance' => 'decimal:2', 'other_deductions' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function employee() { return $this->belongsTo(Employee::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(PayrollItem::class); }
}
