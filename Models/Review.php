<?php
// models/Review.php
require_once __DIR__ . '/../config/Database.php';

class Review {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getByProduct($productId, $limit = 10, $offset = 0) {
        $stmt = $this->db->prepare("SELECT r.*, u.name as user_name 
                                    FROM reviews r 
                                    JOIN users u ON r.user_id = u.id 
                                    WHERE r.product_id = ? 
                                    ORDER BY r.created_at DESC 
                                    LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $productId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getAverageRating($productId) {
        $stmt = $this->db->prepare("SELECT AVG(rating) as average, COUNT(*) as count 
                                    FROM reviews WHERE product_id = ?");
        $stmt->execute([$productId]);
        $result = $stmt->fetch();
        return [
            'average' => round($result['average'] ?? 0, 1),
            'count' => $result['count'] ?? 0
        ];
    }
    
    public function create($userId, $productId, $rating, $comment, $title = null) {
        // Check if user has purchased this product
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM order_items oi 
                                    JOIN orders o ON oi.order_id = o.id 
                                    WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'");
        $stmt->execute([$userId, $productId]);
        $hasPurchased = $stmt->fetchColumn() > 0;
        
        $stmt = $this->db->prepare("INSERT INTO reviews (user_id, product_id, rating, title, comment, verified_purchase) 
                                    VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$userId, $productId, $rating, $title, $comment, $hasPurchased]);
    }
    
    public function getUserReview($userId, $productId) {
        $stmt = $this->db->prepare("SELECT * FROM reviews WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        return $stmt->fetch();
    }
    
    public function delete($id, $userId) {
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }
}