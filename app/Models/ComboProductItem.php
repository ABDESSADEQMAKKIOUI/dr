<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComboProductItem extends Model
{
    public $timestamps = false;

    protected $fillable = ['combo_id', 'product_id', 'quantity'];

    public function combo()
    {
        return $this->belongsTo(ComboProduct::class, 'combo_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
