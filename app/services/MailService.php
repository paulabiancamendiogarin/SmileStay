<?php

class MailService
{
    public static function sendOtpEmail(string $to, string $otpCode): bool
    {
        if (SMTP_PASSWORD === 'your_app_password' || SMTP_PASSWORD === '') {
            return false;
        }

        $subject = APP_NAME . ' — verification code';
        $body = "Your verification code is: {$otpCode}\n\nThis will expire in " . OTP_EXPIRY_MINUTES . " minutes.";

        $autoload = BASE_PATH . '/vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }

        if (class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            try {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USERNAME;
                $mail->Password = SMTP_PASSWORD;
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = (int) SMTP_PORT;
                $mail->CharSet = 'UTF-8';
                $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
                $mail->addAddress($to);
                $mail->Subject = $subject;
                $mail->Body = $body;
                $mail->send();
                return true;
            } catch (\Throwable $e) {
                error_log('[PHPMailer] ' . $e->getMessage());
                return SimpleGmailSmtp::sendMessage($to, $subject, $body);
            }
        }

        return SimpleGmailSmtp::sendMessage($to, $subject, $body);
    }
}
