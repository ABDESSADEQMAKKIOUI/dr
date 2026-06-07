<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchasePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['purchase_id', 'payment_method_id', 'amount', 'date', 'notes', 'user_id'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'date' => 'date'];
    }

    public function purchase() { return $this->belongsTo(Purchase::class); }
    public function paymentMethod() { return $this->belongsTo(PaymentMethod::class); }
    public function user() { return $this->belongsTo(User::class); }
}
