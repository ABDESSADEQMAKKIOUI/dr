<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'is_rtl', 'is_active', 'is_default'];

    protected function casts(): array
    {
        return ['is_rtl' => 'boolean', 'is_active' => 'boolean', 'is_default' => 'boolean'];
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeDefault($query) { return $query->where('is_default', true); }
}
