<?php

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    private function redirectByRole(): void
    {
        if (isAdmin()) {
            unset($_SESSION['show_auth_success_animation']);
            redirect('/admin');
        }

        redirect('/dashboard');
    }

    private function clearPendingLogin(): void
    {
        unset(
            $_SESSION['pending_otp_login_user_id'],
            $_SESSION['pending_otp_email'],
            $_SESSION['pending_login_totp_secret'],
            $_SESSION['pending_login_totp_setup'],
            $_SESSION['pending_login_totp_failed_attempts']
        );
    }

    private function clearPendingRegister(): void
    {
        unset(
            $_SESSION['pending_register_name'],
            $_SESSION['pending_register_email'],
            $_SESSION['pending_register_password_hash'],
            $_SESSION['pending_register_totp_secret'],
            $_SESSION['pending_register_totp_failed_attempts']
        );
    }

    private function buildTotpQrUrl(string $email, string $secret, string $issuer): array
    {
        $provisioningUri = TotpService::buildProvisioningUri($email, $secret, $issuer);
        $filename = 'totp_' . md5($email . '|' . $secret . '|' . $issuer);
        $relativeQrPath = QrGenerator::savePng($provisioningUri, $filename);

        if ($relativeQrPath) {
            return [
                'provisioning_uri' => $provisioningUri,
                'qr_url' => APP_URL . '/' . ltrim($relativeQrPath, '/'),
            ];
        }

        return [
            'provisioning_uri' => $provisioningUri,
            'qr_url' => 'https://chart.googleapis.com/chart?chs=240x240&cht=qr&chl=' . rawurlencode($provisioningUri),
        ];
    }

    private function finishLogin(array $user, bool $showAnimation = true): void
    {
        establishUserSession($user);

        if ($showAnimation && !isAdmin()) {
            $_SESSION['show_auth_success_animation'] = true;
        }
    }

    public function login()
    {
        if (isLoggedIn()) {
            if (!userIsApproved()) {
                redirect('/pending-approval');
            }
            $this->redirectByRole();
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!$email || !$password) {
                $error = 'Please fill in all fields.';
            } else {

                $user = $this->userModel->verifyPassword($email, $password);

                if ($user) {

                    $hasTotpSecret = trim((string) ($user['google_auth_secret'] ?? '')) !== '';
                    $qrComplete = !empty($user['qr_verified']);

                    if (userIsApproved($user) && $hasTotpSecret && $qrComplete) {
                        $this->finishLogin($user);
                        setFlashMessage('success', 'Welcome back, ' . $user['name']);
                        $this->redirectByRole();
                    }

                    if (!$hasTotpSecret || !$qrComplete) {
                        $this->clearPendingLogin();
                        $_SESSION['pending_otp_login_user_id'] = $user['id'];
                        $_SESSION['pending_otp_email'] = $user['email'];
                        $_SESSION['pending_login_totp_failed_attempts'] = 0;

                        $existingSecret = trim((string) ($user['google_auth_secret'] ?? ''));
                        if ($existingSecret === '') {
                            $_SESSION['pending_login_totp_secret'] = TotpService::generateSecret();
                            $_SESSION['pending_login_totp_setup'] = true;
                            setFlashMessage('info', 'Scan the QR code once in Google Authenticator, then enter your 6-digit code.');
                        } else {
                            $_SESSION['pending_login_totp_secret'] = $existingSecret;
                            $_SESSION['pending_login_totp_setup'] = true;
                            setFlashMessage('info', 'Complete one-time QR setup by scanning and entering your code.');
                        }
                        redirect('/verify-login-otp');
                    }

                    if (!userIsApproved($user)) {
                        $this->finishLogin($user, false);
                        setFlashMessage('info', 'Your account is pending admin approval.');
                        redirect('/pending-approval');
                    }

                    $this->finishLogin($user);
                    setFlashMessage('success', 'Welcome back, ' . $user['name']);
                    $this->redirectByRole();

                } else {
                    $error = 'Invalid email or password.';
                }
            }
        }

        include APP_PATH . '/views/auth/login.php';
    }

    public function verifyLoginOtp()
    {
        if (isLoggedIn()) {
            if (!userIsApproved()) {
                redirect('/pending-approval');
            }
            $this->redirectByRole();
        }

        $pendingUserId = $_SESSION['pending_otp_login_user_id'] ?? null;
        $pendingEmail = $_SESSION['pending_otp_email'] ?? null;
        $pendingSecret = $_SESSION['pending_login_totp_secret'] ?? null;
        $isSetup = !empty($_SESSION['pending_login_totp_setup']);

        if (!$pendingUserId || !$pendingEmail || !$pendingSecret) {
            redirect('/login');
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $code = preg_replace('/\D/', '', $_POST['otp_code'] ?? '');
            $failedAttempts = (int) ($_SESSION['pending_login_totp_failed_attempts'] ?? 0);
            if ($failedAttempts >= OTP_MAX_FAILED_ATTEMPTS) {
                $this->clearPendingLogin();
                setFlashMessage('error', 'Too many incorrect attempts. Please sign in again.');
                redirect('/login');
            }

            if (!TotpService::verifyCode($pendingSecret, $code, TOTP_WINDOW_STEPS)) {
                $_SESSION['pending_login_totp_failed_attempts'] = $failedAttempts + 1;
                if ((int) $_SESSION['pending_login_totp_failed_attempts'] >= OTP_MAX_FAILED_ATTEMPTS) {
                    $this->clearPendingLogin();
                    setFlashMessage('error', 'Too many incorrect attempts. Please sign in again.');
                    redirect('/login');
                }
                $error = 'Incorrect code. Try again.';
            } else {

                $user = $this->userModel->findById((int) $pendingUserId);

                if (!$user || strcasecmp($user['email'], $pendingEmail) !== 0) {
                    $this->clearPendingLogin();
                    setFlashMessage('error', 'Session invalid. Please sign in again.');
                    redirect('/login');
                }

                $updateData = [
                    'qr_verified' => 1,
                ];

                if ($isSetup || empty($user['google_auth_secret'])) {
                    $updateData['google_auth_secret'] = $pendingSecret;
                }

                $saved = $this->userModel->update((int) $user['id'], $updateData);

                if (!$saved) {
                    $error = 'Could not save your authenticator setup. Please try again.';
                    include APP_PATH . '/views/auth/verify_otp_login.php';
                    return;
                }

                $user = $this->userModel->findById((int) $user['id']);
                $this->clearPendingLogin();
                $this->finishLogin($user, false);

                if (userIsApproved($user)) {
                    setFlashMessage('success', 'Welcome back, ' . $user['name']);
                    $this->redirectByRole();
                }

                setFlashMessage('success', 'QR verification complete. Waiting for admin approval.');
                redirect('/pending-approval');
            }
        }

        $emailMasked = $pendingEmail;
        $totpSecretForView = $pendingSecret;
        $totpIssuer = TOTP_ISSUER;
        $totpQr = $this->buildTotpQrUrl($pendingEmail, $totpSecretForView, $totpIssuer);
        $totpProvisioningUri = $totpQr['provisioning_uri'];
        $totpQrUrl = $totpQr['qr_url'];
        $isFirstTimeSetup = $isSetup;
        $showQrImage = true;

        include APP_PATH . '/views/auth/verify_otp_login.php';
    }

    public function pendingApproval()
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $user = $this->userModel->findById((int) getCurrentUserId());

        if ($user && userIsApproved($user)) {
            establishUserSession($user);
            setFlashMessage('success', 'Your account has been approved!');
            $this->redirectByRole();
        }

        include APP_PATH . '/views/auth/pending_approval.php';
    }

    public function resendLoginOtp()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/login');
        }

        $pendingUserId = $_SESSION['pending_otp_login_user_id'] ?? null;
        $pendingEmail = $_SESSION['pending_otp_email'] ?? null;

        if (!$pendingUserId || !$pendingEmail) {
            redirect('/login');
        }

        setFlashMessage('error', 'Resend is not used for Google Authenticator. Open your app and enter the current 6-digit code.');

        redirect('/verify-login-otp');
    }

    public function register()
    {
        if (isLoggedIn()) {
            $this->redirectByRole();
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $name = sanitize($_POST['name'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (!$name || !$email || !$password || !$confirm) {
                $error = 'Please fill in all fields.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } elseif ($this->userModel->emailExists($email)) {
                $error = 'Email already exists.';
            } else {

                $this->clearPendingRegister();

                $_SESSION['pending_register_name'] = $name;
                $_SESSION['pending_register_email'] = $email;
                $_SESSION['pending_register_password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                $_SESSION['pending_register_totp_secret'] = TotpService::generateSecret();
                $_SESSION['pending_register_totp_failed_attempts'] = 0;

                setFlashMessage('success', 'Scan the QR in Google Authenticator once, then enter the 6-digit code.');
                redirect('/verify-register-otp');
            }
        }

        include APP_PATH . '/views/auth/register.php';
    }

    public function verifyRegisterOtp()
    {
        if (isLoggedIn()) {
            $this->redirectByRole();
        }

        $name = $_SESSION['pending_register_name'] ?? null;
        $email = $_SESSION['pending_register_email'] ?? null;
        $hash = $_SESSION['pending_register_password_hash'] ?? null;
        $totpSecret = $_SESSION['pending_register_totp_secret'] ?? null;

        if (!$name || !$email || !$hash || !$totpSecret) {
            redirect('/register');
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $code = preg_replace('/\D/', '', $_POST['otp_code'] ?? '');
            $failedAttempts = (int) ($_SESSION['pending_register_totp_failed_attempts'] ?? 0);

            if ($failedAttempts >= OTP_MAX_FAILED_ATTEMPTS) {
                $this->clearPendingRegister();
                setFlashMessage('error', 'Too many incorrect attempts. Please register again.');
                redirect('/register');
            }

            if (!TotpService::verifyCode($totpSecret, $code, TOTP_WINDOW_STEPS)) {
                $_SESSION['pending_register_totp_failed_attempts'] = $failedAttempts + 1;
                if ((int) $_SESSION['pending_register_totp_failed_attempts'] >= OTP_MAX_FAILED_ATTEMPTS) {
                    $this->clearPendingRegister();
                    setFlashMessage('error', 'Too many incorrect attempts. Please register again.');
                    redirect('/register');
                }
                $error = 'Incorrect code. Try again.';
            } else {

                if ($this->userModel->emailExists($email)) {
                    $this->clearPendingRegister();
                    setFlashMessage('error', 'Email was registered meanwhile. Try signing in.');
                    redirect('/login');
                }

                $created = $this->userModel->createWithHashedPassword([
                    'name' => $name,
                    'email' => $email,
                    'password_hash' => $hash,
                    'google_auth_secret' => $totpSecret,
                    'qr_verified' => 1,
                    'role' => 'customer',
                ]);

                $this->clearPendingRegister();

                if (!$created) {
                    setFlashMessage('error', 'Registration failed.');
                    redirect('/register');
                }

                setFlashMessage('success', 'Account created. Sign in after an admin approves your account.');
                redirect('/login?registered=1');
            }
        }
        $totpSecretForView = $totpSecret;
        $totpIssuer = TOTP_ISSUER;
        $totpQr = $this->buildTotpQrUrl($email, $totpSecretForView, $totpIssuer);
        $totpProvisioningUri = $totpQr['provisioning_uri'];
        $totpQrUrl = $totpQr['qr_url'];

        include APP_PATH . '/views/auth/verify_otp_register.php';
    }

    public function resendRegisterOtp()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/register');
        }

        $email = $_SESSION['pending_register_email'] ?? null;

        if (!$email) {
            redirect('/register');
        }

        setFlashMessage('error', 'Resend is not used for Google Authenticator. Use the current code shown in your app.');

        redirect('/verify-register-otp');
    }

    public function logout()
    {
        session_unset();
        session_destroy();

        session_start();
        setFlashMessage('success', 'Logged out successfully.');

        redirect('/login');
    }
}
