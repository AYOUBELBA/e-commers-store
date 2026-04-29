-- schema.sql
CREATE DATABASE IF NOT EXISTS `ecommerce_db`;
USE `ecommerce_db`;

-- Users table
CREATE TABLE `users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `avatar` VARCHAR(500),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE `categories` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) UNIQUE NOT NULL,
  `description` TEXT,
  `image_url` VARCHAR(500),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE `products` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) UNIQUE NOT NULL,
  `description` TEXT,
  `short_description` VARCHAR(500),
  `price` DECIMAL(10,2) NOT NULL,
  `compare_price` DECIMAL(10,2),
  `stock` INT NOT NULL DEFAULT 0,
  `image_url` VARCHAR(500),
  `gallery_images` JSON,
  `category_id` INT,
  `featured` BOOLEAN DEFAULT FALSE,
  `popular` BOOLEAN DEFAULT FALSE,
  `views` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX idx_category (`category_id`),
  INDEX idx_featured (`featured`),
  FULLTEXT idx_search (name, description)
);

-- Cart items table
CREATE TABLE `cart_items` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `user_product_unique` (`user_id`, `product_id`)
);

-- Orders table
CREATE TABLE `orders` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `order_number` VARCHAR(50) UNIQUE NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `tax` DECIMAL(10,2) DEFAULT 0,
  `shipping_cost` DECIMAL(10,2) DEFAULT 0,
  `discount` DECIMAL(10,2) DEFAULT 0,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
  `shipping_address` TEXT NOT NULL,
  `shipping_city` VARCHAR(100),
  `shipping_zip` VARCHAR(20),
  `payment_method` VARCHAR(50),
  `payment_status` ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX idx_user (`user_id`),
  INDEX idx_status (`status`)
);

-- Order items table
CREATE TABLE `order_items` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name` VARCHAR(200) NOT NULL,
  `quantity` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  INDEX idx_order (`order_id`)
);

-- Wishlist table
CREATE TABLE `wishlist` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `user_product_wish` (`user_id`, `product_id`)
);

-- Reviews table
CREATE TABLE `reviews` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `rating` INT CHECK (rating >= 1 AND rating <= 5),
  `title` VARCHAR(200),
  `comment` TEXT,
  `verified_purchase` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX idx_product (`product_id`),
  INDEX idx_rating (`rating`)
);

-- Coupons table
CREATE TABLE `coupons` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `code` VARCHAR(50) UNIQUE NOT NULL,
  `description` TEXT,
  `discount_type` ENUM('percentage', 'fixed') DEFAULT 'percentage',
  `discount_value` DECIMAL(10,2) NOT NULL,
  `min_order_amount` DECIMAL(10,2) DEFAULT 0,
  `max_discount` DECIMAL(10,2),
  `usage_limit` INT DEFAULT 1,
  `used_count` INT DEFAULT 0,
  `expires_at` DATE,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample categories
INSERT INTO `categories` (`name`, `slug`, `description`) VALUES
('Electronics', 'electronics', 'Latest gadgets and electronics'),
('Clothing', 'clothing', 'Fashion and apparel'),
('Books', 'books', 'Books and magazines'),
('Home & Garden', 'home-garden', 'Home decor and garden items'),
('Sports', 'sports', 'Sports equipment and gear');

-- Insert sample products
INSERT INTO `products` (`name`, `slug`, `description`, `price`, `stock`, `image_url`, `category_id`, `featured`, `popular`) VALUES
('iPhone 15 Pro', 'iphone-15-pro', 'The latest iPhone with A17 chip and titanium design', 999.99, 50, 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=400', 1, TRUE, TRUE),
('MacBook Pro M3', 'macbook-pro-m3', '14-inch laptop with M3 chip, 16GB RAM, 512GB SSD', 1599.99, 30, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400', 1, TRUE, TRUE),
('Premium T-Shirt', 'premium-t-shirt', '100% cotton comfortable t-shirt', 29.99, 200, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400', 2, FALSE, TRUE),
('The Great Gatsby', 'great-gatsby', 'Classic novel by F. Scott Fitzgerald', 12.99, 150, 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400', 3, FALSE, FALSE),
('Wireless Headphones', 'wireless-headphones', 'Noise-cancelling Bluetooth headphones', 199.99, 75, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400', 1, TRUE, TRUE);

-- Insert admin user (password: Admin@123)
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Admin User', 'admin@example.com', '$2y$10$YourHashedPasswordHere', 'admin');

-- Insert a test coupon
INSERT INTO `coupons` (`code`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `expires_at`) VALUES
('WELCOME10', '10% off your first order', 'percentage', 10, 50, DATE_ADD(CURDATE(), INTERVAL 30 DAY));