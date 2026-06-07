<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'reference', 'sale_id', 'customer_id', 'status', 'carrier',
        'tracking_number', 'estimated_delivery', 'delivered_at',
        'delivery_address', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'estimated_delivery' => 'date',
            'delivered_at' => 'date',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($shipment) {
            if (empty($shipment->reference)) {
                $shipment->reference = 'SHP-' . date('Ymd') . '-' . str_pad(
                    static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT
                );
            }
        });
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
