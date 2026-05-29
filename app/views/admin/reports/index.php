<?php $pageTitle = 'Reports & Analytics'; ?>
<?php include APP_PATH . '/views/layouts/admin_header.php'; ?>

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="mb-1"><i class="bi bi-graph-up me-2"></i>Reports & Analytics</h4>
            <p class="text-muted mb-0 small">Filters, trends, and exports for bookings and revenue.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-secondary"
               href="<?= APP_URL ?>/admin-reports-export?format=csv&from=<?= urlencode($filters['from'] ?? '') ?>&to=<?= urlencode($filters['to'] ?? '') ?>&hotel_id=<?= urlencode($filters['hotel_id'] ?? '') ?>&payment_status=<?= urlencode($filters['payment_status'] ?? '') ?>&booking_status=<?= urlencode($filters['booking_status'] ?? '') ?>">
                <i class="bi bi-download me-1"></i>CSV
            </a>
            <a class="btn btn-sm btn-outline-success"
               href="<?= APP_URL ?>/admin-reports-export?format=excel&from=<?= urlencode($filters['from'] ?? '') ?>&to=<?= urlencode($filters['to'] ?? '') ?>&hotel_id=<?= urlencode($filters['hotel_id'] ?? '') ?>&payment_status=<?= urlencode($filters['payment_status'] ?? '') ?>&booking_status=<?= urlencode($filters['booking_status'] ?? '') ?>">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel
            </a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?= APP_URL ?>/admin-reports" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">From</label>
                    <input type="date" class="form-control form-control-sm" name="from" value="<?= htmlspecialchars($filters['from'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">To</label>
                    <input type="date" class="form-control form-control-sm" name="to" value="<?= htmlspecialchars($filters['to'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Hotel</label>
                    <select class="form-select form-select-sm" name="hotel_id">
                        <option value="">All hotels</option>
                        <?php foreach (($hotels ?? []) as $h): ?>
                            <option value="<?= (int) $h['id'] ?>" <?= (string)($filters['hotel_id'] ?? '') === (string)$h['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($h['hotel_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Payment status</label>
                    <select class="form-select form-select-sm" name="payment_status">
                        <option value="">All</option>
                        <?php foreach (['unpaid','pending','paid'] as $ps): ?>
                            <option value="<?= $ps ?>" <?= ($filters['payment_status'] ?? '') === $ps ? 'selected' : '' ?>><?= ucfirst($ps) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Booking status</label>
                    <select class="form-select form-select-sm" name="booking_status">
                        <option value="">All</option>
                        <?php foreach (['pending','confirmed','completed','cancelled'] as $bs): ?>
                            <option value="<?= $bs ?>" <?= ($filters['booking_status'] ?? '') === $bs ? 'selected' : '' ?>><?= ucfirst($bs) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small">Year</label>
                    <input type="number" class="form-control form-control-sm" name="year" value="<?= (int)($filters['year'] ?? date('Y')) ?>" min="2000" max="2100">
                </div>
                <div class="col-12 col-lg-auto">
                    <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-funnel me-1"></i>Apply</button>
                    <a class="btn btn-outline-secondary btn-sm" href="<?= APP_URL ?>/admin-reports">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small">Total bookings</div>
                    <div class="h3 mb-0"><?= (int)($summary['total_bookings'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small">Total revenue (paid)</div>
                    <div class="h3 mb-0">₱<?= number_format((float)($summary['total_revenue'] ?? 0), 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small">Paid bookings</div>
                    <div class="h3 mb-0"><?= (int)($summary['paid_count'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small">Unpaid/Pending</div>
                    <div class="h3 mb-0"><?= (int)($summary['unpaid_count'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <strong><i class="bi bi-activity me-2"></i>Booking trends (monthly)</strong>
                </div>
                <div class="card-body">
                    <canvas id="monthlyBookingsChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <strong><i class="bi bi-star me-2"></i>Most booked hotels</strong>
                </div>
                <div class="card-body">
                    <?php if (empty($topHotels)): ?>
                        <p class="text-muted small mb-0">No data for current filters.</p>
                    <?php else: ?>
                        <ol class="mb-0">
                            <?php foreach ($topHotels as $h): ?>
                                <li class="mb-1">
                                    <?= htmlspecialchars($h['hotel_name']) ?>
                                    <span class="text-muted small">(<?= (int)$h['bookings_count'] ?>)</span>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <strong><i class="bi bi-person-plus me-2"></i>User registrations (monthly)</strong>
                </div>
                <div class="card-body">
                    <canvas id="monthlyRegistrationsChart" height="140"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
    const labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const monthlyBookings = <?= json_encode(array_values($monthlyBookings ?? array_fill(1, 12, 0))) ?>;
    const monthlyRegs = <?= json_encode(array_values($monthlyRegistrations ?? array_fill(1, 12, 0))) ?>;

    new Chart(document.getElementById('monthlyBookingsChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Bookings',
                data: monthlyBookings,
                borderWidth: 2,
                tension: 0.25
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: true } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    new Chart(document.getElementById('monthlyRegistrationsChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Registrations',
                data: monthlyRegs,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: true } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
})();
</script>

<?php include APP_PATH . '/views/layouts/admin_footer.php'; ?>

