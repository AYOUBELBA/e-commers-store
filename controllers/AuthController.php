<?php
require_once __DIR__ . '/../auth/Auth.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function showLogin() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        require_once __DIR__ . '/../views/auth/login.php';
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showLogin();
            return;
        }
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (Auth::login($email, $password)) {
            header('Location: ' . BASE_URL);
            exit;
        } else {
            $_SESSION['error_message'] = 'Invalid email or password';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
    
    public function showRegister() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        require_once __DIR__ . '/../views/auth/register.php';
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showRegister();
            return;
        }
        
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($password !== $confirmPassword) {
            $_SESSION['error_message'] = 'Passwords do not match';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }
        
        if (strlen($password) < 6) {
            $_SESSION['error_message'] = 'Password must be at least 6 characters';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }
        
        if ($this->userModel->create($name, $email, $password)) {
            $_SESSION['success_message'] = 'Registration successful! Please login.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        } else {
            $_SESSION['error_message'] = 'Email already exists';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }
    }
    
    public function logout() {
        Auth::logout();
        header('Location: ' . BASE_URL);
        exit;
    }
    
    public function profile() {
        if (!Auth::isAuthenticated()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $user = Auth::getCurrentUser();
        echo "<!DOCTYPE html><html><head><title>My Profile</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-gray-100'><div class='container mx-auto px-4 py-8'><h1 class='text-3xl font-bold mb-4'>My Profile</h1><p>Welcome " . htmlspecialchars($user['name']) . "!</p><p>Email: " . htmlspecialchars($user['email']) . "</p><a href='" . BASE_URL . "/logout' class='bg-red-600 text-white px-4 py-2 rounded-lg'>Logout</a><br><a href='" . BASE_URL . "' class='text-blue-600 mt-4 inline-block'>Back to Home</a></div></body></html>";
    }
}