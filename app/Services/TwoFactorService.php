<?php

namespace App\Services;

use App\Models\User;

/**
 * Pure-PHP TOTP (RFC 6238) two-factor authentication service.
 * No external packages required.
 */
class TwoFactorService
{
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    private const PERIOD       = 30;
    private const DIGITS       = 6;

    /** Generate a 16-character base32 secret. */
    public function generateSecret(): string
    {
        $bytes  = random_bytes(10);
        $result = '';
        $buffer = 0;
        $bufLen = 0;

        foreach (str_split($bytes) as $byte) {
            $buffer = ($buffer << 8) | ord($byte);
            $bufLen += 8;
            while ($bufLen >= 5) {
                $bufLen -= 5;
                $result .= self::BASE32_CHARS[($buffer >> $bufLen) & 0x1F];
            }
        }

        if ($bufLen > 0) {
            $result .= self::BASE32_CHARS[($buffer << (5 - $bufLen)) & 0x1F];
        }

        return str_pad($result, 16, 'A');
    }

    /** Build an otpauth:// URI for QR code generation. */
    public function getQrUri(User $user, string $secret): string
    {
        $issuer  = rawurlencode(config('app.name', 'ERP'));
        $account = rawurlencode($user->email);

        return "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }

    /** Build an inline QR code as an SVG data URI (uses Google Charts API for QR rendering). */
    public function getQrDataUri(string $otpauthUri): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . rawurlencode($otpauthUri);
    }

    /** Verify a 6-digit code against the secret (allows ±1 time window). */
    public function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code);

        if (strlen($code) !== self::DIGITS || !ctype_digit($code)) {
            return false;
        }

        $timestamp = (int) floor(time() / self::PERIOD);

        foreach ([-1, 0, 1] as $offset) {
            if ($this->computeOtp($secret, $timestamp + $offset) === $code) {
                return true;
            }
        }

        return false;
    }

    /** Compute a TOTP code for a given time counter. */
    private function computeOtp(string $secret, int $counter): string
    {
        $key   = $this->base32Decode($secret);
        $msg   = pack('N*', 0) . pack('N*', $counter);
        $hash  = hash_hmac('sha1', $msg, $key, true);
        $offset = ord($hash[19]) & 0xF;
        $code   = (
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8)  |
             (ord($hash[$offset + 3]) & 0xFF)
        ) % (10 ** self::DIGITS);

        return str_pad((string) $code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /** Decode a base32 string to binary. */
    private function base32Decode(string $input): string
    {
        $input  = strtoupper(preg_replace('/\s+/', '', $input));
        $buffer = 0;
        $bufLen = 0;
        $output = '';

        foreach (str_split($input) as $char) {
            $pos = strpos(self::BASE32_CHARS, $char);
            if ($pos === false) {
                continue;
            }
            $buffer = ($buffer << 5) | $pos;
            $bufLen += 5;
            if ($bufLen >= 8) {
                $bufLen -= 8;
                $output .= chr(($buffer >> $bufLen) & 0xFF);
            }
        }

        return $output;
    }

    /** Generate 8 one-time backup recovery codes. */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = implode('-', [
                strtoupper(bin2hex(random_bytes(3))),
                strtoupper(bin2hex(random_bytes(3))),
            ]);
        }

        return $codes;
    }

    /** Enable 2FA on the user after successful verification. */
    public function enable(User $user, string $secret): void
    {
        $user->update([
            'two_factor_secret'         => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($this->generateRecoveryCodes())),
        ]);
    }

    /** Disable 2FA for the user. */
    public function disable(User $user): void
    {
        $user->update([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
        ]);
    }

    /** Check if 2FA is enabled for the user. */
    public function isEnabled(User $user): bool
    {
        return !empty($user->two_factor_secret);
    }

    /** Get decrypted secret for the user. */
    public function getSecret(User $user): ?string
    {
        return $user->two_factor_secret ? decrypt($user->two_factor_secret) : null;
    }

    /** Get decrypted recovery codes. */
    public function getRecoveryCodes(User $user): array
    {
        if (!$user->two_factor_recovery_codes) {
            return [];
        }

        return json_decode(decrypt($user->two_factor_recovery_codes), true) ?? [];
    }
}
