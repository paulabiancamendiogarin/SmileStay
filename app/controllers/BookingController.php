<?php

class BookingController
{
    private $hotelModel;
    private $roomModel;
    private $bookingModel;
    private $paymentModel;

    public function __construct()
    {
        $this->hotelModel = new Hotel();
        $this->roomModel = new Room();
        $this->bookingModel = new Booking();
        $this->paymentModel = new Payment();
    }

    public function create()
    {
        if (!isLoggedIn()) {
            setFlashMessage('error', 'Please login to make a booking.');
            redirect('/login');
        }

        $roomId = $_GET['room_id'] ?? null;
        $hotelId = $_GET['hotel_id'] ?? null;

        if (!$roomId || !$hotelId) {
            setFlashMessage('error', 'Invalid booking request.');
            redirect('/hotels');
        }

        $room = $this->roomModel->findById($roomId);
        $hotel = $this->hotelModel->findById($hotelId);

        if (!$room || !$hotel) {
            setFlashMessage('error', 'Room or hotel not found.');
            redirect('/hotels');
        }

        $checkIn = $_GET['check_in'] ?? date('Y-m-d', strtotime('+1 day'));
        $checkOut = $_GET['check_out'] ?? date('Y-m-d', strtotime('+2 days'));

        include APP_PATH . '/views/bookings/create.php';
    }

    public function store()
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/hotels');
        }

        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            setFlashMessage('error', 'Session expired. Please login again.');
            redirect('/login');
        }

        $roomId = $_POST['room_id'] ?? null;
        $hotelId = $_POST['hotel_id'] ?? null;
        $checkIn = $_POST['check_in'] ?? null;
        $checkOut = $_POST['check_out'] ?? null;
        $guests = (int) ($_POST['guests'] ?? 1);
        $specialRequests = sanitize($_POST['special_requests'] ?? '');

        if (!$roomId || !$hotelId || !$checkIn || !$checkOut) {
            setFlashMessage('error', 'Please fill all required fields.');
            redirect("/booking?room_id=$roomId&hotel_id=$hotelId");
        }

        $checkInDate = new DateTime($checkIn);
        $checkOutDate = new DateTime($checkOut);
        $today = new DateTime('today');

        if ($checkInDate < $today) {
            setFlashMessage('error', 'Check-in date cannot be in the past.');
            redirect("/booking?room_id=$roomId&hotel_id=$hotelId");
        }

        if ($checkOutDate <= $checkInDate) {
            setFlashMessage('error', 'Check-out must be after check-in.');
            redirect("/booking?room_id=$roomId&hotel_id=$hotelId");
        }

        $room = $this->roomModel->findById($roomId);

        if (!$room) {
            setFlashMessage('error', 'Room not found.');
            redirect('/hotels');
        }

        if (!$this->roomModel->isAvailableForDates($roomId, $checkIn, $checkOut)) {
            setFlashMessage('error', 'Room not available.');
            redirect("/booking?room_id=$roomId&hotel_id=$hotelId");
        }

        if ($guests > $room['capacity']) {
            setFlashMessage('error', "Max guests: {$room['capacity']}");
            redirect("/booking?room_id=$roomId&hotel_id=$hotelId");
        }

        $nights = $checkInDate->diff($checkOutDate)->days;
        $totalPrice = $room['price'] * $nights;

        $this->bookingModel->beginTransaction();

        $result = $this->bookingModel->create([
            'user_id' => $userId,
            'hotel_id' => $hotelId,
            'room_id' => $roomId,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'total_price' => $totalPrice,
            'guests' => $guests,
            'special_requests' => $specialRequests,
            'status' => 'pending',
        ]);

        if (!$result || !isset($result['id'])) {
            $this->bookingModel->rollback();
            setFlashMessage('error', 'Booking failed.');
            redirect("/booking?room_id=$roomId&hotel_id=$hotelId");
        }

        $bookingId = (int) $result['id'];

        $this->bookingModel->commit();

        setFlashMessage('success', 'Booking created successfully.');
        redirect('/booking-confirm/' . $bookingId);
    }

    public function payment($id = null)
    {
        setFlashMessage('info', 'Online payment is currently disabled. Your booking is recorded without payment.');
        redirect('/my-bookings');
    }

    public function uploadPaymentProof($id = null)
    {
        setFlashMessage('info', 'Payment proof upload is currently disabled.');
        redirect('/my-bookings');
    }

    public function confirm($id = null)
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $bookingId = $id ?? ($_GET['id'] ?? null);

        if (!$bookingId) {
            redirect('/dashboard');
        }

        $booking = $this->bookingModel->getWithDetails($bookingId);

        if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
            setFlashMessage('error', 'Booking not found.');
            redirect('/dashboard');
        }

        $payment = null;

        include APP_PATH . '/views/bookings/confirmation.php';
    }
}
