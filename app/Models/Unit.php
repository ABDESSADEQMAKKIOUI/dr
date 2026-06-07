<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'short_name',
        'operator',
        'operation_value',
    ];

    protected function casts(): array
    {
        return [
            'operation_value' => 'decimal:4',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function saleProducts()
    {
        return $this->hasMany(Product::class, 'sale_unit_id');
    }

    public function purchaseProducts()
    {
        return $this->hasMany(Product::class, 'purchase_unit_id');
    }
}
