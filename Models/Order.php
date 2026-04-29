<?php

require_once __DIR__ . '/../config/Database.php';

class Order {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($userId, $cartItems, $shippingAddress, $shippingCity, $shippingZip, $paymentMethod, $couponCode = null) {
        try {
            $this->db->beginTransaction();
            
            // Calculate totals
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            $tax = $subtotal * TAX_RATE;
            $shippingCost = ($subtotal >= FREE_SHIPPING_MIN) ? 0 : SHIPPING_COST;
            $discount = 0;
            
            // Apply coupon if provided
            if ($couponCode) {
                $discount = $this->applyCoupon($couponCode, $subtotal);
            }
            
            $total = $subtotal + $tax + $shippingCost - $discount;
            $orderNumber = 'ORD-' . strtoupper(uniqid());
            
            // Create order
            $stmt = $this->db->prepare("INSERT INTO orders (user_id, order_number, subtotal, tax, shipping_cost, discount, total_amount, shipping_address, shipping_city, shipping_zip, payment_method, status) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$userId, $orderNumber, $subtotal, $tax, $shippingCost, $discount, $total, $shippingAddress, $shippingCity, $shippingZip, $paymentMethod]);
            $orderId = $this->db->lastInsertId();
            
            // Create order items and update stock
            foreach ($cartItems as $item) {
                $stmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) 
                                            VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$orderId, $item['product_id'], $item['name'], $item['quantity'], $item['price']]);
                
                // Update product stock
                $stmt = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Clear cart
            $stmt = $this->db->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            $this->db->commit();
            
            return ['order_id' => $orderId, 'order_number' => $orderNumber, 'total' => $total];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getUserOrders($userId, $limit = 50, $offset = 0) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getOrderDetails($orderId, $userId = null) {
        $sql = "SELECT o.*, u.name as user_name, u.email FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?";
        $params = [$orderId];
        
        if ($userId) {
            $sql .= " AND o.user_id = ?";
            $params[] = $userId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $order = $stmt->fetch();
        
        if ($order) {
            $stmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $order['items'] = $stmt->fetchAll();
        }
        
        return $order;
    }
    
    public function getAllOrders($limit = 50, $offset = 0, $status = null) {
        $sql = "SELECT o.*, u.name as user_name FROM orders o 
                JOIN users u ON o.user_id = u.id";
        $params = [];
        
        if ($status) {
            $sql .= " WHERE o.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function updateStatus($orderId, $status) {
        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $orderId]);
    }
    
    public function getSalesStats() {
        $stats = [];
        
        // Total revenue
        $stmt = $this->db->query("SELECT SUM(total_amount) as revenue FROM orders WHERE status = 'delivered'");
        $stats['revenue'] = $stmt->fetchColumn() ?: 0;
        
        // Orders count
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM orders");
        $stats['orders_count'] = $stmt->fetchColumn();
        
        // Average order value
        $stats['avg_order_value'] = $stats['orders_count'] > 0 ? $stats['revenue'] / $stats['orders_count'] : 0;
        
        // Monthly sales for chart
        $stmt = $this->db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as total 
                                  FROM orders WHERE status = 'delivered' 
                                  GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                                  ORDER BY month DESC LIMIT 6");
        $stats['monthly_sales'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    private function applyCoupon($code, $subtotal) {
        $stmt = $this->db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at >= CURDATE())");
        $stmt->execute([$code]);
        $coupon = $stmt->fetch();
        
        if (!$coupon || $subtotal < $coupon['min_order_amount']) {
            return 0;
        }
        
        if ($coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit']) {
            return 0;
        }
        
        $discount = 0;
        if ($coupon['discount_type'] == 'percentage') {
            $discount = $subtotal * ($coupon['discount_value'] / 100);
            if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
                $discount = $coupon['max_discount'];
            }
        } else {
            $discount = $coupon['discount_value'];
        }
        
        // Increment usage count
        $stmt = $this->db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
        $stmt->execute([$coupon['id']]);
        
        return $discount;
    }
}