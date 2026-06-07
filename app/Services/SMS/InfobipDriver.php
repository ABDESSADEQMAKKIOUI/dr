<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Http;

class InfobipDriver implements SmsDriverInterface
{
    private ?string $error = null;

    public function __construct(
        private string $apiKey,
        private string $baseUrl,
        private string $from,
    ) {}

    public function send(string $to, string $message): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'App ' . $this->apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->post(rtrim($this->baseUrl, '/') . '/sms/2/text/advanced', [
                'messages' => [[
                    'from'         => $this->from,
                    'destinations' => [['to' => $to]],
                    'text'         => $message,
                ]],
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
