<?php

namespace App\Services\SMS;

interface SmsDriverInterface
{
    /**
     * Send an SMS message.
     *
     * @param  string  $to      International phone number, e.g. +212612345678
     * @param  string  $message Plain-text message body
     * @return bool    true on success, false on failure
     */
    public function send(string $to, string $message): bool;

    /**
     * Return the last error string after a failed send.
     */
    public function lastError(): ?string;
}
