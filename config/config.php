<?php

if (session_status() === PHP_SESSION_NONE) {
session_start();
}

define('APP_NAME', 'SmileStay');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/SMILESTAY/public');

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads/hotels/');

define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY_HERE');

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
return htmlspecialchars(strip_tags(trim($input)));
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