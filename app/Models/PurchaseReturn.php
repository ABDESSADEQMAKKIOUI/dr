<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['reference', 'purchase_id', 'warehouse_id', 'date', 'reason', 'total_amount', 'notes', 'user_id', 'applied_at'];

    protected function casts(): array
    {
        return ['date' => 'date', 'total_amount' => 'decimal:2', 'applied_at' => 'datetime'];
    }

    public function isApplied(): bool
    {
        return $this->applied_at !== null;
    }

    public function purchase() { return $this->belongsTo(Purchase::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(PurchaseReturnItem::class); }
}
