<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Http;

class TermiiDriver implements SmsDriverInterface
{
    private ?string $error = null;

    public function __construct(
        private string $apiKey,
        private string $senderId,
    ) {}

    public function send(string $to, string $message): bool
    {
        try {
            $response = Http::post('https://api.ng.termii.com/api/sms/send', [
                'to'      => $to,
                'from'    => $this->senderId,
                'sms'     => $message,
                'type'    => 'plain',
                'channel' => 'generic',
                'api_key' => $this->apiKey,
            ]);

            if ($response->failed() || isset($response->json()['code']) && $response->json()['code'] !== 'ok') {
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
