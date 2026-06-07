<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference', 'supplier_id', 'warehouse_id', 'user_id', 'date', 'status',
        'tax_amount', 'discount_amount', 'shipping_cost', 'total_amount', 
        'paid_amount', 'payment_status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($purchase) {
            if (empty($purchase->reference)) {
                $purchase->reference = 'PO-' . date('Ymd') . '-' . str_pad(Purchase::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(PurchaseItem::class); }
    public function payments() { return $this->hasMany(PurchasePayment::class); }
    public function returns() { return $this->hasMany(PurchaseReturn::class); }
    
    public function getDueAmountAttribute() { return $this->total_amount - $this->paid_amount; }
}
