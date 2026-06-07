<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    protected $fillable = ['event', 'label', 'body', 'variables_hint', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /**
     * Seed default templates if none exist.
     */
    public static function seedDefaults(): void
    {
        $defaults = [
            ['event' => 'sale_created',      'label' => 'Sale Created',      'body' => 'Hi {customer_name}, your order #{reference} for {total} DH has been placed on {date}. Thank you!', 'variables_hint' => '{customer_name}, {reference}, {total}, {date}'],
            ['event' => 'purchase_created',  'label' => 'Purchase Created',  'body' => 'Purchase order #{reference} for {total} DH has been created on {date}.', 'variables_hint' => '{reference}, {total}, {date}'],
            ['event' => 'quotation_created', 'label' => 'Quotation Created', 'body' => 'Hi {customer_name}, your quotation #{reference} for {total} DH is ready. Valid until {date}.', 'variables_hint' => '{customer_name}, {reference}, {total}, {date}'],
            ['event' => 'payment_received',  'label' => 'Payment Received',  'body' => 'Hi {customer_name}, we received your payment of {total} DH for #{reference}. Thank you!', 'variables_hint' => '{customer_name}, {reference}, {total}, {date}'],
            ['event' => 'sale_return',       'label' => 'Sale Return',       'body' => 'Hi {customer_name}, your return for #{reference} has been processed. Amount: {total} DH.', 'variables_hint' => '{customer_name}, {reference}, {total}, {date}'],
            ['event' => 'purchase_return',   'label' => 'Purchase Return',   'body' => 'Purchase return for #{reference} ({total} DH) has been processed on {date}.', 'variables_hint' => '{reference}, {total}, {date}'],
        ];

        foreach ($defaults as $data) {
            self::firstOrCreate(['event' => $data['event']], $data);
        }
    }
}
