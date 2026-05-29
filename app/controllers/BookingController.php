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
        requireApprovedAccess();

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
        requireApprovedAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/hotels');
        }

        $userId = getCurrentUserId();

        $roomId = $_POST['room_id'] ?? null;
        $hotelId = $_POST['hotel_id'] ?? null;
        $checkIn = $_POST['check_in'] ?? null;
        $checkOut = $_POST['check_out'] ?? null;
        $guests = (int) ($_POST['guests'] ?? 1);
        $specialRequests = sanitize($_POST['special_requests'] ?? '');
        $paymentMethod = sanitize($_POST['payment_method'] ?? 'cash');

        if (!in_array($paymentMethod, ['cash', 'card'], true)) {
            $paymentMethod = 'cash';
        }

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
        $referenceCode = 'PAY' . strtoupper(substr(md5($result['reference'] . $bookingId), 0, 8));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . PAYMENT_WINDOW_MINUTES . ' minutes'));

        try {
            $paymentId = $this->paymentModel->create([
                'booking_id' => $bookingId,
                'amount' => $totalPrice,
                'payment_method' => $paymentMethod,
                'reference_code' => $referenceCode,
                'qr_image_path' => null,
                'expires_at' => $expiresAt,
            ]);
        } catch (Throwable $e) {
            $this->bookingModel->rollback();
            setFlashMessage('error', 'Booking saved but payment initialization failed. Please contact support or try again.');
            redirect('/my-bookings');
        }

        if (!$paymentId) {
            $this->bookingModel->rollback();
            setFlashMessage('error', 'Could not initialize payment for this booking.');
            redirect("/booking?room_id=$roomId&hotel_id=$hotelId");
        }

        if ($paymentMethod === 'cash') {
            $this->bookingModel->syncPaymentFields($bookingId, 'pending');
        } else {
            // card starts as unpaid until processed
            $this->bookingModel->syncPaymentFields($bookingId, 'unpaid');
        }
        $this->bookingModel->commit();

        if ($paymentMethod === 'cash') {
            setFlashMessage('success', 'Booking created. Payment method: Cash. Please pay at the property; admin will confirm your payment.');
            redirect('/booking-confirm/' . $bookingId);
        }

        if ($paymentMethod === 'card') {
            setFlashMessage('info', 'Booking created. Enter your card details to complete payment.');
            redirect('/booking-card/' . $bookingId);
        }

        setFlashMessage('success', 'Booking created successfully.');
        redirect('/booking-confirm/' . $bookingId);
    }

    public function card($id = null)
    {
        requireApprovedAccess();

        $bookingId = (int) ($id ?? 0);
        if (!$bookingId) {
            redirect('/my-bookings');
        }

        $booking = $this->bookingModel->getWithDetails($bookingId);
        if (!$booking || (int) $booking['user_id'] !== (int) getCurrentUserId()) {
            setFlashMessage('error', 'Booking not found.');
            redirect('/my-bookings');
        }

        $payment = $this->paymentModel->findByBookingId($bookingId);
        if (!$payment) {
            setFlashMessage('error', 'Payment record not found for this booking.');
            redirect('/my-bookings');
        }

        if (($payment['payment_method'] ?? 'card') !== 'card') {
            setFlashMessage('info', 'Payment management is handled by the admin panel.');
            redirect('/booking-confirm/' . $bookingId);
        }

        include APP_PATH . '/views/bookings/card.php';
    }

    /**
     * Backward-compatible route: old GCash payment page.
     * Payment management is now admin-side, so redirect gracefully.
     */
    public function payment($id = null)
    {
        requireApprovedAccess();

        $bookingId = (int) ($id ?? 0);
        if (!$bookingId) {
            redirect('/my-bookings');
        }

        $booking = $this->bookingModel->getWithDetails($bookingId);
        if (!$booking || (int) $booking['user_id'] !== (int) getCurrentUserId()) {
            setFlashMessage('error', 'Booking not found.');
            redirect('/my-bookings');
        }

        $payment = $this->paymentModel->findByBookingId($bookingId);
        if (!$payment) {
            setFlashMessage('error', 'Payment record not found for this booking.');
            redirect('/my-bookings');
        }

        $paymentExpiresUnix = strtotime($payment['expires_at'] ?? 'now') * 1000;
        include APP_PATH . '/views/bookings/payment.php';
    }

    /**
     * Backward-compatible route: old proof upload endpoint.
     */
    public function uploadPaymentProof($id = null)
    {
        requireApprovedAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/my-bookings');
        }

        $bookingId = (int) ($id ?? 0);
        if (!$bookingId) {
            redirect('/my-bookings');
        }

        $booking = $this->bookingModel->findById($bookingId);
        if (!$booking || (int) $booking['user_id'] !== (int) getCurrentUserId()) {
            setFlashMessage('error', 'Unauthorized.');
            redirect('/my-bookings');
        }

        $payment = $this->paymentModel->findByBookingId($bookingId);
        if (!$payment) {
            setFlashMessage('error', 'Payment record not found.');
            redirect('/my-bookings');
        }

        $method = strtolower((string) ($payment['payment_method'] ?? 'cash'));
        if ($method !== 'cash' && $this->paymentModel->hasColumn('payment_method')) {
            setFlashMessage('error', 'Proof upload is only available for cash payments.');
            redirect('/booking-payment/' . $bookingId);
        }

        if (!isset($_FILES['proof']) || $_FILES['proof']['error'] !== UPLOAD_ERR_OK) {
            setFlashMessage('error', 'Please upload a valid receipt image.');
            redirect('/booking-payment/' . $bookingId);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['proof']['type'] ?? '';
        if (!in_array($fileType, $allowedTypes, true)) {
            setFlashMessage('error', 'Invalid file type. Use JPEG, PNG, GIF, or WebP.');
            redirect('/booking-payment/' . $bookingId);
        }

        if (!is_dir(PAYMENT_PROOF_PATH)) {
            mkdir(PAYMENT_PROOF_PATH, 0755, true);
        }

        $extension = pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $filename = 'proof_' . $bookingId . '_' . time() . '.' . strtolower($extension);
        $fullPath = PAYMENT_PROOF_PATH . $filename;
        $relativePath = 'uploads/payments/' . $filename;

        if (!move_uploaded_file($_FILES['proof']['tmp_name'], $fullPath)) {
            setFlashMessage('error', 'Failed to upload proof. Please try again.');
            redirect('/booking-payment/' . $bookingId);
        }

        $reference = sanitize($_POST['payment_reference'] ?? '');
        $notes = sanitize($_POST['payment_notes'] ?? '');

        try {
            if ($this->paymentModel->updateClientCashPayment($bookingId, $relativePath, $reference ?: null, $notes ?: null)) {
                setFlashMessage('success', 'Payment details updated. Status: Pending Verification.');
            } else {
                setFlashMessage('error', 'Could not save payment update. Please run database/schema_updates_client_payment_updates.sql or contact support.');
            }
        } catch (Throwable $e) {
            setFlashMessage('error', 'Payment update failed: ' . $e->getMessage());
        }

        redirect('/booking-payment/' . $bookingId);
    }

    public function processCard($id = null)
    {
        requireApprovedAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/my-bookings');
        }

        $bookingId = (int) ($id ?? 0);
        if (!$bookingId) {
            redirect('/my-bookings');
        }

        $booking = $this->bookingModel->findById($bookingId);
        if (!$booking || (int) $booking['user_id'] !== (int) getCurrentUserId()) {
            setFlashMessage('error', 'Unauthorized.');
            redirect('/my-bookings');
        }

        $payment = $this->paymentModel->findByBookingId($bookingId);
        if (!$payment) {
            setFlashMessage('error', 'Payment record not found.');
            redirect('/my-bookings');
        }

        $method = strtolower((string) ($payment['payment_method'] ?? 'card'));
        if ($method !== 'card' && $this->paymentModel->hasColumn('payment_method')) {
            setFlashMessage('error', 'Card payment is not available for this booking.');
            redirect('/my-bookings');
        }

        $cardNumber = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
        $cardName = sanitize($_POST['card_name'] ?? '');
        $exp = sanitize($_POST['exp'] ?? '');
        $cvv = preg_replace('/\D/', '', $_POST['cvv'] ?? '');

        if (strlen($cardNumber) < 12 || strlen($cardNumber) > 19 || $cardName === '' || strlen($cvv) < 3 || strlen($cvv) > 4) {
            setFlashMessage('error', 'Invalid card details.');
            redirect('/booking-card/' . $bookingId);
        }

        $last4 = substr($cardNumber, -4);
        $brand = (str_starts_with($cardNumber, '4')) ? 'VISA' : ((str_starts_with($cardNumber, '5')) ? 'MASTERCARD' : 'CARD');
        $transactionId = 'TXN-' . strtoupper(substr(md5($bookingId . '|' . microtime(true)), 0, 12));

        // Demo-only: never store full PAN/CVV.
        try {
            $ok = $this->paymentModel->markCardPaid($bookingId, $transactionId, $brand, $last4);
            if ($ok) {
                setFlashMessage('success', 'Card payment successful. Transaction: ' . $transactionId);
                redirect('/booking-confirm/' . $bookingId);
            }
            setFlashMessage('error', 'Card payment could not be saved. Please try again.');
        } catch (Throwable $e) {
            setFlashMessage('error', 'Card payment failed: ' . $e->getMessage());
        }

        redirect('/booking-card/' . $bookingId);
    }

    public function confirm($id = null)
    {
        requireApprovedAccess();

        $bookingId = $id ?? ($_GET['id'] ?? null);

        if (!$bookingId) {
            redirect('/dashboard');
        }

        $booking = $this->bookingModel->getWithDetails($bookingId);

        if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
            setFlashMessage('error', 'Booking not found.');
            redirect('/dashboard');
        }

        $payment = $this->paymentModel->findByBookingId((int) $bookingId);

        include APP_PATH . '/views/bookings/confirmation.php';
    }
}
