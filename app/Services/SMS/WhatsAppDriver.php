<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Http;

class WhatsAppDriver implements SmsDriverInterface
{
    private ?string $error = null;

    public function __construct(
        private string $token,
        private string $phoneId,
    ) {}

    public function send(string $to, string $message): bool
    {
        // Strip leading + for WhatsApp
        $to = ltrim($to, '+');

        try {
            $response = Http::withToken($this->token)
                ->post("https://graph.facebook.com/v18.0/{$this->phoneId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to'                => $to,
                    'type'              => 'text',
                    'text'              => ['preview_url' => false, 'body' => $message],
                ]);

            if ($response->failed()) {
                $this->error = $response->body();
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function lastError(): ?string
    {
        return $this->error;
    }
}
