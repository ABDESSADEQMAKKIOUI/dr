<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComboProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'sku', 'price', 'image', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function items()
    {
        return $this->hasMany(ComboProductItem::class, 'combo_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'combo_product_items', 'combo_id', 'product_id')
            ->withPivot('quantity');
    }
}
