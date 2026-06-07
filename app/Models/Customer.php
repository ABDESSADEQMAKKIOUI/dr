<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'tax_number',
        'customer_group_id',
        'address',
        'city',
        'postal_code',
        'country',
        'credit_limit',
        'loyalty_points',
        'is_active',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'loyalty_points' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }

    public function contacts()
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function getTotalPurchasesAttribute()
    {
        return $this->sales()->where('payment_status', 'paid')->sum('total_amount');
    }

    public function getBalanceAttribute()
    {
        $totalSales = $this->sales()->sum('total_amount');
        $totalPaid = $this->sales()->sum('paid_amount');
        return $totalSales - $totalPaid;
    }
}
