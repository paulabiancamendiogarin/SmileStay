<?php $pageTitle = 'Manage Bookings'; ?>
<?php include APP_PATH . '/views/layouts/admin_header.php'; ?>

<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-body">

            <h4 class="mb-4">
                <i class="bi bi-calendar-check me-2"></i>Manage Bookings
            </h4>

            <?php if (empty($bookings)): ?>

                <!-- EMPTY STATE -->
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x display-1 text-muted"></i>
                    <h4 class="mt-3">No Bookings Yet</h4>
                    <p class="text-muted">Bookings will appear here once users start booking.</p>
                </div>

            <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>Guest</th>
                                <th>Hotel</th>
                                <th>Room</th>
                                <th>Dates</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th width="220">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($bookings as $booking): ?>

                                <?php
                                    $statusClass = match($booking['status']) {
                                        'confirmed' => 'success',
                                        'pending' => 'warning',
                                        'cancelled' => 'danger',
                                        'completed' => 'secondary',
                                        default => 'secondary'
                                    };
                                ?>

                                <tr>

                                    <!-- REF -->
                                    <td>
                                        <code><?= htmlspecialchars($booking['booking_reference']) ?></code>
                                    </td>

                                    <!-- GUEST -->
                                    <td>
                                        <strong><?= htmlspecialchars($booking['guest_name']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($booking['guest_email']) ?></small>
                                    </td>

                                    <!-- HOTEL -->
                                    <td><?= htmlspecialchars($booking['hotel_name']) ?></td>

                                    <!-- ROOM -->
                                    <td><?= htmlspecialchars($booking['room_type']) ?></td>

                                    <!-- DATES -->
                                    <td>
                                        <?= date('M d', strtotime($booking['check_in'])) ?> -
                                        <?= date('M d, Y', strtotime($booking['check_out'])) ?>
                                    </td>

                                    <!-- PRICE -->
                                    <td>₱<?= number_format($booking['total_price'], 0) ?></td>

                                    <!-- STATUS -->
                                    <td>
                                        <span class="badge bg-<?= $statusClass ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                    </td>

                                    <!-- ✅ FULL CRUD ACTIONS -->
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">

                                  

                                            <!-- STATUS UPDATE -->
                                            <form action="<?= APP_URL ?>/admin-update-booking" method="POST" class="d-flex">
                                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">

                                                <select name="status" class="form-select form-select-sm me-1">
                                                    <option value="pending" <?= $booking['status']=='pending'?'selected':'' ?>>Pending</option>
                                                    <option value="confirmed" <?= $booking['status']=='confirmed'?'selected':'' ?>>Confirmed</option>
                                                    <option value="completed" <?= $booking['status']=='completed'?'selected':'' ?>>Completed</option>
                                                    <option value="cancelled" <?= $booking['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
                                                </select>

                                                <button class="btn btn-sm btn-primary">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            </form>

                                            <!-- DELETE -->
                                            <form action="<?= APP_URL ?>/admin-delete-booking" method="POST">
                                                  
                                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>   
                                            </form>

                                        </div>
                                    </td>

                                </tr>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>