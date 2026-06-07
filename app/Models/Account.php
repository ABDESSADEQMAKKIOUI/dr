<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_type_id', 'name', 'code', 'parent_id', 'description', 'is_active',
        'pcg_class', 'normal_balance', 'opening_balance', 'currency',
        'acquisition_date', 'amortization_rate', 'useful_life_years',
        'valuation_method', 'vat_rate', 'payment_terms_days',
        'bank_name', 'iban', 'budget_amount', 'vat_deductible',
    ];

    protected function casts(): array
    {
        return [
            'is_active'      => 'boolean',
            'vat_deductible' => 'boolean',
            'acquisition_date' => 'date',
        ];
    }

    public function accountType() { return $this->belongsTo(AccountType::class); }
    public function parent() { return $this->belongsTo(Account::class, 'parent_id'); }
    public function children() { return $this->hasMany(Account::class, 'parent_id'); }
    public function transactions() { return $this->hasMany(Transaction::class); }
}
