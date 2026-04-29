<?php
require_once __DIR__ . '/../config/Database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $db = Database::getInstance()->getConnection();
    
    // Detect primary key column
    $columns = $db->query("SHOW COLUMNS FROM products")->fetchAll();
    $primaryKey = 'product_id';
    foreach ($columns as $col) {
        if ($col['Key'] === 'PRI') {
            $primaryKey = $col['Field'];
            break;
        }
    }
    
    $stmt = $db->prepare("SELECT image_data, image_type FROM products WHERE $primaryKey = ? AND image_data IS NOT NULL");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if ($product && !empty($product['image_data'])) {
        header('Content-Type: ' . $product['image_type']);
        echo $product['image_data'];
        exit;
    }
}

// Placeholder
header('Content-Type: image/svg+xml');
echo '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 24 24" fill="none" stroke="#9ca3af"><rect x="2" y="2" width="20" height="20" rx="2.18"/><circle cx="8.5" cy="8.5" r="2.5"/><path d="M21 15l-5-5-6 6-3-3-4 4"/></svg>';
?>