<?php $pageTitle = 'My Bookings - ' . APP_NAME; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<section class="py-5">
    <div class="container">

     
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">My Bookings</h2>
                <p class="text-muted mb-0">View and manage your hotel reservations</p>
            </div>
            <a href="<?= APP_URL ?>/hotels" class="btn btn-primary">
                <i class="bi bi-plus me-2"></i>New Booking
            </a>
        </div>

        <?php if (empty($bookings)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-calendar-x display-1 text-muted"></i>
                    <h4 class="mt-3">No Bookings Yet</h4>
                    <p class="text-muted">You haven't made any hotel reservations.</p>
                    <a href="<?= APP_URL ?>/hotels" class="btn btn-primary">
                        <i class="bi bi-buildings me-2"></i>Browse Hotels
                    </a>
                </div>
            </div>

        <?php else: ?>

            <div class="row g-4">

                <?php foreach ($bookings as $booking): ?>

                    <?php
                        $statusClass = match($booking['status']) {
                            'confirmed' => 'success',
                            'pending' => 'warning',
                            'cancelled' => 'danger',
                            'completed' => 'secondary',
                            default => 'secondary'
                        };

                        $checkInDate = new DateTime($booking['check_in']);
                        $today = new DateTime('today');
                        $canModify = ($checkInDate > $today) && in_array($booking['status'], ['pending','confirmed']);
                    ?>

                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100">

                            <div class="card-body">

                              
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                    <small class="text-muted"><?= $booking['booking_reference'] ?></small>
                                </div>

                              
                                <h5 class="card-title"><?= htmlspecialchars($booking['hotel_name']) ?></h5>

                                <p class="text-muted small mb-2">
                                    <i class="bi bi-door-open me-1"></i>
                                    <?= htmlspecialchars($booking['room_type']) ?>
                                </p>

                               
                                <div class="row text-center my-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Check-in</small>
                                        <strong><?= date('M d', strtotime($booking['check_in'])) ?></strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Check-out</small>
                                        <strong><?= date('M d', strtotime($booking['check_out'])) ?></strong>
                                    </div>
                                </div>

                              
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="h5 text-primary mb-0">
                                        ₱<?= number_format($booking['total_price'], 0) ?>
                                    </span>
                                </div>

                               
                                <?php if ($canModify): ?>
                                    <div class="d-flex gap-2 flex-wrap">

                                       
                                        <a href="<?= APP_URL ?>/edit-booking/<?= $booking['id'] ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                       
                                        <form action="<?= APP_URL ?>/cancel-booking" method="POST"
                                              onsubmit="return confirm('Cancel this booking?');">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <button class="btn btn-outline-warning btn-sm">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>

                                        
                                        <form action="<?= APP_URL ?>/delete-booking/<?= $booking['id'] ?>" method="POST"
                                              onsubmit="return confirm('Delete this booking permanently?');">
                                            <button class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>

                                    </div>
                                <?php endif; ?>

                            </div>

                           
                            <div class="card-footer bg-white">
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    Booked on <?= date('M d, Y', strtotime($booking['created_at'])) ?>
                                </small>
                            </div>

                        </div>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>
</section>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>