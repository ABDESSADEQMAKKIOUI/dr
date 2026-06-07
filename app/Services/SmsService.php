<?php

namespace App\Services;

use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Services\SMS\SmsDriverInterface;
use App\Services\SMS\TwilioDriver;
use App\Services\SMS\NexmoDriver;
use App\Services\SMS\InfobipDriver;
use App\Services\SMS\TermiiDriver;
use App\Services\SMS\WhatsAppDriver;
use Illuminate\Database\Eloquent\Model;

class SmsService
{
    private ?SmsDriverInterface $driver = null;
    private string $activeGateway = 'twilio';

    public function __construct()
    {
        $this->boot();
    }

    private function boot(): void
    {
        $settings = \App\Models\SmsSettings::first();
        if (!$settings || !$settings->sms_enabled) {
            return;
        }

        $this->activeGateway = $settings->gateway;
        $this->driver = match($settings->gateway) {
            'twilio'   => new TwilioDriver($settings->twilio_sid ?? '', $settings->twilio_token ?? '', $settings->twilio_from ?? ''),
            'nexmo'    => new NexmoDriver($settings->nexmo_key ?? '', $settings->nexmo_secret ?? '', $settings->nexmo_from ?? ''),
            'infobip'  => new InfobipDriver($settings->infobip_api_key ?? '', $settings->infobip_base_url ?? '', $settings->infobip_from ?? ''),
            'termii'   => new TermiiDriver($settings->termii_api_key ?? '', $settings->termii_sender_id ?? ''),
            'whatsapp' => new WhatsAppDriver($settings->whatsapp_token ?? '', $settings->whatsapp_phone_id ?? ''),
            default    => null,
        };
    }

    public function isEnabled(): bool
    {
        return $this->driver !== null;
    }

    /**
     * Send a raw SMS message.
     */
    public function send(string $to, string $message, string $event = '', ?Model $loggable = null): bool
    {
        if (!$this->driver) {
            return false;
        }

        $success = $this->driver->send($to, $message);

        SmsLog::create([
            'to'            => $to,
            'message'       => $message,
            'gateway'       => $this->activeGateway,
            'status'        => $success ? 'sent' : 'failed',
            'error'         => $success ? null : $this->driver->lastError(),
            'event'         => $event,
            'loggable_type' => $loggable ? get_class($loggable) : null,
            'loggable_id'   => $loggable?->id,
            'sent_at'       => $success ? now() : null,
        ]);

        return $success;
    }

    /**
     * Send an SMS using a named template, substituting variables.
     *
     * Variables available: {customer_name}, {total}, {reference}, {date}
     */
    public function sendForEvent(string $event, array $variables, string $to, ?Model $loggable = null): bool
    {
        $template = SmsTemplate::where('event', $event)->where('is_active', true)->first();
        if (!$template) {
            return false;
        }

        $body = $template->body;
        foreach ($variables as $key => $value) {
            $body = str_replace('{' . $key . '}', $value, $body);
        }

        return $this->send($to, $body, $event, $loggable);
    }

    /**
     * Fire event-based notification (checks setting toggle before sending).
     */
    public function notify(string $event, string $to, array $variables, ?Model $loggable = null): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $settings = \App\Models\SmsSettings::first();
        if (!$settings) {
            return;
        }

        $toggleMap = [
            'sale_created'      => 'notify_sale',
            'purchase_created'  => 'notify_purchase',
            'quotation_created' => 'notify_quotation',
            'payment_received'  => 'notify_payment',
            'sale_return'       => 'notify_sale_return',
            'purchase_return'   => 'notify_purchase_return',
        ];

        $toggle = $toggleMap[$event] ?? null;
        if ($toggle && !$settings->{$toggle}) {
            return;
        }

        $this->sendForEvent($event, $variables, $to, $loggable);
    }
}
