<?php $pageTitle = 'Card Payment - ' . APP_NAME; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="text-center mb-4">
                    <span class="badge bg-primary mb-2"><i class="bi bi-credit-card me-1"></i>Card Payment</span>
                    <h2 class="fw-bold">Pay with Credit/Debit Card</h2>
                    <p class="text-muted mb-0">Demo processing. Do not use real card details.</p>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="alert alert-warning small">
                            <strong>Demo only:</strong> this form does not connect to a real payment gateway.
                            For security, we only store transaction id + last 4 digits.
                        </div>

                        <div class="mb-3 bg-light rounded p-3 small">
                            <div><strong>Booking:</strong> <code><?= htmlspecialchars($booking['booking_reference']) ?></code></div>
                            <div><strong>Hotel:</strong> <?= htmlspecialchars($booking['hotel_name']) ?></div>
                            <div><strong>Total:</strong> ₱<?= number_format((float) $booking['total_price'], 2) ?></div>
                        </div>

                        <form method="POST" action="<?= APP_URL ?>/booking-card-process/<?= (int) $booking['id'] ?>" class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Name on card</label>
                                <input type="text" name="card_name" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Card number</label>
                                <input type="text" name="card_number" inputmode="numeric" class="form-control" placeholder="•••• •••• •••• ••••" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expiry (MM/YY)</label>
                                <input type="text" name="exp" class="form-control" placeholder="MM/YY" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CVV</label>
                                <input type="password" name="cvv" inputmode="numeric" class="form-control" placeholder="•••" required>
                            </div>

                            <div class="col-12 d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-check2-circle me-2"></i>Pay now
                                </button>
                            </div>
                            <div class="col-12 text-center">
                                <a href="<?= APP_URL ?>/my-bookings" class="small text-muted">Cancel and go back</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>

