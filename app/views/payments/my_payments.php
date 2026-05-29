<?php $pageTitle = 'My Payments - ' . APP_NAME; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">My Payments</h2>
                <p class="text-muted mb-0">Track and update your booking payments.</p>
            </div>
            <a href="<?= APP_URL ?>/my-bookings" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to My Bookings
            </a>
        </div>

        <?php if (empty($payments)): ?>
            <div class="card">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-wallet2 display-4 d-block mb-3"></i>
                    No payment records yet.
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Booking</th>
                            <th>Hotel</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Proof</th>
                            <th>Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($payments as $p): ?>
                        <?php
                            $status = strtolower((string) ($p['status'] ?? 'pending'));
                            $badge = $status === 'verified' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning');
                            $label = $status === 'verified' ? 'Paid' : ($status === 'rejected' ? 'Rejected' : 'Pending Verification');
                            $method = strtolower((string) ($p['payment_method'] ?? 'cash'));
                        ?>
                        <tr>
                            <td>
                                <code><?= htmlspecialchars($p['booking_reference']) ?></code><br>
                                <small class="text-muted">₱<?= number_format((float) $p['amount'], 2) ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($p['hotel_name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($p['room_type']) ?></small>
                            </td>
                            <td><?= strtoupper($method) ?></td>
                            <td><span class="badge bg-<?= $badge ?>"><?= $label ?></span></td>
                            <td>
                                <?php if (!empty($p['proof_image'])): ?>
                                    <a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= APP_URL ?>/<?= htmlspecialchars($p['proof_image']) ?>">
                                        <i class="bi bi-image"></i> View
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted">
                                <?= !empty($p['updated_at']) ? date('M d, Y g:i A', strtotime($p['updated_at'])) : date('M d, Y g:i A', strtotime($p['created_at'])) ?>
                            </td>
                            <td>
                                <a href="<?= APP_URL ?>/booking-payment/<?= (int) $p['booking_id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil-square me-1"></i>Update
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>

