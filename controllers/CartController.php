<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/Auth.php';

class CartController {
    
    public function showCart() {
        require_once __DIR__ . '/../views/shop/cart.php';
    }
    
    public function wishlist() {
        if (!Auth::isAuthenticated()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        echo "<!DOCTYPE html><html><head><title>My Wishlist</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-gray-100'><div class='container mx-auto px-4 py-8'><h1 class='text-3xl font-bold mb-4'>My Wishlist</h1><p>Your wishlist is empty.</p><a href='" . BASE_URL . "/shop' class='bg-blue-600 text-white px-4 py-2 rounded-lg inline-block mt-4'>Browse Products</a></div></body></html>";
    }
}