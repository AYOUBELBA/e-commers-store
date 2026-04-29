<?php

require_once __DIR__ . '/../config/Database.php';

class Cart {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getItems($userId) {
        $stmt = $this->db->prepare("SELECT c.*, p.name, p.price, p.image_url, p.stock 
                                    FROM cart_items c 
                                    JOIN products p ON c.product_id = p.id 
                                    WHERE c.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function addItem($userId, $productId, $quantity = 1) {
        // Check stock
        $stmt = $this->db->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product || $product['stock'] < $quantity) {
            return false;
        }
        
        $stmt = $this->db->prepare("INSERT INTO cart_items (user_id, product_id, quantity) 
                                    VALUES (?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE quantity = quantity + ?");
        return $stmt->execute([$userId, $productId, $quantity, $quantity]);
    }
    
    public function updateQuantity($userId, $productId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeItem($userId, $productId);
        }
        
        // Check stock
        $stmt = $this->db->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product || $product['stock'] < $quantity) {
            return false;
        }
        
        $stmt = $this->db->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$quantity, $userId, $productId]);
    }
    
    public function removeItem($userId, $productId) {
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$userId, $productId]);
    }
    
    public function clearCart($userId) {
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
    
    public function getCartTotal($userId) {
        $stmt = $this->db->prepare("SELECT SUM(p.price * c.quantity) as total 
                                    FROM cart_items c 
                                    JOIN products p ON c.product_id = p.id 
                                    WHERE c.user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    public function getCartCount($userId) {
        $stmt = $this->db->prepare("SELECT SUM(quantity) as count FROM cart_items WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
}