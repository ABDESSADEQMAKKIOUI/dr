<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositCategory extends Model
{
    protected $fillable = ['name', 'description'];

    public function deposits()
    {
        return $this->hasMany(Deposit::class, 'category_id');
    }
}
