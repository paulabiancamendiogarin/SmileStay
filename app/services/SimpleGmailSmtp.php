<?php

/**
 * Lightweight SMTP sender for Gmail (STARTTLS :587).
 * Used when PHPMailer is not installed (run composer install for PHPMailer).
 */
class SimpleGmailSmtp
{
    private $socket;

    private function readLines(): array
    {
        $lines = [];
        while (($line = fgets($this->socket)) !== false) {
            $lines[] = $line;
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        return $lines;
    }

    private function expect250(string $context): void
    {
        $lines = $this->readLines();
        $first = $lines[0] ?? '';
        if (strlen($first) < 3 || substr($first, 0, 3) !== '250') {
            throw new RuntimeException('SMTP error (' . $context . '): ' . trim(implode('', $lines)));
        }
    }

    private function expect354(string $context): void
    {
        $lines = $this->readLines();
        $first = $lines[0] ?? '';
        if (strlen($first) < 3 || substr($first, 0, 3) !== '354') {
            throw new RuntimeException('SMTP error (' . $context . '): ' . trim(implode('', $lines)));
        }
    }

    private function writeLine(string $data): void
    {
        fwrite($this->socket, $data . "\r\n");
    }

    public static function sendMessage(string $to, string $subject, string $bodyText): bool
    {
        if (empty(SMTP_USERNAME) || SMTP_PASSWORD === '' || SMTP_PASSWORD === 'your_app_password') {
            return false;
        }

        $smtp = new self();
        try {
            $smtp->sendSession($to, $subject, $bodyText);
            return true;
        } catch (Throwable $e) {
            error_log('[Mail] ' . $e->getMessage());
            return false;
        }
    }

    private function sendSession(string $to, string $subject, string $bodyText): void
    {
        $host = SMTP_HOST;
        $port = (int) SMTP_PORT;

        $ctx = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ],
        ]);

        $this->socket = @stream_socket_client(
            'tcp://' . $host . ':' . $port,
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $ctx
        );

        if (!$this->socket) {
            throw new RuntimeException("SMTP connect failed: $errstr ($errno)");
        }

        stream_set_timeout($this->socket, 30);

        $this->readLines(); // greeting

        $ehlo = function_exists('gethostname') ? gethostname() : 'localhost';
        $this->writeLine('EHLO ' . $ehlo);
        $this->expect250('EHLO');

        $this->writeLine('STARTTLS');
        $lines = $this->readLines();
        $first = $lines[0] ?? '';
        if (strlen($first) < 3 || substr($first, 0, 3) !== '220') {
            throw new RuntimeException('STARTTLS failed: ' . trim(implode('', $lines)));
        }

        if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new RuntimeException('TLS negotiation failed');
        }

        $this->writeLine('EHLO ' . $ehlo);
        $this->expect250('EHLO post-TLS');

        $this->writeLine('AUTH LOGIN');
        $lines = $this->readLines();
        $first = $lines[0] ?? '';
        if (strlen($first) < 3 || substr($first, 0, 3) !== '334') {
            throw new RuntimeException('AUTH LOGIN failed: ' . trim(implode('', $lines)));
        }

        $this->writeLine(base64_encode(SMTP_USERNAME));
        $this->readLines();

        $this->writeLine(base64_encode(SMTP_PASSWORD));
        $lines = $this->readLines();
        $first = $lines[0] ?? '';
        if (strlen($first) < 3 || substr($first, 0, 3) !== '235') {
            throw new RuntimeException('SMTP authentication failed');
        }

        $from = MAIL_FROM_EMAIL;
        $this->writeLine('MAIL FROM:<' . $from . '>');
        $this->expect250('MAIL FROM');

        $this->writeLine('RCPT TO:<' . $to . '>');
        $this->expect250('RCPT TO');

        $this->writeLine('DATA');
        $this->expect354('DATA');

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $headers = [
            'From: ' . MAIL_FROM_NAME . ' <' . $from . '>',
            'To: <' . $to . '>',
            'Subject: ' . $encodedSubject,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];

        $message = implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n.", "\n..", $bodyText);
        fwrite($this->socket, $message . "\r\n.\r\n");

        $lines = $this->readLines();
        $first = $lines[0] ?? '';
        if (strlen($first) < 3 || substr($first, 0, 3) !== '250') {
            throw new RuntimeException('Message rejected: ' . trim(implode('', $lines)));
        }

        $this->writeLine('QUIT');
        fclose($this->socket);
        $this->socket = null;
    }
}
