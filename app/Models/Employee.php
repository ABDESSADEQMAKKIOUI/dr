<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'employee_code', 'department_id', 'designation_id', 'hire_date', 'salary', 'commission_rate', 'is_active'];

    protected function casts(): array
    {
        return ['hire_date' => 'date', 'salary' => 'decimal:2', 'commission_rate' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function designation() { return $this->belongsTo(Designation::class); }
    public function attendances() { return $this->hasMany(Attendance::class); }
    public function leaves() { return $this->hasMany(Leave::class); }
    public function payrolls() { return $this->hasMany(Payroll::class); }
}
