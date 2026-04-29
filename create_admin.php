<?php
require_once 'config/Database.php';
require_once 'config/config.php';

$db = Database::getInstance()->getConnection();

// Check if admin exists
$stmt = $db->prepare("SELECT id FROM users WHERE email = 'admin@example.com'");
$stmt->execute();

if (!$stmt->fetch()) {
    // Create new admin
    $name = "Admin User";
    $email = "admin@example.com";
    $password = password_hash("Admin@123", PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
    if ($stmt->execute([$name, $email, $password])) {
        echo "Admin user created successfully!<br>";
        echo "Email: admin@example.com<br>";
        echo "Password: Admin@123<br>";
    } else {
        echo "Failed to create admin user.";
    }
} else {
    // Update existing admin password
    $password = password_hash("Admin@123", PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = 'admin@example.com'");
    $stmt->execute([$password]);
    echo "Admin password updated!<br>";
    echo "Email: admin@example.com<br>";
    echo "Password: Admin@123<br>";
}
?>