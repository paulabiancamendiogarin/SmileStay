
<?php $pageTitle = 'Booking Confirmed - ' . APP_NAME; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-4">
                    <div class="confirmation-icon">
                        <i class="bi bi-check-circle-fill text-success display-1"></i>
                    </div>
                    <h2 class="mt-3">Booking received</h2>
                    <p class="text-muted">
                        <?php if (!empty($payment) && $payment['status'] === 'verified'): ?>
                            Payment verified. Your reservation is on file pending hotel confirmation when staff updates your booking status.
                        <?php elseif (!empty($payment)): ?>
                            GCash payment is still pending verification. You can check status from <a href="<?= APP_URL ?>/booking-payment/<?= (int)$booking['id'] ?>">the payment page</a>.
                        <?php else: ?>
                            Your reservation request has been recorded.
                        <?php endif; ?>
                    </p>
                </div>

                <div class="card">
                    <div class="card-header bg-success text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Booking Details</h5>
                            <span class="badge bg-white text-success"><?= htmlspecialchars($booking['booking_reference']) ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h6 class="text-muted mb-3">Hotel Information</h6>
                                <p class="mb-1"><strong><?= htmlspecialchars($booking['hotel_name']) ?></strong></p>
                                <p class="text-muted mb-1">
                                    <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($booking['hotel_location']) ?>
                                </p>
                                <p class="mb-0">
                                    <i class="bi bi-door-open me-1"></i><?= htmlspecialchars($booking['room_type']) ?>
                                </p>
                            </div>
                            <div class="col-md-6 mb-4">
                                <h6 class="text-muted mb-3">Guest Information</h6>
                                <p class="mb-1"><strong><?= htmlspecialchars($booking['guest_name']) ?></strong></p>
                                <p class="text-muted mb-1">
                                    <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($booking['guest_email']) ?>
                                </p>
                                <p class="mb-0">
                                    <i class="bi bi-people me-1"></i><?= $booking['guests'] ?> Guest(s)
                                </p>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <small class="text-muted d-block">Check-in</small>
                                    <strong><?= date('M d, Y', strtotime($booking['check_in'])) ?></strong>
                                    <small class="text-muted d-block">From 2:00 PM</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <small class="text-muted d-block">Check-out</small>
                                    <strong><?= date('M d, Y', strtotime($booking['check_out'])) ?></strong>
                                    <small class="text-muted d-block">Until 12:00 PM</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 bg-primary text-white rounded">
                                    <small class="d-block">Total Amount</small>
                                    <strong class="h4">₱<?= number_format($booking['total_price'], 0) ?></strong>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($booking['special_requests'])): ?>
                            <hr>
                            <h6 class="text-muted mb-2">Special Requests</h6>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($booking['special_requests'])) ?></p>
                        <?php endif; ?>

                        <hr>

                        <div class="d-flex justify-content-center gap-3">
                            <a href="<?= APP_URL ?>/my-bookings" class="btn btn-primary">
                                <i class="bi bi-calendar-check me-2"></i>View My Bookings
                            </a>
                            <a href="<?= APP_URL ?>/hotels" class="btn btn-outline-primary">
                                <i class="bi bi-buildings me-2"></i>Browse More Hotels
                            </a>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle me-2"></i>
                    Save your booking reference <strong><?= htmlspecialchars($booking['booking_reference']) ?></strong> for check-in.
                    <?php if (!empty($payment)): ?>
                        Payment reference: <strong><?= htmlspecialchars($payment['reference_code']) ?></strong>
                        — status:
                        <strong><?= $payment['status'] === 'verified' ? 'Paid (verified)' : 'Pending verification' ?></strong>.
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>