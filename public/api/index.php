<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

session_start();
require_once __DIR__ . '/../../config/Database.php';

$db = Database::getInstance()->getConnection();
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Get products with filters
if ($action === 'products') {
    try {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";
        $params = [];
        
        // Search filter - IMPORTANT: This must be here
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = '%' . $_GET['search'] . '%';
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            error_log("Searching for: " . $_GET['search']); // Debug log
        }
        
        // Category filter
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = (int)$_GET['category'];
        }
        
        // Price range filters
        if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
            $sql .= " AND p.price >= ?";
            $params[] = (float)$_GET['min_price'];
        }
        
        if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
            $sql .= " AND p.price <= ?";
            $params[] = (float)$_GET['max_price'];
        }
        
        // Sorting
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
        switch ($sort) {
            case 'price_asc':
                $sql .= " ORDER BY p.price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY p.price DESC";
                break;
            case 'name_asc':
                $sql .= " ORDER BY p.name ASC";
                break;
            case 'name_desc':
                $sql .= " ORDER BY p.name DESC";
                break;
            case 'newest':
            default:
                $sql .= " ORDER BY p.created_at DESC, p.id DESC";
                break;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        
        echo json_encode($products);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Get categories
if ($action === 'categories') {
    try {
        $stmt = $db->query("SELECT id, name, slug FROM categories ORDER BY name");
        echo json_encode($stmt->fetchAll());
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Test endpoint
if ($action === 'test') {
    echo json_encode([
        'status' => 'ok', 
        'message' => 'API is working',
        'logged_in' => isset($_SESSION['user_id']),
        'session_id' => session_id()
    ]);
    exit;
}

// Add to cart
if ($action === 'add-to-cart') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Please login first']);
        exit;
    }
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
    $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;
    $user_id = $_SESSION['user_id'];
    
    if ($product_id <= 0) {
        echo json_encode(['error' => 'Invalid product ID']);
        exit;
    }
    
    try {
        // Check if product exists
        $stmt = $db->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Product not found']);
            exit;
        }
        
        // Add to cart
        $stmt = $db->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?) 
                              ON DUPLICATE KEY UPDATE quantity = quantity + ?");
        $stmt->execute([$user_id, $product_id, $quantity, $quantity]);
        
        // Get updated count (number of unique products)
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM cart_items WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $count = $stmt->fetch()['count'];
        
        echo json_encode([
            'success' => true, 
            'message' => 'Added to cart', 
            'cart_count' => $count
        ]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Get cart
if ($action === 'cart') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([]);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $db->prepare("SELECT c.*, p.name, p.price, p.image_url 
                          FROM cart_items c 
                          JOIN products p ON c.product_id = p.id 
                          WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}

// Get cart count
if ($action === 'cart-count') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['count' => 0]);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    // COUNT(*) gives number of unique products, not total quantity
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM cart_items WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $count = $stmt->fetch()['count'];
    echo json_encode(['count' => $count]);
    exit;
}
// Update cart
if ($action === 'update-cart') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
    $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 0;
    $user_id = $_SESSION['user_id'];
    
    if ($quantity <= 0) {
        $stmt = $db->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
    } else {
        $stmt = $db->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$quantity, $user_id, $product_id]);
    }
    
    // Get updated count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM cart_items WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $count = $stmt->fetch()['count'];
    
    echo json_encode(['success' => true, 'cart_count' => $count]);
    exit;
}

// Remove from cart
if ($action === 'remove-from-cart') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $user_id = $_SESSION['user_id'];
    
    if ($product_id > 0) {
        $stmt = $db->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        
        // Get updated count
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM cart_items WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $count = $stmt->fetch()['count'];
        
        echo json_encode(['success' => true, 'cart_count' => $count]);
    } else {
        echo json_encode(['error' => 'Invalid product ID']);
    }
    exit;
}
// Search products
if ($action === 'search') {
    $search = isset($_GET['q']) ? $_GET['q'] : '';
    if (strlen($search) >= 2) {
        $stmt = $db->prepare("SELECT id, name, slug, price, image_url FROM products WHERE name LIKE ? OR description LIKE ? LIMIT 10");
        $like = "%{$search}%";
        $stmt->execute([$like, $like]);
        echo json_encode($stmt->fetchAll());
    } else {
        echo json_encode([]);
    }
    exit;
}

// Default response
echo json_encode([
    'message' => 'API is working',
    'available_actions' => ['products', 'categories', 'test', 'add-to-cart', 'cart', 'cart-count', 'update-cart', 'remove-from-cart', 'search']
]);
?>