<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['customer_id', 'title', 'value', 'probability', 'stage', 'expected_close_date', 'assigned_to', 'notes'];

    protected function casts(): array
    {
        return ['value' => 'decimal:2', 'probability' => 'integer', 'expected_close_date' => 'date'];
    }

    public function customer() { return $this->belongsTo(Customer::class); }
    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function activities() { return $this->morphMany(Activity::class, 'subject'); }
}
