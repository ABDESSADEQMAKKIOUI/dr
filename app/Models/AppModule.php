<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppModule extends Model
{
    protected $table = 'modules';

    protected $fillable = ['key', 'name', 'is_enabled'];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean'];
    }

    public static function isEnabled(string $key): bool
    {
        return Cache::remember("module_{$key}", 300, function () use ($key) {
            return static::where('key', $key)->value('is_enabled') ?? true;
        });
    }

    public static function defaultModules(): array
    {
        return [
            ['key' => 'pos',               'name' => 'Point of Sale'],
            ['key' => 'hrm',               'name' => 'HR Management'],
            ['key' => 'accounting',        'name' => 'Accounting'],
            ['key' => 'projects',          'name' => 'Projects & Tasks'],
            ['key' => 'shipments',         'name' => 'Shipments & Delivery'],
            ['key' => 'recurring_invoices','name' => 'Recurring Invoices'],
            ['key' => 'warranties',        'name' => 'Warranties'],
            ['key' => 'combo_products',    'name' => 'Combo / Bundle Products'],
            ['key' => 'deposits',          'name' => 'Deposits & Transfers'],
            ['key' => 'sms',               'name' => 'SMS Notifications'],
        ];
    }
}
