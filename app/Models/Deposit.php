<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $fillable = [
        'amount', 'category_id', 'account_id', 'reference',
        'date', 'payment_method', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function category()
    {
        return $this->belongsTo(DepositCategory::class, 'category_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
