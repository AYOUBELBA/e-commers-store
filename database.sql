-- =============================================
-- Database: ecommerce_db
-- Complete eCommerce Database Schema
-- =============================================

CREATE DATABASE IF NOT EXISTS `ecommerce_db`;
USE `ecommerce_db`;

-- =============================================
-- Table: users
-- =============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `avatar` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Table: categories
-- =============================================
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Table: products
-- =============================================
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `compare_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image_url` varchar(500) DEFAULT NULL,
  `gallery_images` longtext DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `popular` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_featured` (`featured`),
  FULLTEXT KEY `idx_search` (`name`,`description`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Table: cart_items
-- =============================================
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product_unique` (`user_id`,`product_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Table: orders
-- =============================================
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL UNIQUE,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) DEFAULT 0,
  `shipping_cost` decimal(10,2) DEFAULT 0,
  `discount` decimal(10,2) DEFAULT 0,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_zip` varchar(20) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Table: order_items
-- =============================================
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Table: wishlist
-- =============================================
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product_wish` (`user_id`,`product_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Table: reviews
-- =============================================
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `title` varchar(200) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `verified_purchase` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_rating` (`rating`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Table: coupons
-- =============================================
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed') DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT 0,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT 1,
  `used_count` int(11) DEFAULT 0,
  `expires_at` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- INSERT SAMPLE DATA
-- =============================================

-- Insert admin user (password: Admin@123)
-- Password hash is for 'Admin@123'
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample categories
INSERT INTO `categories` (`name`, `slug`, `description`) VALUES
('Electronics', 'electronics', 'Latest gadgets and electronics'),
('Clothing', 'clothing', 'Fashion and apparel for men and women'),
('Books', 'books', 'Books and magazines'),
('Home & Garden', 'home-garden', 'Home decor and garden items'),
('Sports', 'sports', 'Sports equipment and outdoor gear');

-- Insert sample products
INSERT INTO `products` (`name`, `slug`, `description`, `price`, `stock`, `image_url`, `category_id`, `featured`, `popular`) VALUES
('iPhone 15 Pro', 'iphone-15-pro', 'The latest iPhone with A17 chip and titanium design. Features a 48MP main camera, 5x optical zoom, and USB-C charging.', 999.99, 50, 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=400', 1, 1, 1),
('MacBook Pro M3', 'macbook-pro-m3', '14-inch laptop with M3 chip, 16GB RAM, 512GB SSD. Perfect for professionals and creators.', 1599.99, 30, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400', 1, 1, 1),
('Premium Cotton T-Shirt', 'premium-t-shirt', '100% organic cotton comfortable t-shirt. Available in multiple colors. Breathable and soft fabric.', 29.99, 200, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400', 2, 0, 1),
('The Great Gatsby', 'great-gatsby', 'Classic novel by F. Scott Fitzgerald. Set in the Jazz Age, this masterpiece explores themes of wealth, love, and the American Dream.', 12.99, 150, 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400', 3, 0, 0),
('Wireless Headphones', 'wireless-headphones', 'Premium noise-cancelling Bluetooth headphones. 30-hour battery life, comfortable fit, and crystal clear sound quality.', 199.99, 75, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400', 1, 1, 1),
('Smart Watch Ultra', 'smart-watch-ultra', 'Fitness tracker with heart rate monitor, GPS, and 7-day battery life. Water resistant up to 50m.', 299.99, 45, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400', 1, 0, 1),
('Running Shoes', 'running-shoes', 'Comfortable athletic running shoes with memory foam insole. Lightweight and durable design.', 89.99, 120, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400', 2, 0, 0),
('Ceramic Coffee Mug', 'ceramic-coffee-mug', 'Handcrafted ceramic coffee mug with lid. Microwave and dishwasher safe. Holds 15oz.', 14.99, 200, 'https://images.unsplash.com/photo-1514228742587-6b1558fcca3d?w=400', 4, 0, 0),
('Yoga Mat', 'yoga-mat', 'Eco-friendly non-slip yoga mat. 6mm thickness for extra comfort. Perfect for yoga, pilates, and floor exercises.', 39.99, 85, 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b33?w=400', 5, 0, 0),
('Dyson Vacuum Cleaner', 'dyson-vacuum', 'Cordless vacuum cleaner with powerful suction. Lightweight and easy to use. Perfect for pet hair.', 499.99, 25, 'https://images.unsplash.com/photo-1558317374-687fb8cf2634?w=400', 4, 1, 0);

-- Insert a sample coupon
INSERT INTO `coupons` (`code`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `expires_at`) VALUES
('WELCOME10', '10% off your first order', 'percentage', 10.00, 50.00, DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
('SAVE20', 'Save $20 on orders over $100', 'fixed', 20.00, 100.00, DATE_ADD(CURDATE(), INTERVAL 60 DAY));

-- =============================================
-- Create indexes for better performance
-- =============================================

ALTER TABLE `products` ADD INDEX `idx_price` (`price`);
ALTER TABLE `products` ADD INDEX `idx_stock` (`stock`);
ALTER TABLE `orders` ADD INDEX `idx_created_at` (`created_at`);
ALTER TABLE `orders` ADD INDEX `idx_user_status` (`user_id`, `status`);
ALTER TABLE `reviews` ADD INDEX `idx_product_rating` (`product_id`, `rating`);

-- =============================================
-- View for product analytics
-- =============================================

CREATE OR REPLACE VIEW `product_stats` AS
SELECT 
    p.id,
    p.name,
    p.price,
    p.stock,
    p.views,
    COALESCE(AVG(r.rating), 0) as avg_rating,
    COUNT(DISTINCT r.id) as review_count,
    COALESCE(SUM(oi.quantity), 0) as total_sold
FROM products p
LEFT JOIN reviews r ON p.id = r.product_id
LEFT JOIN order_items oi ON p.id = oi.product_id
GROUP BY p.id;
