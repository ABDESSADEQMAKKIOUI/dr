<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'type'];

    public function transactions() { return $this->hasMany(Transaction::class); }
}
