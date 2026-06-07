<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference', 'expense_category_id', 'supplier_id', 'date', 'amount',
        'payment_method_id', 'description', 'attachment', 'is_recurring',
        'recurrence_period', 'user_id',
    ];

    protected function casts(): array
    {
        return ['date' => 'date', 'amount' => 'decimal:2', 'is_recurring' => 'boolean'];
    }

    public function category() { return $this->belongsTo(ExpenseCategory::class, 'expense_category_id'); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function paymentMethod() { return $this->belongsTo(PaymentMethod::class); }
    public function user() { return $this->belongsTo(User::class); }
}
