

<?php

class AdminController {
    private $hotelModel;
    private $roomModel;
    private $bookingModel;
    private $userModel;

    public function __construct() {
        
        if (!isLoggedIn() || !isAdmin()) {
            setFlashMessage('error', 'Access denied. Admin privileges required.');
            redirect('/login');
        }

        $this->hotelModel = new Hotel();
        $this->roomModel = new Room();
        $this->bookingModel = new Booking();
        $this->userModel = new User();
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

    $bookings = $this->bookingModel->getAll();

   
    if (!is_array($bookings)) {
        $bookings = [];
    }


    include APP_PATH . '/views/admin/bookings/index.php';
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

}