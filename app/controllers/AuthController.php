<?php
class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    private function redirectByRole() {
        if (isAdmin()) {
            redirect('/admin'); 
        } else {
            redirect('/dashboard'); 
        }
    }

    public function login() {

       
        if (isLoggedIn()) {
            $this->redirectByRole();
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

           
            if (empty($email) || empty($password)) {
                $error = 'Please fill in all fields.';
            } else {

                $user = $this->userModel->verifyPassword($email, $password);

                if ($user) {

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];

                    setFlashMessage('success', 'Welcome back, ' . $user['name'] . '!');

                   
                    $this->redirectByRole();

                } else {
                    $error = 'Invalid email or password.';
                }
            }
        }

        include APP_PATH . '/views/auth/login.php';
    }

    public function register() {

        if (isLoggedIn()) {
            $this->redirectByRole();
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $name = sanitize($_POST['name'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

           
            if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
                $error = 'Please fill in all fields.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email format.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } elseif ($this->userModel->emailExists($email)) {
                $error = 'Email already exists.';
            } else {

                $userId = $this->userModel->create([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'role' => 'customer'
                ]);

                if ($userId) {
                    setFlashMessage('success', 'Registration successful! Please login.');
                    redirect('/login');
                } else {
                    $error = 'Registration failed.';
                }
            }
        }

        include APP_PATH . '/views/auth/register.php';
    }

   
    public function logout() {

        session_unset();
        session_destroy();

        session_start();
        setFlashMessage('success', 'Logged out successfully.');

        redirect('/login');
    }
}