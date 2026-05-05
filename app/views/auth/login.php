<?php $pageTitle = 'Login - ' . APP_NAME; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<section class="auth-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card auth-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-building display-4 text-primary"></i>
                            <h3 class="mt-3 mb-1">Welcome Back</h3>
                            <p class="text-muted">Sign in to your account</p>
                        </div>

                        <?php if (isset($_GET['registered'])): ?>
                            <div class="alert alert-success auth-reg-success mb-4">
                                <i class="bi bi-stars me-2"></i>Welcome aboard — your email is verified. Sign in below.
                            </div>
                            <style>
                            @keyframes regPop {
                                0% { transform: translateY(8px); opacity: 0; }
                                100% { transform: translateY(0); opacity: 1; }
                            }
                            .auth-reg-success { animation: regPop .55s ease-out; border-width: 2px; }
                            </style>
                        <?php endif; ?>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= APP_URL ?>/login">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="your@email.com" required
                                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Enter your password" required>

                                    <!-- TOGGLE BUTTON -->
                                    <span class="input-group-text" onclick="togglePassword('password', this)" style="cursor:pointer;">
                                        <i class="bi bi-eye"></i>
                                    </span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                            </button>

                            <p class="text-center text-muted mb-0">
                                Don't have an account? 
                                <a href="<?= APP_URL ?>/register" class="text-primary">Register here</a>
                            </p>
                        </form>

                        <div class="mt-4 p-3 bg-light rounded">
                            <small class="text-muted">
                                <strong>Demo:</strong> Admin / User passwords are still <code>password</code> — after login/register,
                                Gmail OTP is emailed when SMTP is configured in <code>config/config.php</code>
                                (App Password required).
                            </small>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<script>
document.addEventListener('DOMContentLoaded', function () {

    window.togglePassword = function(fieldId, el) {
        const input = document.getElementById(fieldId);
        const icon = el.querySelector("i");

        if (!input) return;

        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace("bi-eye", "bi-eye-slash");
        } else {
            input.type = "password";
            icon.classList.replace("bi-eye-slash", "bi-eye");
        }
    };

});
</script>
<?php include APP_PATH . '/views/layouts/footer.php'; ?>