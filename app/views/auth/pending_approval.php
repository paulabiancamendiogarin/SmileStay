<?php $pageTitle = 'Pending Approval - ' . APP_NAME; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<section class="auth-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card auth-card border-0 shadow-sm">
                    <div class="card-body p-5 text-center">
                        <i class="bi bi-hourglass-split display-3 text-warning"></i>
                        <h2 class="mt-3 mb-2">Account Pending Approval</h2>
                        <p class="text-muted">
                            Hello, <strong><?= htmlspecialchars($user['name'] ?? $_SESSION['user_name'] ?? '') ?></strong>.
                            Your account is waiting for an administrator to approve it.
                        </p>

                        <span class="badge bg-warning text-dark fs-6 mb-4">Pending</span>

                        <div class="alert alert-light border text-start small mb-4">
                            <ul class="mb-0 ps-3">
                                <li>One-time QR setup is complete<?= !empty($user['qr_verified']) ? '' : ' (in progress)' ?>.</li>
                                <li>You cannot book hotels or access the dashboard until approved.</li>
                                <li>You will receive full access after approval — future logins skip QR scanning.</li>
                            </ul>
                        </div>

                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <a href="<?= APP_URL ?>/logout" class="btn btn-outline-secondary">
                                <i class="bi bi-box-arrow-right me-1"></i>Sign out
                            </a>
                            <a href="<?= APP_URL ?>/pending-approval" class="btn btn-primary">
                                <i class="bi bi-arrow-clockwise me-1"></i>Check again
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
