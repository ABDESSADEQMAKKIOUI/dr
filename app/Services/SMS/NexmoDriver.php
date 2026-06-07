<?php

namespace App\Services\SMS;

use Vonage\Client as VonageClient;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;

class NexmoDriver implements SmsDriverInterface
{
    private ?string $error = null;

    public function __construct(
        private string $key,
        private string $secret,
        private string $from,
    ) {}

    public function send(string $to, string $message): bool
    {
        try {
            $credentials = new Basic($this->key, $this->secret);
            $client      = new VonageClient($credentials);
            $client->sms()->send(new SMS($to, $this->from, $message));
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
