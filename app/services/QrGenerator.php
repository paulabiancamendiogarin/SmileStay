<?php

class QrGenerator
{
    public static function hasConfiguredQrPhPayload(): bool
    {
        return defined('GCASH_QRPH_PAYLOAD') && trim((string) GCASH_QRPH_PAYLOAD) !== '';
    }

    /**
     * Saves PNG under public/qrcodes/. Returns relative path from public/ e.g. qrcodes/booking_12.png
     */
    public static function savePng(string $content, string $baseFilename): ?string
    {
        $dir = PUBLIC_PATH . '/qrcodes/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseFilename) . '.png';
        $fullPath = $dir . $safeName;

        try {
            if (class_exists(\Endroid\QrCode\Builder\Builder::class)) {
                $result = \Endroid\QrCode\Builder\Builder::create()
                    ->writer(new \Endroid\QrCode\Writer\PngWriter())
                    ->data($content)
                    ->size(280)
                    ->margin(10)
                    ->build();
                $result->saveToFile($fullPath);
                return 'qrcodes/' . $safeName;
            }
        } catch (Throwable $e) {
            error_log('[QrGenerator] Endroid: ' . $e->getMessage());
        }

        $url = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&ecc=M&data=' . rawurlencode($content);
        $ctx = stream_context_create(['http' => ['timeout' => 15]]);
        $png = @file_get_contents($url, false, $ctx);
        if ($png !== false && strlen($png) > 100) {
            file_put_contents($fullPath, $png);
            return 'qrcodes/' . $safeName;
        }

        return null;
    }

    public static function gcashPayload(int $bookingId, float $amount, string $referenceCode): string
    {
        if (self::hasConfiguredQrPhPayload()) {
            // Use official merchant/static QRPh payload provided by GCash.
            return trim((string) GCASH_QRPH_PAYLOAD);
        }

        $num = GCASH_NUMBER;
        $amt = number_format($amount, 2);

        return "GCash Payment Request\nGCash Number: {$num}\nAmount: PHP {$amt}\nReference: {$referenceCode}\n\nScan with GCash. Include the reference when paying.";
    }
}
