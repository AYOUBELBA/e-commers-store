<?php
require_once __DIR__ . '/../auth/Auth.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

class AdminController {
    private function checkAdmin() {
        if (!Auth::isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
    
    public function dashboard() {
        $this->checkAdmin();
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }
    
    public function products() {
        $this->checkAdmin();
        require_once __DIR__ . '/../views/admin/products.php';
    }
    
    public function orders() {
        $this->checkAdmin();
        // Simple orders page until you build the full one
        echo "<!DOCTYPE html><html><head><title>Manage Orders</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-gray-100'><div class='container mx-auto px-4 py-8'><h1 class='text-3xl font-bold mb-4'>Manage Orders</h1><p class='mb-4'>Order management coming soon...</p><a href='" . BASE_URL . "/admin/dashboard' class='bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700'>Back to Dashboard</a></div></body></html>";
    }
    
    public function users() {
        $this->checkAdmin();
        echo "<!DOCTYPE html><html><head><title>Manage Users</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-gray-100'><div class='container mx-auto px-4 py-8'><h1 class='text-3xl font-bold mb-4'>Manage Users</h1><p class='mb-4'>User management coming soon...</p><a href='" . BASE_URL . "/admin/dashboard' class='bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700'>Back to Dashboard</a></div></body></html>";
    }
    public function addProduct() {
    $this->checkAdmin();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $db = Database::getInstance()->getConnection();
        $name = $_POST['name'] ?? '';
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $name)));
        $description = $_POST['description'] ?? '';
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $category_id = $_POST['category_id'] ? (int)$_POST['category_id'] : null;
        $image_url = $_POST['image_url'] ?? null;
        
        $stmt = $db->prepare("INSERT INTO products (name, slug, description, price, stock, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $slug, $description, $price, $stock, $category_id, $image_url])) {
            $_SESSION['success_message'] = 'Product added successfully';
        } else {
            $_SESSION['error_message'] = 'Failed to add product';
        }
    }
    header('Location: ' . BASE_URL . '/admin/products');
}

public function updateProduct() {
    $this->checkAdmin();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $db = Database::getInstance()->getConnection();
        $id = (int)$_POST['product_id'];
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $category_id = $_POST['category_id'] ? (int)$_POST['category_id'] : null;
        $image_url = $_POST['image_url'] ?? null;
        
        $stmt = $db->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image_url = ? WHERE id = ?");
        if ($stmt->execute([$name, $description, $price, $stock, $category_id, $image_url, $id])) {
            $_SESSION['success_message'] = 'Product updated successfully';
        } else {
            $_SESSION['error_message'] = 'Failed to update product';
        }
    }
    header('Location: ' . BASE_URL . '/admin/products');
}

public function deleteProduct() {
    $this->checkAdmin();
    $id = (int)$_GET['id'] ?? 0;
    if ($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['success_message'] = 'Product deleted successfully';
        } else {
            $_SESSION['error_message'] = 'Failed to delete product';
        }
    }
    header('Location: ' . BASE_URL . '/admin/products');
}
    public function categories() {
        $this->checkAdmin();
        echo "<!DOCTYPE html><html><head><title>Manage Categories</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-gray-100'><div class='container mx-auto px-4 py-8'><h1 class='text-3xl font-bold mb-4'>Manage Categories</h1><p class='mb-4'>Category management coming soon...</p><a href='" . BASE_URL . "/admin/dashboard' class='bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700'>Back to Dashboard</a></div></body></html>";
    }
}