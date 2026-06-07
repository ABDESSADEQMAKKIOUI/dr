<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeShift extends Model
{
    protected $fillable = ['name', 'start_time', 'end_time', 'late_after_minutes'];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'shift_id');
    }
}
