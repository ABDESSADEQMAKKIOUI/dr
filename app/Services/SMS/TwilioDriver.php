<?php

namespace App\Services\SMS;

use Twilio\Rest\Client as TwilioClient;

class TwilioDriver implements SmsDriverInterface
{
    private ?string $error = null;

    public function __construct(
        private string $sid,
        private string $token,
        private string $from,
    ) {}

    public function send(string $to, string $message): bool
    {
        try {
            $client = new TwilioClient($this->sid, $this->token);
            $client->messages->create($to, ['from' => $this->from, 'body' => $message]);
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
