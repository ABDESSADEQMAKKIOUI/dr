<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringInvoice extends Model
{
    protected $fillable = [
        'customer_id', 'items', 'total', 'frequency', 'next_run_at',
        'last_run_at', 'status', 'send_email', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'total' => 'decimal:2',
            'next_run_at' => 'date',
            'last_run_at' => 'date',
            'send_email' => 'boolean',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function advanceNextRun(): void
    {
        $this->last_run_at = now()->toDateString();
        $this->next_run_at = match($this->frequency) {
            'daily'   => now()->addDay()->toDateString(),
            'weekly'  => now()->addWeek()->toDateString(),
            'monthly' => now()->addMonth()->toDateString(),
            'yearly'  => now()->addYear()->toDateString(),
        };
        $this->save();
    }
}
