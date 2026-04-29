<?php
// auth/Auth.php
require_once __DIR__ . '/../config/Database.php';

class Auth {
    public static function login($email, $password) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }
        return false;
    }
    
    public static function logout() {
        session_destroy();
        return true;
    }
    
    public static function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }
    
    public static function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    public static function getCurrentUser() {
        if (!self::isAuthenticated()) return null;
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    
    public static function register($name, $email, $password) {
        $db = Database::getInstance()->getConnection();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword]);
            return true;
        } catch(PDOException $e) {
            return false;
        }
    }
}