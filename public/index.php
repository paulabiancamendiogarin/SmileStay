<?php

require_once dirname(__DIR__) . '/config/config.php';

$autoload = BASE_PATH . '/vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}

require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Hotel.php';
require_once APP_PATH . '/models/Room.php';
require_once APP_PATH . '/models/Booking.php';
require_once APP_PATH . '/models/Payment.php';
require_once APP_PATH . '/models/OtpCode.php';
require_once APP_PATH . '/models/Notification.php';
require_once APP_PATH . '/services/SimpleGmailSmtp.php';
require_once APP_PATH . '/services/MailService.php';
require_once APP_PATH . '/services/QrGenerator.php';
require_once APP_PATH . '/services/TotpService.php';

try {
    (new Booking())->expireUnpaidBookings();
} catch (Throwable $e) {
    // DB may not have payments table until schema_updates_gcash_otp.sql is applied
}

try {
    (new OtpCode())->deleteExpired();
} catch (Throwable $e) {
}

require_once BASE_PATH . '/routes/web.php';