<?php $pageTitle = 'Verify registration - ' . APP_NAME; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<section class="auth-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card auth-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-shield-check display-4 text-primary"></i>
                            <h3 class="mt-3 mb-1">Set up Google Authenticator</h3>
                            <p class="text-muted small">Scan this QR for<br><strong><?= htmlspecialchars($email ?? '') ?></strong><br>then enter the 6-digit code.</p>
                        </div>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mb-3">
                            <img src="<?= htmlspecialchars($totpQrUrl ?? '') ?>" alt="Google Authenticator QR" class="img-fluid border rounded p-2 bg-white" style="max-width: 240px;">
                        </div>
                        <div class="alert alert-light border small">
                            <div><strong>Issuer:</strong> <?= htmlspecialchars($totpIssuer ?? APP_NAME) ?></div>
                            <div><strong>Manual key:</strong> <code><?= htmlspecialchars($totpSecretForView ?? '') ?></code></div>
                        </div>

                        <form method="POST" action="<?= APP_URL ?>/verify-register-otp" class="mb-3">
                            <div class="mb-3">
                                <label for="otp_code" class="form-label">6-digit code</label>
                                <input type="text" inputmode="numeric" pattern="\d{6}" maxlength="6" class="form-control form-control-lg text-center letter-spacing-otp" id="otp_code" name="otp_code" placeholder="••••••" required autocomplete="one-time-code">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                <i class="bi bi-person-check me-2"></i>Verify & create account
                            </button>
                        </form>

                        <p class="text-center mt-4 mb-0 small text-muted">
                            <a href="<?= APP_URL ?>/register">← Edit registration</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.letter-spacing-otp { letter-spacing: 0.35em; font-variant-numeric: tabular-nums; }
@keyframes otp-pop-in { 0% { transform: scale(.85); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
.auth-card { animation: otp-pop-in .45s ease-out; }
@keyframes check-burst { 0% { transform: scale(.5); opacity: 0; } 55% { transform: scale(1.08); opacity: 1; } 100% { transform: scale(1); opacity: 1; } }
</style>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
