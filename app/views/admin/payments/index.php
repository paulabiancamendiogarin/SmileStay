<?php $pageTitle = 'Payment Management'; ?>
<?php include APP_PATH . '/views/layouts/admin_header.php'; ?>

<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="mb-4"><i class="bi bi-wallet2 me-2"></i>Payment Management</h4>

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
                                <th>Method</th>
                                <th>Reference / Notes</th>
                                <th>Proof</th>
                                <th>Status</th>
                                <th width="140">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $p): ?>
                                <?php
                                    $payClass = $p['status'] === 'verified' ? 'success' : ($p['status'] === 'rejected' ? 'danger' : 'warning');
                                    $method = strtolower((string) ($p['payment_method'] ?? 'gcash'));
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
                                        <?= $method === 'cash' ? 'Cash Payment' : ($method === 'card' ? 'Card Payment' : 'GCash Payment') ?>
                                    </td>
                                    <td class="small">
                                        <?php if (!empty($p['payment_reference']) || !empty($p['transaction_id'])): ?>
                                            <div><strong>Ref:</strong> <code><?= htmlspecialchars($p['payment_reference'] ?? $p['transaction_id']) ?></code></div>
                                        <?php endif; ?>
                                        <?php if (!empty($p['payment_notes'])): ?>
                                            <div class="text-muted"><?= nl2br(htmlspecialchars($p['payment_notes'])) ?></div>
                                        <?php elseif (empty($p['payment_reference']) && empty($p['transaction_id'])): ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
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
                                        <span class="badge bg-<?= $payClass ?>">
                                            <?= $p['status'] === 'verified' ? 'Paid' : ($p['status'] === 'rejected' ? 'Rejected' : 'Pending Verification') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($p['status'] === 'pending' && (!empty($p['proof_image']) || $method === 'card')): ?>
                                            <div class="d-grid gap-1">
                                                <form action="<?= APP_URL ?>/admin-verify-payment" method="POST"
                                                      onsubmit="return confirm('Mark this payment as verified (paid)?');">
                                                    <input type="hidden" name="payment_id" value="<?= (int) $p['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                                        <i class="bi bi-check-lg"></i> Verify
                                                    </button>
                                                </form>
                                                <form action="<?= APP_URL ?>/admin-reject-payment" method="POST"
                                                      onsubmit="return confirm('Reject this payment?');">
                                                    <input type="hidden" name="payment_id" value="<?= (int) $p['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                                        <i class="bi bi-x-lg"></i> Reject
                                                    </button>
                                                </form>
                                            </div>
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
