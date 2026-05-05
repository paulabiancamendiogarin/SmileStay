<?php

if (session_status() === PHP_SESSION_NONE) {
session_start();
}

define('APP_NAME', 'SmileStay');
define('APP_VERSION', '1.0.0');

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
$scheme = $isHttps ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$basePath = ($scriptDir === '.' || $scriptDir === '/') ? '' : $scriptDir;
$basePathForUrl = '';
if ($basePath !== '') {
    $segments = explode('/', ltrim($basePath, '/'));
    $basePathForUrl = '/' . implode('/', array_map('rawurlencode', $segments));
}

define('APP_BASE_PATH', $basePath);
define('APP_URL', $scheme . '://' . $host . $basePathForUrl);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads/hotels/');

define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY_HERE');

/** GCash number shown in QR payload (configure for production). */
define('GCASH_NUMBER', '09171234567');
/**
 * Paste your official merchant/static QRPh payload string from GCash here.
 * Without a real QRPh payload, GCash scanner will mark QR as invalid.
 */
define('GCASH_QRPH_PAYLOAD', '');
/** Static payment image shown to users (relative to public/). */
define('GCASH_PAYMENT_IMAGE', 'uploads/gcash/gcash_payment.jpg');

/** Minutes user has to complete GCash payment window before booking auto-expires. */
define('PAYMENT_WINDOW_MINUTES', 10);

/** OTP email verification */
define('OTP_EXPIRY_MINUTES', 5);
define('OTP_RESEND_COOLDOWN_SECONDS', 30);
define('OTP_MAX_FAILED_ATTEMPTS', 3);
define('TOTP_ISSUER', APP_NAME);
define('TOTP_WINDOW_STEPS', 1);

/** Gmail SMTP — use an App Password, not your normal Gmail password. */
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'yourgmail@gmail.com');
define('SMTP_PASSWORD', 'your_app_password');
define('SMTP_PORT', 587);
define('MAIL_FROM_EMAIL', SMTP_USERNAME);
define('MAIL_FROM_NAME', APP_NAME);

define('BACOLOD_LAT', 10.6765);
define('BACOLOD_LNG', 122.9509);

date_default_timezone_set('Asia/Manila');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once CONFIG_PATH . '/database.php';

function redirect($url) {
header("Location: " . APP_URL . $url);
exit;
}

function isLoggedIn() {
return isset($_SESSION['user_id']);
}

function isAdmin() {
return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function getCurrentUserId() {
return $_SESSION['user_id'] ?? null;
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input ?? '')));
}

function generateBookingReference() {
return 'BK' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
}


function setFlashMessage($type, $message) {
$_SESSION['flash'] = [
'type' => $type,
'message' => $message
];
}
function getFlashMessage() {
if (isset($_SESSION['flash'])) {
$flash = $_SESSION['flash'];
unset($_SESSION['flash']);
return $flash;
}
return null;
}