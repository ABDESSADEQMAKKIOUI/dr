<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsSettings extends Model
{
    protected $table = 'sms_settings';

    protected $fillable = [
        'gateway',
        'twilio_sid', 'twilio_token', 'twilio_from',
        'nexmo_key', 'nexmo_secret', 'nexmo_from',
        'infobip_api_key', 'infobip_base_url', 'infobip_from',
        'termii_api_key', 'termii_sender_id',
        'whatsapp_token', 'whatsapp_phone_id',
        'notify_sale', 'notify_purchase', 'notify_quotation',
        'notify_payment', 'notify_sale_return', 'notify_purchase_return',
        'notify_whatsapp_sale', 'notify_whatsapp_purchase',
        'sms_enabled', 'whatsapp_enabled',
    ];

    protected $casts = [
        'notify_sale'             => 'boolean',
        'notify_purchase'         => 'boolean',
        'notify_quotation'        => 'boolean',
        'notify_payment'          => 'boolean',
        'notify_sale_return'      => 'boolean',
        'notify_purchase_return'  => 'boolean',
        'notify_whatsapp_sale'    => 'boolean',
        'notify_whatsapp_purchase'=> 'boolean',
        'sms_enabled'             => 'boolean',
        'whatsapp_enabled'        => 'boolean',
    ];
}
