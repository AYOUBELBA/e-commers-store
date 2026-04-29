<?php
// config/config.php
// Define all constants FIRST

// Base URLs
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/store-projet/public');
}
if (!defined('APP_URL')) {
    define('APP_URL', BASE_URL);
}

// Application constants
if (!defined('APP_NAME')) {
    define('APP_NAME', 'ModernStore');
}
if (!defined('APP_ENV')) {
    define('APP_ENV', 'development');
}

// Database constants
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'ecommerce_db');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}

// Shopping constants
if (!defined('TAX_RATE')) {
    define('TAX_RATE', 0.10);
}
if (!defined('SHIPPING_COST')) {
    define('SHIPPING_COST', 5.99);
}
if (!defined('FREE_SHIPPING_MIN')) {
    define('FREE_SHIPPING_MIN', 50);
}
if (!defined('ITEMS_PER_PAGE')) {
    define('ITEMS_PER_PAGE', 12);
}

// Start session (NO output before this!)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting for development
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

date_default_timezone_set('UTC');