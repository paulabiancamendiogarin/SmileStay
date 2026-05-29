<?php $pageTitle = 'Booking History'; ?>
<?php include APP_PATH . '/views/layouts/admin_header.php'; ?>

<div class="mb-2 animate-on-scroll">
    <p class="text-muted mb-0">User details, payments, hotel info, and approval status.</p>
</div>

<div class="d-flex flex-wrap justify-content-end mb-4 gap-2 animate-on-scroll">
        <a href="<?= APP_URL ?>/admin-users" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-people me-1"></i>Manage Users
        </a>
    </div>

    <div class="card admin-card filter-card mb-4 animate-on-scroll">
        <div class="card-body p-4">
            <form method="GET" action="<?= APP_URL ?>/admin-bookings" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Search</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                           placeholder="Ref, guest, hotel…" value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Booking status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Payment</label>
                    <select name="payment_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach (['unpaid', 'pending', 'paid'] as $ps): ?>
                        <option value="<?= $ps ?>" <?= ($filters['payment_status'] ?? '') === $ps ? 'selected' : '' ?>><?= ucfirst($ps) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">User approved</label>
                    <select name="user_approved" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="1" <?= ($filters['user_approved'] ?? '') === '1' ? 'selected' : '' ?>>Approved</option>
                        <option value="0" <?= ($filters['user_approved'] ?? '') === '0' ? 'selected' : '' ?>>Pending</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Sort by</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="created_at" <?= ($filters['sort'] ?? '') === 'created_at' ? 'selected' : '' ?>>Booked date</option>
                        <option value="check_in" <?= ($filters['sort'] ?? '') === 'check_in' ? 'selected' : '' ?>>Check-in</option>
                        <option value="total_price" <?= ($filters['sort'] ?? '') === 'total_price' ? 'selected' : '' ?>>Amount</option>
                        <option value="guest_name" <?= ($filters['sort'] ?? '') === 'guest_name' ? 'selected' : '' ?>>Guest</option>
                        <option value="hotel_name" <?= ($filters['sort'] ?? '') === 'hotel_name' ? 'selected' : '' ?>>Hotel</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small">Order</label>
                    <select name="dir" class="form-select form-select-sm">
                        <option value="DESC" <?= strtoupper($filters['dir'] ?? '') === 'DESC' ? 'selected' : '' ?>>Desc</option>
                        <option value="ASC" <?= strtoupper($filters['dir'] ?? '') === 'ASC' ? 'selected' : '' ?>>Asc</option>
                    </select>
                </div>
                <div class="col-md-12 col-lg-auto">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Apply</button>
                    <a href="<?= APP_URL ?>/admin-bookings" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card admin-card animate-on-scroll">
        <div class="card-body p-0">
            <?php if (empty($bookings)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x display-4 text-muted"></i>
                    <p class="text-muted mt-3 mb-0">No bookings match your filters.</p>
                </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Guest</th>
                            <th>Hotel / Room</th>
                            <th>Dates</th>
                            <th>Amount</th>
                            <th>Booking</th>
                            <th>Payment</th>
                            <th>User</th>
                            <th>Proof</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                        <?php
                            $bookingBadge = bookingStatusBadge($booking['status']);
                            $payStatus = $booking['payment_status'] ?? 'unpaid';
                            if (($booking['payment_record_status'] ?? '') === 'verified') {
                                $payStatus = 'paid';
                            } elseif (($booking['payment_record_status'] ?? '') === 'pending' && !empty($booking['payment_proof_image'])) {
                                $payStatus = 'pending';
                            }
                            $payBadge = paymentStatusBadge($payStatus);
                            $proofPath = $booking['payment_proof'] ?? $booking['payment_proof_image'] ?? null;
                        ?>
                        <tr>
                            <td><code class="small"><?= htmlspecialchars($booking['booking_reference']) ?></code></td>
                            <td>
                                <strong class="d-block"><?= htmlspecialchars($booking['guest_name'] ?? '—') ?></strong>
                                <small class="text-muted"><?= htmlspecialchars($booking['guest_email'] ?? '') ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($booking['hotel_name'] ?? '') ?></strong>
                                <?php if (!empty($booking['hotel_location'])): ?>
                                    <small class="text-muted d-block"><?= htmlspecialchars($booking['hotel_location']) ?></small>
                                <?php endif; ?>
                                <small class="text-muted"><i class="bi bi-door-open"></i> <?= htmlspecialchars($booking['room_type'] ?? '') ?></small>
                            </td>
                            <td class="small text-nowrap">
                                <?= date('M d', strtotime($booking['check_in'])) ?> –
                                <?= date('M d, Y', strtotime($booking['check_out'])) ?>
                            </td>
                            <td class="text-nowrap">₱<?= number_format((float) $booking['total_price'], 0) ?></td>
                            <td>
                                <span class="badge bg-<?= $bookingBadge['class'] ?>"><?= $bookingBadge['label'] ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $payBadge['class'] ?>"><?= $payBadge['label'] ?></span>
                                <?php if (!empty($booking['payment_reference'])): ?>
                                    <small class="d-block text-muted"><code><?= htmlspecialchars($booking['payment_reference']) ?></code></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= approvalBadgeClass(!empty($booking['user_is_approved'])) ?>">
                                    <?= !empty($booking['user_is_approved']) ? 'Approved' : 'Pending' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($proofPath): ?>
                                    <a href="<?= APP_URL ?>/<?= htmlspecialchars(ltrim($proofPath, '/')) ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-image"></i> View
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <form action="<?= APP_URL ?>/admin-update-booking" method="POST" class="d-flex gap-1">
                                        <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                        <select name="status" class="form-select form-select-sm" style="max-width:110px">
                                            <?php foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $booking['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-sm btn-primary" title="Update status"><i class="bi bi-check"></i></button>
                                    </form>
                                    <?php if (!empty($booking['payment_id']) && ($booking['payment_record_status'] ?? '') === 'pending' && $proofPath): ?>
                                    <form method="POST" action="<?= APP_URL ?>/admin-verify-payment">
                                        <input type="hidden" name="payment_id" value="<?= (int) $booking['payment_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success" title="Verify payment"
                                                onclick="return confirm('Mark payment as verified?');">
                                            <i class="bi bi-wallet2"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 border-top">
                <div class="small text-muted">
                    Showing page <strong><?= (int)($currentPage ?? 1) ?></strong> of <strong><?= (int)($totalPages ?? 1) ?></strong>
                    (<?= (int)($totalRows ?? 0) ?> records)
                </div>
                <?php
                    $qs = $_GET;
                    $perPageVal = (int)($perPage ?? 15);
                ?>
                <nav aria-label="Bookings pagination">
                    <ul class="pagination pagination-sm mb-0">
                        <?php
                            $cp = (int)($currentPage ?? 1);
                            $tp = (int)($totalPages ?? 1);

                            $makeUrl = function(int $p) use ($qs) {
                                $qs2 = $qs;
                                $qs2['page'] = $p;
                                return APP_URL . '/admin-bookings?' . http_build_query($qs2);
                            };
                        ?>
                        <li class="page-item <?= $cp <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars($makeUrl(max(1, $cp - 1))) ?>">Prev</a>
                        </li>
                        <li class="page-item disabled"><span class="page-link"><?= $cp ?></span></li>
                        <li class="page-item <?= $cp >= $tp ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars($makeUrl(min($tp, $cp + 1))) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/admin_footer.php'; ?>
