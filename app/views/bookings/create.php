<?php $pageTitle = 'Book Room - ' . APP_NAME; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/hotel/<?= $hotel['id'] ?>"><?= htmlspecialchars($hotel['hotel_name']) ?></a></li>
                        <li class="breadcrumb-item active">Book Room</li>
                    </ol>
                </nav>

                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h4 class="mb-0"><i class="bi bi-calendar-check me-2 text-primary"></i>Complete Your Booking</h4>
                    </div>
                    <div class="card-body">
                        
                        <!-- Hotel & Room Info Alignment Fix -->
                        <div class="row mb-4 align-items-center">
                            <div class="col-md-4 col-sm-5">
                                <div class="hotel-card-img">
                                    <img src="<?= APP_URL ?>/uploads/hotels/<?= $hotel['image'] ?? 'default.jpg' ?>"
                                         onerror="this.onerror=null;this.src='<?= APP_URL ?>/uploads/hotels/default.jpg';"
                                         class="img-fluid rounded shadow-sm" 
                                         style="width: 100%; height: 180px; object-fit: cover;">
                                </div>
                            </div>
                            <div class="col-md-8 col-sm-7 mt-3 mt-sm-0">
                                <h5 class="fw-bold mb-1"><?= htmlspecialchars($hotel['hotel_name']) ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($hotel['location']) ?>
                                </p>
                                <div class="mb-2">
                                    <span class="badge bg-light text-dark border">
                                        <strong class="text-secondary">Room:</strong> <?= htmlspecialchars($room['room_type']) ?>
                                    </span>
                                    <small class="text-muted ms-2"><i class="bi bi-people me-1"></i>Max <?= $room['capacity'] ?> guests</small>
                                </div>
                                <p class="h4 text-primary fw-bold mb-0">
                                    ₱<?= number_format($room['price'], 0) ?> 
                                    <small class="text-muted fs-6 fw-normal">/ night</small>
                                </p>
                            </div>
                        </div>

                        <hr class="text-muted opacity-25">

                        <form action="<?= APP_URL ?>/booking-store" method="POST" id="bookingForm">
                            <input type="hidden" name="hotel_id" value="<?= $hotel['id'] ?>">
                            <input type="hidden" name="room_id" value="<?= $room['id'] ?>">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="check_in" class="form-label fw-semibold">Check-in Date</label>
                                    <input type="date" class="form-control" id="check_in" name="check_in" 
                                           value="<?= $checkIn ?>" required min="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="check_out" class="form-label fw-semibold">Check-out Date</label>
                                    <input type="date" class="form-control" id="check_out" name="check_out" 
                                           value="<?= $checkOut ?>" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="guests" class="form-label fw-semibold">Number of Guests</label>
                                    <select class="form-select" id="guests" name="guests" required>
                                        <?php for ($i = 1; $i <= $room['capacity']; $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?> Guest<?= $i > 1 ? 's' : '' ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nights</label>
                                    <input type="text" class="form-control bg-light" id="nights" readonly value="1 night">
                                </div>
                                <div class="col-12">
                                    <label for="special_requests" class="form-label fw-semibold">Special Requests (Optional)</label>
                                    <textarea class="form-control" id="special_requests" name="special_requests" 
                                              rows="3" placeholder="Any special requests or requirements..."></textarea>
                                </div>
                            </div>

                            <hr class="my-4 text-muted opacity-25">

                            <div class="bg-light p-4 rounded mb-4 border">
                                <h6 class="mb-3 fw-bold">Price Summary</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>₱<?= number_format($room['price'], 0) ?> x <span id="nightsCount" class="fw-bold">1</span> night(s)</span>
                                    <span id="subtotal" class="fw-semibold">₱<?= number_format($room['price'], 0) ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="h5 mb-0">Total</strong>
                                    <strong class="text-primary h4 mb-0" id="total">₱<?= number_format($room['price'], 0) ?></strong>
                                </div>
                            </div>

                            <div class="d-grid gap-3">
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                    <i class="bi bi-check-circle me-2"></i>Confirm Booking
                                </button>
                                <a href="<?= APP_URL ?>/hotel/<?= $hotel['id'] ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Hotel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roomPrice = <?= $room['price'] ?>;
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    const nightsDisplay = document.getElementById('nights');
    const nightsCount = document.getElementById('nightsCount');
    const subtotal = document.getElementById('subtotal');
    const total = document.getElementById('total');

    function calculatePrice() {
        const checkIn = new Date(checkInInput.value);
        const checkOut = new Date(checkOutInput.value);

        if (checkIn && checkOut && checkOut > checkIn) {
            const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
            const totalPrice = roomPrice * nights;

            nightsDisplay.value = nights + ' night' + (nights > 1 ? 's' : '');
            nightsCount.textContent = nights;
            subtotal.textContent = '₱' + totalPrice.toLocaleString();
            total.textContent = '₱' + totalPrice.toLocaleString();
        }
    }

    checkInInput.addEventListener('change', function() {
        const checkIn = new Date(this.value);
        checkIn.setDate(checkIn.getDate() + 1);
        checkOutInput.min = checkIn.toISOString().split('T')[0];
        
        if (new Date(checkOutInput.value) <= new Date(this.value)) {
            checkOutInput.value = checkIn.toISOString().split('T')[0];
        }
        
        calculatePrice();
    });

    checkOutInput.addEventListener('change', calculatePrice);
    calculatePrice();
});
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>