<?php $pageTitle = 'GCash Payments'; ?>
<?php include APP_PATH . '/views/layouts/admin_header.php'; ?>

<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="mb-4">
                <i class="bi bi-qr-code-scan me-2"></i>GCash payments & proof
            </h4>

            <?php if (empty($payments)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-wallet2 display-4 d-block mb-3"></i>
                    No payment records yet.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>Guest</th>
                                <th>Hotel</th>
                                <th>Amount</th>
                                <th>Proof</th>
                                <th>Status</th>
                                <th width="140">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $p): ?>
                                <?php
                                    $payClass = $p['status'] === 'verified' ? 'success' : 'warning';
                                ?>
                                <tr>
                                    <td>
                                        <code><?= htmlspecialchars($p['reference_code']) ?></code><br>
                                        <small class="text-muted">Booking <?= (int) $p['booking_id'] ?></small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($p['guest_name']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($p['guest_email']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($p['hotel_name']) ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($p['room_type']) ?></small>
                                    </td>
                                    <td>₱<?= number_format((float) $p['amount'], 2) ?></td>
                                    <td>
                                        <?php if (!empty($p['proof_image'])): ?>
                                            <a href="<?= APP_URL ?>/<?= htmlspecialchars($p['proof_image']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-image"></i> View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $payClass ?>"><?= $p['status'] === 'verified' ? 'Paid' : 'Pending' ?></span>
                                    </td>
                                    <td>
                                        <?php if ($p['status'] === 'pending' && !empty($p['proof_image'])): ?>
                                            <form action="<?= APP_URL ?>/admin-verify-payment" method="POST"
                                                  onsubmit="return confirm('Mark this payment as verified (paid)?');">
                                                <input type="hidden" name="payment_id" value="<?= (int) $p['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success w-100">
                                                    <i class="bi bi-check-lg"></i> Verify
                                                </button>
                                            </form>
                                        <?php elseif ($p['status'] === 'pending'): ?>
                                            <span class="small text-muted">Awaiting proof</span>
                                        <?php else: ?>
                                            <span class="small text-muted">Done</span>
                                        <?php endif; ?>
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
