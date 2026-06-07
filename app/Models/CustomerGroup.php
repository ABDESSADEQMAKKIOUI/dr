<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'discount_percentage',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'discount_percentage' => 'decimal:2',
        ];
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
