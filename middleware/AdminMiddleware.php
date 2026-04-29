<?php
// middleware/AdminMiddleware.php
require_once __DIR__ . '/../auth/Auth.php';

class AdminMiddleware {
    public static function check() {
        if (!Auth::isAuthenticated()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
        
        if (!Auth::isAdmin()) {
            http_response_code(403);
            die('Access denied. Admin privileges required.');
        }
        
        return true;
    }
}