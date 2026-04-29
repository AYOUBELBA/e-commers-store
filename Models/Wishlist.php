<?php
// models/Wishlist.php
require_once __DIR__ . '/../config/Database.php';

class Wishlist {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getByUser($userId) {
        $stmt = $this->db->prepare("SELECT w.*, p.name, p.price, p.image_url, p.slug 
                                    FROM wishlist w 
                                    JOIN products p ON w.product_id = p.id 
                                    WHERE w.user_id = ? 
                                    ORDER BY w.created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function add($userId, $productId) {
        // Check if already exists
        $stmt = $this->db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        if ($stmt->fetch()) {
            return false;
        }
        
        $stmt = $this->db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        return $stmt->execute([$userId, $productId]);
    }
    
    public function remove($userId, $productId) {
        $stmt = $this->db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$userId, $productId]);
    }
    
    public function isInWishlist($userId, $productId) {
        $stmt = $this->db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        return $stmt->fetch() !== false;
    }
}