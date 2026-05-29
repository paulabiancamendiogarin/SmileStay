

<?php

class AdminController {
    private $hotelModel;
    private $roomModel;
    private $bookingModel;
    private $userModel;
    private $paymentModel;

    public function __construct() {
        
        if (!isLoggedIn() || !isAdmin()) {
            setFlashMessage('error', 'Access denied. Admin privileges required.');
            redirect('/login');
        }

        $this->hotelModel = new Hotel();
        $this->roomModel = new Room();
        $this->bookingModel = new Booking();
        $this->userModel = new User();
        $this->paymentModel = new Payment();
    }

    
   public function dashboard() {


    $bookings = $this->bookingModel->getAll();

    
    if (!is_array($bookings)) {
        $bookings = [];
    }

    $stats = [
        'total' => count($bookings),
        'pending' => 0,
        'confirmed' => 0,
        'completed' => 0,
        'cancelled' => 0
    ];
    
    foreach ($bookings as $b) {
        if (isset($stats[$b['status']])) {
            $stats[$b['status']]++;
        }
    }

    $pendingUserCount = $this->userModel->getPendingCount();

    include APP_PATH . '/views/admin/dashboard.php';
}

    public function hotels() {
        $hotels = $this->hotelModel->getAllAdmin();
        include APP_PATH . '/views/admin/hotels/index.php';
    }

   
    public function addHotel() {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $imageName = 'default_hotel.jpg';
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $fileType = $_FILES['image']['type'];
                
                if (in_array($fileType, $allowedTypes)) {
                    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $imageName = 'hotel_' . time() . '_' . uniqid() . '.' . $extension;
                    $uploadPath = UPLOAD_PATH . $imageName;
                    
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                        $error = 'Failed to upload image.';
                    }
                } else {
                    $error = 'Invalid image type. Allowed: JPEG, PNG, GIF, WEBP';
                }
            }

            if (!$error) {
               
                $hotelId = $this->hotelModel->create([
                    'hotel_name' => sanitize($_POST['hotel_name']),
                    'location' => sanitize($_POST['location']),
                    'latitude' => (float) $_POST['latitude'],
                    'longitude' => (float) $_POST['longitude'],
                    'description' => sanitize($_POST['description']),
                    'price_per_night' => (float) $_POST['price_per_night'],
                    'image' => $imageName,
                    'amenities' => sanitize($_POST['amenities']),
                    'rating' => (float) ($_POST['rating'] ?? 0),
                    'status' => $_POST['status'] ?? 'active'
                ]);

                if ($hotelId) {
                    setFlashMessage('success', 'Hotel added successfully!');
                    redirect('/admin-hotels');
                } else {
                    $error = 'Failed to add hotel.';
                }
            }
        }

        include APP_PATH . '/views/admin/hotels/create.php';
    }

   
    public function editHotel($id = null) {
        $hotelId = $id ?? ($_GET['id'] ?? null);

        if (!$hotelId) {
            redirect('/admin-hotels');
        }

        $hotel = $this->hotelModel->findById($hotelId);

        if (!$hotel) {
            setFlashMessage('error', 'Hotel not found.');
            redirect('/admin-hotels');
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $updateData = [
                'hotel_name' => sanitize($_POST['hotel_name']),
                'location' => sanitize($_POST['location']),
                'latitude' => (float) $_POST['latitude'],
                'longitude' => (float) $_POST['longitude'],
                'description' => sanitize($_POST['description']),
                'price_per_night' => (float) $_POST['price_per_night'],
                'amenities' => sanitize($_POST['amenities']),
                'rating' => (float) ($_POST['rating'] ?? 0),
                'status' => $_POST['status'] ?? 'active'
            ];

            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $fileType = $_FILES['image']['type'];
                
                if (in_array($fileType, $allowedTypes)) {
                    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $imageName = 'hotel_' . time() . '_' . uniqid() . '.' . $extension;
                    $uploadPath = UPLOAD_PATH . $imageName;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                        $updateData['image'] = $imageName;
                        
                      
                        if ($hotel['image'] !== 'default_hotel.jpg' && file_exists(UPLOAD_PATH . $hotel['image'])) {
                            unlink(UPLOAD_PATH . $hotel['image']);
                        }
                    }
                }
            }

            if ($this->hotelModel->update($hotelId, $updateData)) {
                setFlashMessage('success', 'Hotel updated successfully!');
                redirect('/admin-hotels');
            } else {
                $error = 'Failed to update hotel.';
            }
        }

        include APP_PATH . '/views/admin/hotels/edit.php';
    }

   
    public function deleteHotel($id = null) {
        $hotelId = $id ?? ($_POST['hotel_id'] ?? null);

        if (!$hotelId) {
            redirect('/admin-hotels');
        }

        $hotel = $this->hotelModel->findById($hotelId);

        if ($hotel) {
            
            if ($hotel['image'] !== 'default_hotel.jpg' && file_exists(UPLOAD_PATH . $hotel['image'])) {
                unlink(UPLOAD_PATH . $hotel['image']);
            }

            if ($this->hotelModel->delete($hotelId)) {
                setFlashMessage('success', 'Hotel deleted successfully!');
            } else {
                setFlashMessage('error', 'Failed to delete hotel.');
            }
        }

        redirect('/admin-hotels');
    }

    
    public function rooms() {
        $hotelId = $_GET['hotel_id'] ?? null;
        
        if ($hotelId) {
            $rooms = $this->roomModel->getByHotelId($hotelId);
            $hotel = $this->hotelModel->findById($hotelId);
        } else {
            $rooms = $this->roomModel->getAllWithHotel();
            $hotel = null;
        }

        $hotels = $this->hotelModel->getAllAdmin();
        
        include APP_PATH . '/views/admin/rooms/index.php';
    }

    
    public function addRoom() {
    $hotels = $this->hotelModel->getAllAdmin();
    $error = null;
    $room = null;

    // 👉 EDIT MODE
    if (isset($_GET['room_id'])) {
        $room = $this->roomModel->findById($_GET['room_id']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $data = [
            'hotel_id' => (int) $_POST['hotel_id'],
            'room_type' => sanitize($_POST['room_type']),
            'capacity' => (int) $_POST['capacity'],
            'price' => (float) $_POST['price'],
            'description' => sanitize($_POST['description']),
            'available' => (int) $_POST['available']
        ];

        // 👉 UPDATE
        if (!empty($_POST['room_id'])) {

            if ($this->roomModel->update($_POST['room_id'], $data)) {
                setFlashMessage('success', 'Room updated successfully!');
            } else {
                $error = 'Failed to update room.';
            }

        } else {
            // 👉 CREATE
            if ($this->roomModel->create($data)) {
                setFlashMessage('success', 'Room added successfully!');
            } else {
                $error = 'Failed to add room.';
            }
        }

        redirect('/admin-rooms?hotel_id=' . $_POST['hotel_id']);
    }

    include APP_PATH . '/views/admin/rooms/create.php';
}

    public function editRoom($id = null) {
    $roomId = $id ?? ($_GET['id'] ?? null);

    if (!$roomId) {
        redirect('/admin-rooms');
    }

    $room = $this->roomModel->findById($roomId);
    $hotels = $this->hotelModel->getAllAdmin();
 
    if (!$room) {
        setFlashMessage('error', 'Room not found.');
        redirect('/admin-rooms');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $updated = $this->roomModel->update($roomId, [
            'hotel_id' => (int) $_POST['hotel_id'],
            'room_type' => sanitize($_POST['room_type']),
            'capacity' => (int) $_POST['capacity'],
            'price' => (float) $_POST['price'],
            'description' => sanitize($_POST['description']),
            'available' => (int) $_POST['available']
        ]);

        if ($updated) {
            setFlashMessage('success', 'Room updated successfully!');
            redirect('/admin-rooms?hotel_id=' . $_POST['hotel_id']);
        } else {
            $error = 'Failed to update room.';
        }
    }

    include APP_PATH . '/views/admin/rooms/edit.php';

}
    public function deleteRoom($id = null) {
        $roomId = $id ?? ($_POST['room_id'] ?? null);
        $hotelId = $_POST['hotel_id'] ?? '';

        if ($roomId && $this->roomModel->delete($roomId)) {
            setFlashMessage('success', 'Room deleted successfully!');
        } else {
            setFlashMessage('error', 'Failed to delete room.');
        }

        redirect('/admin-rooms' . ($hotelId ? "?hotel_id=$hotelId" : ''));
    }

    
   public function bookings() {
    $filters = [
        'search' => sanitize($_GET['q'] ?? ''),
        'status' => sanitize($_GET['status'] ?? ''),
        'payment_status' => sanitize($_GET['payment_status'] ?? ''),
        'user_approved' => $_GET['user_approved'] ?? '',
        'sort' => sanitize($_GET['sort'] ?? 'created_at'),
        'dir' => sanitize($_GET['dir'] ?? 'DESC'),
    ];

    $page = (int) ($_GET['page'] ?? 1);
    $perPage = (int) ($_GET['per_page'] ?? 15);
    $paged = $this->bookingModel->searchAdminHistoryPaged($filters, $page, $perPage);
    $bookings = $paged['rows'] ?? [];
    $totalPages = $paged['total_pages'] ?? 1;
    $currentPage = $paged['page'] ?? 1;
    $totalRows = $paged['total'] ?? 0;
    $perPage = $paged['per_page'] ?? $perPage;

    include APP_PATH . '/views/admin/bookings/index.php';
}

    public function users() {
        $users = $this->userModel->getAllCustomers();
        $pendingUsers = $this->userModel->getPendingApproval();

        include APP_PATH . '/views/admin/users/index.php';
    }

    public function approveUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin-users');
        }

        $userId = (int) ($_POST['user_id'] ?? 0);

        if (!$userId) {
            setFlashMessage('error', 'Invalid user.');
            redirect('/admin-users');
        }

        $user = $this->userModel->findById($userId);

        if (!$user || ($user['role'] ?? '') !== 'customer') {
            setFlashMessage('error', 'User not found.');
            redirect('/admin-users');
        }

        if (!empty($user['is_approved'])) {
            setFlashMessage('info', 'User is already approved.');
            redirect('/admin-users');
        }

        if ($this->userModel->approve($userId)) {
            setFlashMessage('success', 'User approved successfully. They can now access the system.');
        } else {
            setFlashMessage('error', 'Could not approve user.');
        }

        redirect('/admin-users');
    }

    
    public function updateBooking() {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('/admin-bookings');
    }

    $bookingId = $_POST['booking_id'] ?? null;
    $newStatus = $_POST['status'] ?? null;

    if (!$bookingId || !$newStatus) {
        redirect('/admin-bookings');
    }

    $booking = $this->bookingModel->findById($bookingId);

    if (!$booking) {
        setFlashMessage('error', 'Booking not found.');
        redirect('/admin-bookings');
    }

    $oldStatus = $booking['status'];

    
    if ($oldStatus === 'pending' && $newStatus === 'confirmed') {
        $this->roomModel->decreaseAvailability($booking['room_id']);
    }

   
    if ($oldStatus === 'confirmed' && $newStatus === 'cancelled') {
        $this->roomModel->increaseAvailability($booking['room_id']);
    }

    if ($oldStatus === 'cancelled' && $newStatus === 'confirmed') {
        $this->roomModel->decreaseAvailability($booking['room_id']);
    }

   
    if ($this->bookingModel->updateStatus($bookingId, $newStatus)) {
        setFlashMessage('success', 'Booking updated successfully!');
    } else {
        setFlashMessage('error', 'Failed to update booking.');
    }

    redirect('/admin-bookings');
}
public function deleteBooking() {
    $id = $_POST['booking_id'] ?? null;

    if ($id && $this->bookingModel->delete($id)) {
        setFlashMessage('success', 'Booking deleted!');
    } else {
        setFlashMessage('error', 'Delete failed.');
    }

    redirect('/admin-bookings');
}

    public function payments() {
        $payments = $this->paymentModel->getAllWithBookingDetails();

        if (!is_array($payments)) {
            $payments = [];
        }

        include APP_PATH . '/views/admin/payments/index.php';
    }

    public function verifyPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin-payments');
        }

        $paymentId = (int) ($_POST['payment_id'] ?? 0);

        if (!$paymentId) {
            redirect('/admin-payments');
        }

        $payment = $this->paymentModel->findById($paymentId);

        if (!$payment || $payment['status'] !== 'pending') {
            setFlashMessage('error', 'Payment not found or already verified.');
            redirect('/admin-payments');
        }

        if (empty($payment['proof_image'])) {
            setFlashMessage('error', 'Guest has not uploaded proof yet.');
            redirect('/admin-payments');
        }

        if ($this->paymentModel->verify($paymentId)) {
            setFlashMessage('success', 'Payment marked as verified (paid).');
        } else {
            setFlashMessage('error', 'Could not update payment.');
        }

        redirect('/admin-payments');
    }

    public function rejectPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin-payments');
        }

        $paymentId = (int) ($_POST['payment_id'] ?? 0);
        $reason = sanitize($_POST['reason'] ?? 'Payment rejected by admin.');

        if (!$paymentId) {
            redirect('/admin-payments');
        }

        if ($this->paymentModel->reject($paymentId, $reason)) {
            setFlashMessage('success', 'Payment rejected.');
        } else {
            setFlashMessage('error', 'Could not reject payment.');
        }

        redirect('/admin-payments');
    }

    public function reports()
    {
        $filters = [
            'from' => sanitize($_GET['from'] ?? ''),
            'to' => sanitize($_GET['to'] ?? ''),
            'hotel_id' => sanitize($_GET['hotel_id'] ?? ''),
            'payment_status' => sanitize($_GET['payment_status'] ?? ''),
            'booking_status' => sanitize($_GET['booking_status'] ?? ''),
            'year' => (int) ($_GET['year'] ?? (int) date('Y')),
        ];

        $hotels = $this->hotelModel->getAllAdmin();
        $summary = $this->bookingModel->getReportSummary($filters);
        $topHotels = $this->bookingModel->getMostBookedHotels($filters, 5);
        $monthlyBookings = $this->bookingModel->getMonthlyBookings((int) $filters['year'], $filters);
        $monthlyRegistrations = $this->userModel->getMonthlyRegistrations((int) $filters['year']);

        include APP_PATH . '/views/admin/reports/index.php';
    }

    public function exportReports()
    {
        $format = sanitize($_GET['format'] ?? 'csv'); // csv|excel
        $filters = [
            'from' => sanitize($_GET['from'] ?? ''),
            'to' => sanitize($_GET['to'] ?? ''),
            'hotel_id' => sanitize($_GET['hotel_id'] ?? ''),
            'payment_status' => sanitize($_GET['payment_status'] ?? ''),
            'booking_status' => sanitize($_GET['booking_status'] ?? ''),
        ];

        $rows = $this->bookingModel->searchAdminHistory($filters);

        $filename = 'reports_' . date('Ymd_His');
        if ($format === 'excel') {
            header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
            echo "Reference\tGuest\tEmail\tHotel\tRoom\tCheck-in\tCheck-out\tBooking Status\tPayment Status\tAmount\n";
            foreach ($rows as $r) {
                echo ($r['booking_reference'] ?? '') . "\t"
                    . ($r['guest_name'] ?? '') . "\t"
                    . ($r['guest_email'] ?? '') . "\t"
                    . ($r['hotel_name'] ?? '') . "\t"
                    . ($r['room_type'] ?? '') . "\t"
                    . ($r['check_in'] ?? '') . "\t"
                    . ($r['check_out'] ?? '') . "\t"
                    . ($r['status'] ?? '') . "\t"
                    . ($r['payment_status'] ?? '') . "\t"
                    . ($r['total_price'] ?? '') . "\n";
            }
            exit;
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Reference','Guest','Email','Hotel','Room','Check-in','Check-out','Booking Status','Payment Status','Amount']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['booking_reference'] ?? '',
                $r['guest_name'] ?? '',
                $r['guest_email'] ?? '',
                $r['hotel_name'] ?? '',
                $r['room_type'] ?? '',
                $r['check_in'] ?? '',
                $r['check_out'] ?? '',
                $r['status'] ?? '',
                $r['payment_status'] ?? '',
                $r['total_price'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

}