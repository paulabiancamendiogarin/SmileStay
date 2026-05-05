<?php


class DashboardController {
    private $bookingModel;
    private $roomModel;
    private $userModel;

 public function __construct() {
    $this->bookingModel = new Booking();
    $this->roomModel = new Room();
    $this->userModel = new User(); 
}

    
   public function index() {
   
    if (!isLoggedIn()) {
        redirect('/login');
    }

    if (isAdmin()) {
        redirect('/admin'); 
    }

 
    $bookings = $this->bookingModel->getByUserId(getCurrentUserId());

 
    if (!is_array($bookings)) {
        $bookings = [];
    }

   
    $stats = $this->calculateStats($bookings);

    $showAuthSuccessAnimation = !empty($_SESSION['show_auth_success_animation']);
    unset($_SESSION['show_auth_success_animation']);

    include APP_PATH . '/views/dashboard/index.php';
}

 
    private function calculateStats($bookings) {
        $stats = [
            'total' => 0,
            'confirmed' => 0,
            'pending' => 0,
            'cancelled' => 0,
            'completed' => 0
        ];

        if (!is_array($bookings)) {
            return $stats;
        }

        $stats['total'] = count($bookings);

        foreach ($bookings as $booking) {
            if (isset($booking['status']) && isset($stats[$booking['status']])) {
                $stats[$booking['status']]++;
            }
        }

        return $stats;
    }

   
    public function bookings() {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $bookings = $this->bookingModel->getByUserId(getCurrentUserId());

        if (!is_array($bookings)) {
            $bookings = [];
        }

        include APP_PATH . '/views/bookings/history.php';
    }
public function editBooking($id) {
    $booking = $this->bookingModel->findById($id);

    if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
        setFlashMessage('error', 'Booking not found.');
        redirect('/my-bookings');
    }

    $room = $this->roomModel->findWithHotel($booking['room_id']);

    include APP_PATH . '/views/dashboard/edit_booking.php';
}


public function updateBooking($id) {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('/my-bookings');
    }

    $booking = $this->bookingModel->findById($id);

    if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
        setFlashMessage('error', 'Unauthorized.');
        redirect('/my-bookings');
    }

    $update = $this->bookingModel->update($id, [
        'check_in' => $_POST['check_in'],
        'check_out' => $_POST['check_out'],
        'guests' => $_POST['guests'],
        'special_requests' => $_POST['special_requests']
    ]);

    if ($update) {
        setFlashMessage('success', 'Booking updated successfully!');
    } else {
        setFlashMessage('error', 'Failed to update booking.');
    }

    redirect('/my-bookings');
}


public function deleteBooking($id) {
    $booking = $this->bookingModel->findById($id);

    if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
        setFlashMessage('error', 'Unauthorized.');
        redirect('/my-bookings');
    }

    if ($this->bookingModel->delete($id)) {
        setFlashMessage('success', 'Booking deleted.');
    } else {
        setFlashMessage('error', 'Failed to delete booking.');
    }

    redirect('/my-bookings');
}

    public function cancelBooking($id = null) {

    if (!isLoggedIn()) {
        redirect('/login');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('/my-bookings');
    }

    $bookingId = $id ?? ($_POST['booking_id'] ?? null);

    if (!$bookingId) {
        setFlashMessage('error', 'Invalid booking.');
        redirect('/my-bookings');
    }

    $booking = $this->bookingModel->findById($bookingId);

    if (!$booking) {
        setFlashMessage('error', 'Booking not found.');
        redirect('/my-bookings');
    }

    if ($booking['user_id'] != $_SESSION['user_id']) {
        setFlashMessage('error', 'Unauthorized action.');
        redirect('/my-bookings');
    }

    if (in_array($booking['status'], ['cancelled', 'completed'])) {
        setFlashMessage('error', 'This booking cannot be cancelled.');
        redirect('/my-bookings');
    }

   
    $checkInDate = new DateTime($booking['check_in']);
    $today = new DateTime();
    $today->setTime(0,0,0); 

    if ($checkInDate <= $today) {
        setFlashMessage('error', 'Cannot cancel on or after check-in date.');
        redirect('/my-bookings');
    }

    try {
        
        $this->bookingModel->beginTransaction();

        
        $cancelled = $this->bookingModel->cancel($bookingId);

        if (!$cancelled) {
            throw new Exception('Cancel failed');
        }

   
        $this->roomModel->increaseAvailability($booking['room_id']);

      
        $this->bookingModel->commit();

        setFlashMessage('success', 'Booking cancelled successfully.');

    } catch (Exception $e) {

        $this->bookingModel->rollback();

        setFlashMessage('error', 'Failed to cancel booking.');
    }

    redirect('/my-bookings');
}
public function profile() {
    include APP_PATH . '/views/user/profile.php';
}

public function updateProfile() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $name = sanitize($_POST['name']);
        $password = $_POST['password'];

        $data = ['name' => $name];

        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->userModel->update($_SESSION['user_id'], $data);

        $_SESSION['user_name'] = $name;

        setFlashMessage('success', 'Profile updated!');
        redirect('/profile');
    }
}
}