<?php $pageTitle = 'Admin Dashboard - ' . APP_NAME; ?>
<?php include APP_PATH . '/views/layouts/admin_header.php'; ?>

<section class="admin-main">
    <div class="container-fluid">

        <!-- TITLE -->
        <div class="row mb-4">
            <div class="col">
                <h2 class="fw-bold">
                    <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
                </h2>
                <p class="text-muted mb-0">Overview of bookings and system activity</p>
            </div>
        </div>

        <?php if (!empty($pendingUserCount)): ?>
        <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <span><i class="bi bi-person-exclamation me-2"></i><strong><?= (int) $pendingUserCount ?></strong> user(s) awaiting approval.</span>
            <a href="<?= APP_URL ?>/admin-users" class="btn btn-sm btn-warning">Review users</a>
        </div>
        <?php endif; ?>

        <!-- STATS -->
        <div class="row g-4 mb-5">

            <div class="col-md-3">
                <div class="card bg-primary text-white shadow-sm">
                    <div class="card-body text-center">
                        <h3><?= $stats['total'] ?? 0 ?></h3>
                        <small>Total Bookings</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-dark shadow-sm">
                    <div class="card-body text-center">
                        <h3><?= $stats['pending'] ?? 0 ?></h3>
                        <small>Pending</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-success text-white shadow-sm">
                    <div class="card-body text-center">
                        <h3><?= $stats['confirmed'] ?? 0 ?></h3>
                        <small>Confirmed</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-secondary text-white shadow-sm">
                    <div class="card-body text-center">
                        <h3><?= $stats['completed'] ?? 0 ?></h3>
                        <small>Completed</small>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">

            <!-- RECENT BOOKINGS -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between">
                        <h5 class="mb-0">Recent Bookings</h5>
                        <a href="<?= APP_URL ?>/admin-bookings" class="btn btn-sm btn-primary">
                            Manage Bookings
                        </a>
                    </div>

                    <div class="card-body">

                        <?php if (empty($bookings)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-calendar-x display-4 text-muted"></i>
                                <p class="text-muted mt-2">No bookings yet</p>
                            </div>

                        <?php else: ?>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Guest</th>
                                            <th>Hotel</th>
                                            <th>Dates</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php foreach (array_slice($bookings, 0, 5) as $booking): ?>
                                            <tr>

                                                <td>
                                                    <strong><?= htmlspecialchars($booking['guest_name'] ?? '') ?></strong><br>
                                                    <small><?= htmlspecialchars($booking['guest_email'] ?? '') ?></small>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($booking['hotel_name'] ?? '') ?><br>
                                                    <small><?= htmlspecialchars($booking['room_type'] ?? '') ?></small>
                                                </td>

                                                <td>
                                                    <?= date('M d', strtotime($booking['check_in'])) ?> -
                                                    <?= date('M d', strtotime($booking['check_out'])) ?>
                                                </td>

                                                <td>
                                                    <span class="badge bg-<?=
                                                        $booking['status'] === 'confirmed' ? 'success' :
                                                        ($booking['status'] === 'pending' ? 'warning' :
                                                        ($booking['status'] === 'cancelled' ? 'danger' : 'secondary'))
                                                    ?>">
                                                        <?= ucfirst($booking['status']) ?>
                                                    </span>
                                                </td>

                                                <td>₱<?= number_format($booking['total_price'], 0) ?></td>

                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>

                                </table>
                            </div>

                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <!-- QUICK ACTIONS -->
            <div class="col-lg-4">

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Admin Actions</h5>
                    </div>

                    <div class="card-body">
                        <div class="d-grid gap-2">

                            <a href="<?= APP_URL ?>/admin-bookings" class="btn btn-primary">
                                <i class="bi bi-calendar-check me-2"></i>Manage Bookings
                            </a>

                            <a href="<?= APP_URL ?>/hotels" class="btn btn-outline-primary">
                                <i class="bi bi-buildings me-2"></i>Manage Hotels
                            </a>

                            <a href="<?= APP_URL ?>/map" class="btn btn-outline-secondary">
                                <i class="bi bi-geo-alt me-2"></i>View Map
                            </a>

                        </div>
                    </div>
                </div>

                <!-- ADMIN INFO -->
                <div class="card mt-4 shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Admin Info</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($_SESSION['user_name']) ?></p>
                        <p class="mb-0"><strong>Email:</strong> <?= htmlspecialchars($_SESSION['user_email']) ?></p>
                    </div>
                </div>

            </div>

        </div>

    </div>
</section>

