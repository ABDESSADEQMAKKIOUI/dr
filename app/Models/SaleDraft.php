<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDraft extends Model
{
    protected $fillable = [
        'user_id',
        'warehouse_id',
        'customer_id',
        'items',
        'discount',
        'notes',
    ];

    protected $casts = [
        'items'    => 'array',
        'discount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Total value of all items in the draft.
     */
    public function getTotalAttribute(): float
    {
        $subtotal = collect($this->items)->sum(fn($i) => ($i['price'] ?? 0) * ($i['quantity'] ?? 1));
        return round($subtotal - (float)$this->discount, 2);
    }
}
