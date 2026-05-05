
<?php $pageTitle = 'GCash Payment - ' . APP_NAME; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="text-center mb-4">
                    <span class="badge bg-warning text-dark mb-2"><i class="bi bi-wallet2 me-1"></i>GCash Payment</span>
                    <h2 class="fw-bold">Pay for your booking</h2>
                    <p class="text-muted mb-0">Use the payment picture/details below, then upload your proof of payment.</p>
                </div>
                <div class="alert alert-info">
                    <strong>Note:</strong> QR scanning is disabled. Please pay manually to
                    <strong><?= htmlspecialchars(GCASH_NUMBER) ?></strong>, exact amount shown below, and include the reference code.
                </div>

                <div class="alert alert-danger text-center mb-4 py-3 border-danger border-2" id="countdownWrap">
                    <span class="small text-uppercase text-muted d-block mb-1">Time remaining</span>
                    <span class="display-6 fw-bold text-danger font-monospace" id="countdownDisplay">—</span>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100 border-primary border-opacity-25">
                            <div class="card-header bg-primary text-white">
                                <i class="bi bi-image me-2"></i>GCash payment picture
                            </div>
                            <div class="card-body text-center">
                                <?php if (!empty($gcashPaymentImageExists)): ?>
                                    <img src="<?= APP_URL ?>/<?= htmlspecialchars($gcashPaymentImage) ?>" alt="GCash payment picture"
                                         class="img-fluid rounded border mb-3" style="max-width:280px;">
                                <?php else: ?>
                                    <div class="p-4 bg-light rounded mb-3">
                                        <p class="small text-muted mb-0">
                                            Payment picture not found at
                                            <code><?= htmlspecialchars(GCASH_PAYMENT_IMAGE) ?></code>.
                                            Add your image file there to display it.
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <div class="small text-start bg-light rounded p-3">
                                    <p class="mb-1"><strong>GCash number:</strong> <?= htmlspecialchars(GCASH_NUMBER) ?></p>
                                    <p class="mb-1"><strong>Amount:</strong> ₱<?= number_format((float) $payment['amount'], 2) ?></p>
                                    <p class="mb-0"><strong>Reference:</strong> <code><?= htmlspecialchars($payment['reference_code']) ?></code></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white border-bottom">
                                <i class="bi bi-receipt me-2"></i>Booking summary
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($booking['hotel_name']) ?></h5>
                                <p class="text-muted small mb-2"><?= htmlspecialchars($booking['hotel_location']) ?></p>
                                <p class="mb-2"><i class="bi bi-door-open me-1"></i><?= htmlspecialchars($booking['room_type']) ?></p>
                                <p class="mb-2">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    <?= date('M d, Y', strtotime($booking['check_in'])) ?> → <?= date('M d, Y', strtotime($booking['check_out'])) ?>
                                </p>
                                <p class="mb-2"><i class="bi bi-people me-1"></i><?= (int) $booking['guests'] ?> guest(s)</p>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Total</strong>
                                    <span class="h5 text-primary mb-0">₱<?= number_format((float) $booking['total_price'], 2) ?></span>
                                </div>
                                <p class="small text-muted mt-2 mb-0">
                                    Booking ref: <code><?= htmlspecialchars($booking['booking_reference']) ?></code>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4 shadow-sm">
                    <div class="card-header">
                        <i class="bi bi-cloud-upload me-2"></i>Upload proof of payment
                    </div>
                    <div class="card-body">
                        <?php if (!empty($payment['proof_image'])): ?>
                            <div class="alert alert-success mb-3">
                                <i class="bi bi-check-circle me-2"></i>Proof received. Status:
                                <strong><?= $payment['status'] === 'verified' ? 'Verified (paid)' : 'Pending admin verification' ?></strong>
                            </div>
                            <p class="small mb-3"><a href="<?= APP_URL ?>/<?= htmlspecialchars($payment['proof_image']) ?>" target="_blank">View uploaded file</a></p>
                        <?php endif; ?>

                        <?php if ($payment['status'] === 'pending'): ?>
                            <form action="<?= APP_URL ?>/booking-payment-upload/<?= (int) $booking['id'] ?>" method="POST"
                                  enctype="multipart/form-data" class="row g-3 align-items-end">
                                <div class="col-md-8">
                                    <label class="form-label">Screenshot / receipt image</label>
                                    <input type="file" name="proof" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp" required>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-upload me-1"></i>Submit proof
                                    </button>
                                </div>
                            </form>
                            <p class="small text-muted mt-2 mb-0">JPEG, PNG, GIF, or WebP — max practical size depends on your PHP upload limits.</p>
                        <?php endif; ?>

                        <hr class="my-4">

                        <div class="d-flex flex-wrap gap-2 justify-content-between">
                            <a href="<?= APP_URL ?>/my-bookings" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>My bookings</a>
                            <?php if ($payment['status'] === 'verified'): ?>
                                <a href="<?= APP_URL ?>/booking-confirm/<?= (int) $booking['id'] ?>" class="btn btn-primary">
                                    <i class="bi bi-check2-circle me-1"></i>View confirmation
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var deadline = <?= isset($paymentExpiresUnix) ? (int)$paymentExpiresUnix : 0 ?>;
    var display = document.getElementById('countdownDisplay');
    var wrap = document.getElementById('countdownWrap');

    function fmt(sec) {
        var m = Math.floor(sec / 60);
        var s = sec % 60;
        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }

    function tick() {
        var left = Math.floor(deadline / 1000 - Date.now() / 1000);
        if (left <= 0) {
            display.textContent = '00:00';
            wrap.classList.remove('alert-danger');
            wrap.classList.add('alert-secondary');
            clearInterval(timer);
            return;
        }
        display.textContent = fmt(left);
    }

    var timer = setInterval(tick, 1000);
    tick();
})();
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
