<?php

require_once __DIR__ . '/../config/Database.php';

class Product {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAll($limit = 12, $offset = 0, $filters = []) {
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
        $params = [];
        
        if (isset($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (isset($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }
        
        if (isset($filters['min_price'])) {
            $sql .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (isset($filters['max_price'])) {
            $sql .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }
        
        $sql .= " ORDER BY ";
        
        if (isset($filters['sort'])) {
            switch($filters['sort']) {
                case 'price_asc':
                    $sql .= "p.price ASC";
                    break;
                case 'price_desc':
                    $sql .= "p.price DESC";
                    break;
                case 'name_asc':
                    $sql .= "p.name ASC";
                    break;
                default:
                    $sql .= "p.created_at DESC";
            }
        } else {
            $sql .= "p.created_at DESC";
        }
        
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function getByCategory($categoryId, $limit = 4, $excludeId = null) {
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.category_id = ?";
    $params = [$categoryId];
    
    if ($excludeId) {
        $sql .= " AND p.id != ?";
        $params[] = $excludeId;
    }
    
    $sql .= " ORDER BY p.created_at DESC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
    public function getFeatured($limit = 8) {
        $stmt = $this->db->prepare("SELECT p.*, c.name as category_name FROM products p 
                                    LEFT JOIN categories c ON p.category_id = c.id 
                                    WHERE p.featured = 1 ORDER BY p.created_at DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getPopular($limit = 8) {
        $stmt = $this->db->prepare("SELECT p.*, c.name as category_name FROM products p 
                                    LEFT JOIN categories c ON p.category_id = c.id 
                                    WHERE p.popular = 1 ORDER BY p.views DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT p.*, c.name as category_name FROM products p 
                                    LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if ($product) {
            // Increment view count
            $this->incrementViews($id);
        }
        
        return $product;
    }
    
    public function findBySlug($slug) {
        $stmt = $this->db->prepare("SELECT p.*, c.name as category_name FROM products p 
                                    LEFT JOIN categories c ON p.category_id = c.id WHERE p.slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO products (name, slug, description, price, stock, image_url, category_id, featured, popular) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['description'],
            $data['price'],
            $data['stock'],
            $data['image_url'] ?? null,
            $data['category_id'] ?? null,
            $data['featured'] ?? 0,
            $data['popular'] ?? 0
        ]);
    }
    
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        $allowedFields = ['name', 'slug', 'description', 'price', 'stock', 'image_url', 'category_id', 'featured', 'popular'];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($fields)) return false;
        
        $values[] = $id;
        $stmt = $this->db->prepare("UPDATE products SET " . implode(", ", $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function updateStock($id, $quantity) {
        $stmt = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
        return $stmt->execute([$quantity, $id, $quantity]);
    }
    
    private function incrementViews($id) {
        $stmt = $this->db->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
        $stmt->execute([$id]);
    }
    
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) FROM products p WHERE 1=1";
        $params = [];
        
        if (isset($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (isset($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    public function search($query, $limit = 10) {
        $stmt = $this->db->prepare("SELECT id, name, slug, price, image_url FROM products 
                                    WHERE name LIKE ? OR description LIKE ? 
                                    LIMIT ?");
        $searchTerm = "%{$query}%";
        $stmt->bindValue(1, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(2, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}