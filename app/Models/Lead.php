<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
   use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'email', 'phone', 'source', 'status', 'assigned_to', 'notes'];

    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function activities() { return $this->morphMany(Activity::class, 'subject'); }
}
