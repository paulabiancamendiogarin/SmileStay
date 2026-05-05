<?php

class TotpService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function generateSecret(int $length = 32): string
    {
        $bytes = random_bytes((int) ceil($length * 5 / 8));
        $base32 = self::base32Encode($bytes);

        return substr($base32, 0, $length);
    }

    public static function buildProvisioningUri(string $email, string $secret, string $issuer): string
    {
        $label = rawurlencode($issuer . ':' . $email);
        $issuerParam = rawurlencode($issuer);

        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuerParam}&algorithm=SHA1&digits=6&period=30";
    }

    public static function verifyCode(string $secret, string $code, int $window = 1): bool
    {
        if (!preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $timeSlice = (int) floor(time() / 30);
        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals(self::generateCode($secret, $timeSlice + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    private static function generateCode(string $secret, int $timeSlice): string
    {
        $key = self::base32Decode($secret);
        if ($key === '') {
            return '000000';
        }

        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $key, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        $otp = $binary % 1000000;
        return str_pad((string) $otp, 6, '0', STR_PAD_LEFT);
    }

    private static function base32Encode(string $data): string
    {
        $binaryString = '';
        $dataLength = strlen($data);
        for ($i = 0; $i < $dataLength; $i++) {
            $binaryString .= str_pad(decbin(ord($data[$i])), 8, '0', STR_PAD_LEFT);
        }

        $chunks = str_split($binaryString, 5);
        $output = '';
        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }
            $output .= self::BASE32_ALPHABET[bindec($chunk)];
        }

        return $output;
    }

    private static function base32Decode(string $secret): string
    {
        $secret = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret));
        if ($secret === '') {
            return '';
        }

        $binaryString = '';
        $secretLength = strlen($secret);
        for ($i = 0; $i < $secretLength; $i++) {
            $index = strpos(self::BASE32_ALPHABET, $secret[$i]);
            if ($index === false) {
                return '';
            }
            $binaryString .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }

        $bytes = str_split($binaryString, 8);
        $output = '';
        foreach ($bytes as $byte) {
            if (strlen($byte) === 8) {
                $output .= chr(bindec($byte));
            }
        }

        return $output;
    }
}
