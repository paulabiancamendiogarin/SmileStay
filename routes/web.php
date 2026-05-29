<?php


$request_uri = $_SERVER['REQUEST_URI'];
$base_path = APP_BASE_PATH;

$request_path = rawurldecode(parse_url($request_uri, PHP_URL_PATH) ?? '');
$uri = str_replace($base_path, '', $request_path);
$uri = trim($uri, '/');

if (empty($uri)) {
    $uri = 'home';
}

$segments = explode('/', $uri);
$page = $segments[0] ?? 'home';
$param = $segments[1] ?? null;


$routes = [

    // PUBLIC
    'home' => ['controller' => 'HotelController', 'action' => 'home'],
    'hotels' => ['controller' => 'HotelController', 'action' => 'index'],
    'hotel' => ['controller' => 'HotelController', 'action' => 'show'],
    'search' => ['controller' => 'HotelController', 'action' => 'search'],
    'map' => ['controller' => 'HotelController', 'action' => 'map'],

    // AUTH
    'login' => ['controller' => 'AuthController', 'action' => 'login'],
    'register' => ['controller' => 'AuthController', 'action' => 'register'],
    'logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    'verify-login-otp' => ['controller' => 'AuthController', 'action' => 'verifyLoginOtp'],
    'verify-register-otp' => ['controller' => 'AuthController', 'action' => 'verifyRegisterOtp'],
    'resend-login-otp' => ['controller' => 'AuthController', 'action' => 'resendLoginOtp'],
    'resend-register-otp' => ['controller' => 'AuthController', 'action' => 'resendRegisterOtp'],
    'pending-approval' => ['controller' => 'AuthController', 'action' => 'pendingApproval'],

    // BOOKINGS (USER)
    'booking' => ['controller' => 'BookingController', 'action' => 'create'],
    'booking-confirm' => ['controller' => 'BookingController', 'action' => 'confirm'],
    'booking-store' => ['controller' => 'BookingController', 'action' => 'store'],
    // Booking payment management is admin-side; client uses card/cash only.
    'booking-card' => ['controller' => 'BookingController', 'action' => 'card'],
    'booking-card-process' => ['controller' => 'BookingController', 'action' => 'processCard'],
    // Backward-compatible: old links should not 404
    'booking-payment' => ['controller' => 'BookingController', 'action' => 'payment'],
    'booking-payment-upload' => ['controller' => 'BookingController', 'action' => 'uploadPaymentProof'],

    'edit-booking' => ['controller' => 'DashboardController', 'action' => 'editBooking'],
'update-booking' => ['controller' => 'DashboardController', 'action' => 'updateBooking'],
'delete-booking' => ['controller' => 'DashboardController', 'action' => 'deleteBooking'],

    // USER DASHBOARD
    'dashboard' => ['controller' => 'DashboardController', 'action' => 'index'],
    'my-bookings' => ['controller' => 'DashboardController', 'action' => 'bookings'],
    'my-payments' => ['controller' => 'DashboardController', 'action' => 'payments'],
    'cancel-booking' => ['controller' => 'DashboardController', 'action' => 'cancelBooking'],

    'profile' => ['controller' => 'DashboardController', 'action' => 'profile'],
'update-profile' => ['controller' => 'DashboardController', 'action' => 'updateProfile'],

    // ADMIN DASHBOARD
    'admin' => ['controller' => 'AdminController', 'action' => 'dashboard'],

    // HOTELS CRUD
    'admin-hotels' => ['controller' => 'AdminController', 'action' => 'hotels'],
    'admin-add-hotel' => ['controller' => 'AdminController', 'action' => 'addHotel'],
    'admin-edit-hotel' => ['controller' => 'AdminController', 'action' => 'editHotel'],
    'admin-delete-hotel' => ['controller' => 'AdminController', 'action' => 'deleteHotel'],

    // ROOMS CRUD
    'admin-rooms' => ['controller' => 'AdminController', 'action' => 'rooms'],
    'admin-add-room' => ['controller' => 'AdminController', 'action' => 'addRoom'],
    'admin-edit-room' => ['controller' => 'AdminController', 'action' => 'editRoom'], // ✅ ADDED
    'admin-delete-room' => ['controller' => 'AdminController', 'action' => 'deleteRoom'],

    // BOOKINGS CRUD (ADMIN)
    'admin-bookings' => ['controller' => 'AdminController', 'action' => 'bookings'],
    'admin-update-booking' => ['controller' => 'AdminController', 'action' => 'updateBooking'],
    'admin-delete-booking' => ['controller' => 'AdminController', 'action' => 'deleteBooking'], // ✅ ADDED
    'admin-payments' => ['controller' => 'AdminController', 'action' => 'payments'],
    'admin-verify-payment' => ['controller' => 'AdminController', 'action' => 'verifyPayment'],
    'admin-reject-payment' => ['controller' => 'AdminController', 'action' => 'rejectPayment'],
    'admin-users' => ['controller' => 'AdminController', 'action' => 'users'],
    'admin-approve-user' => ['controller' => 'AdminController', 'action' => 'approveUser'],
    'admin-reports' => ['controller' => 'AdminController', 'action' => 'reports'],
    'admin-reports-export' => ['controller' => 'AdminController', 'action' => 'exportReports'],
];



if (isset($routes[$page])) {

    $controllerName = $routes[$page]['controller'];
    $actionName = $routes[$page]['action'];

    $controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';

    if (file_exists($controllerFile)) {
        require_once $controllerFile;

        $controller = new $controllerName();

        if (method_exists($controller, $actionName)) {
            $controller->$actionName($param);
        } else {
            http_response_code(404);
            include APP_PATH . '/views/errors/404.php';
        }

    } else {
        http_response_code(404);
        include APP_PATH . '/views/errors/404.php';
    }

} else {

  
    if (is_numeric($page)) {
        require_once APP_PATH . '/controllers/HotelController.php';
        $controller = new HotelController();
        $controller->show($page);
    } else {
        http_response_code(404);
        include APP_PATH . '/views/errors/404.php';
    }
}