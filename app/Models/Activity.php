<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = ['subject_type', 'subject_id', 'type', 'title', 'description', 'due_date', 'status', 'assigned_to'];

    protected function casts(): array
    {
        return ['due_date' => 'datetime'];
    }

    public function subject() { return $this->morphTo(); }
    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }
}
