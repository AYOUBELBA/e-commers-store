<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debugging Information</h1>";

// Check if files exist
$files = [
    '../config/config.php',
    '../config/Database.php',
    '../auth/Auth.php',
    '../controllers/AuthController.php',
    '../controllers/ProductController.php',
    '../controllers/CartController.php',
    '../controllers/OrderController.php',
    '../controllers/AdminController.php'
];

foreach ($files as $file) {
    $fullPath = __DIR__ . '/' . $file;
    echo "<br>Checking: $file<br>";
    echo "Full path: $fullPath<br>";
    if (file_exists($fullPath)) {
        echo "✅ File exists<br>";
    } else {
        echo "❌ File NOT found<br>";
    }
}

// Try to include config
echo "<hr>";
echo "<h2>Trying to include config.php...</h2>";
try {
    require_once __DIR__ . '/../config/config.php';
    echo "✅ config.php loaded successfully<br>";
    echo "APP_NAME: " . APP_NAME . "<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>PHP Version: " . phpversion() . "</h2>";
echo "<h2>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</h2>";