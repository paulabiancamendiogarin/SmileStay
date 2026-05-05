

<?php $pageTitle = 'Dashboard - ' . APP_NAME; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<?php if (!empty($showAuthSuccessAnimation)): ?>
<style>
@keyframes authPulse {
    0% { transform: scale(.94); opacity: 0; }
    35% { transform: scale(1); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}
.auth-success-overlay {
    position: fixed; inset: 0; z-index: 1080;
    display: flex; align-items: center; justify-content: center;
    background: rgba(25, 135, 84, 0.12);
    animation: fadeBG .6s ease forwards;
    pointer-events: none;
}
.auth-success-overlay .bubble {
    animation: authPulse .65s ease-out forwards;
    background: #fff; border-radius: 1rem;
    padding: 1.25rem 2rem; box-shadow: 0 .5rem 2rem rgba(0,0,0,.12);
    border: 2px solid rgba(25,135,84,.35);
}
@keyframes fadeBG { from { opacity: 0; } to { opacity: 1; } }
</style>
<div class="auth-success-overlay" id="authSuccessFx">
    <div class="bubble text-center">
        <i class="bi bi-check-circle-fill text-success display-4 d-block mb-2"></i>
        <strong class="text-success">You're in!</strong>
        <div class="small text-muted mt-1">OTP verified successfully.</div>
    </div>
</div>
<script>
setTimeout(function () {
    var el = document.getElementById('authSuccessFx');
    if (el) { el.style.opacity = '0'; el.style.transition = 'opacity .5s'; setTimeout(function(){ el.remove(); }, 500); }
}, 1400);
</script>
<?php endif; ?>

<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col">
                <h2 class="fw-bold">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h2>
                <p class="text-muted mb-0">Manage your bookings and account</p>
            </div>
        </div>

       
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <?= $stats['total'] ?? 0 ?>
                                <small>Total Bookings</small>
                            </div>
                            <i class="bi bi-calendar-check display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <?= $stats['confirmed'] ?? 0 ?>
                                <small>Confirmed</small>
                            </div>
                            <i class="bi bi-check-circle display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <?= $stats['pending'] ?? 0 ?>
                                <small>Pending</small>
                            </div>
                            <i class="bi bi-hourglass-split display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                 <?= $stats['completed'] ?? 0 ?>
                                <small>Completed</small>
                            </div>
                            <i class="bi bi-flag display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
           
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Bookings</h5>
                        <a href="<?= APP_URL ?>/my-bookings" class="btn btn-sm btn-outline-primary">View All</a>
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
                                            <th>Hotel</th>
                                            <th>Check-in</th>
                                            <th>Check-out</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($bookings, 0, 5) as $booking): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($booking['hotel_name']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($booking['room_type']) ?></small>
                                                </td>
                                                <td><?= date('M d, Y', strtotime($booking['check_in'])) ?></td>
                                                <td><?= date('M d, Y', strtotime($booking['check_out'])) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $booking['status'] === 'confirmed' ? 'success' : 
                                                        ($booking['status'] === 'pending' ? 'warning' : 
                                                        ($booking['status'] === 'cancelled' ? 'danger' : 'secondary')) ?>">
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

          
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?= APP_URL ?>/hotels" class="btn btn-primary">
                                <i class="bi bi-buildings me-2"></i>Browse Hotels
                            </a>
                            <a href="<?= APP_URL ?>/map" class="btn btn-outline-primary">
                                <i class="bi bi-geo-alt me-2"></i>View Map
                            </a>
                            <a href="<?= APP_URL ?>/my-bookings" class="btn btn-outline-secondary">
                                <i class="bi bi-calendar-check me-2"></i>My Bookings
                            </a>
                        </div>
                    </div>
                </div>

                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Account Information</h5>
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

<?php include APP_PATH . '/views/layouts/footer.php'; ?>