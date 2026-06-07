<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CardPayment extends Model
{
    protected $fillable = [
        'payable_type', 'payable_id',
        'customer_id',
        'stripe_payment_intent_id',
        'stripe_payment_method_id',
        'amount', 'currency', 'status',
        'card_last4', 'card_brand',
        'error_message',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
