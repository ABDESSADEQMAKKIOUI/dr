<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['journal_id', 'account_id', 'date', 'reference', 'description', 'debit', 'credit', 'source_type', 'source_id', 'user_id'];

    protected function casts(): array
    {
        return ['date' => 'date', 'debit' => 'decimal:2', 'credit' => 'decimal:2'];
    }

    public function journal() { return $this->belongsTo(Journal::class); }
    public function account() { return $this->belongsTo(Account::class); }
    public function source() { return $this->morphTo(); }
    public function user() { return $this->belongsTo(User::class); }
}
