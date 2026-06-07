<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_id', 'payment_method_id', 'amount', 'date', 'notes', 'user_id'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'date' => 'date'];
    }

    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function paymentMethod() { return $this->belongsTo(PaymentMethod::class); }
    public function user() { return $this->belongsTo(User::class); }
}
