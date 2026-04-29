<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/Auth.php';

class OrderController {
    
    public function showCheckout() {
        if (!Auth::isAuthenticated()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        echo "<!DOCTYPE html><html><head><title>Checkout</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-gray-100'><div class='container mx-auto px-4 py-8'><h1 class='text-3xl font-bold mb-4'>Checkout</h1><p>Checkout functionality coming soon.</p><a href='" . BASE_URL . "/cart' class='bg-blue-600 text-white px-4 py-2 rounded-lg'>Back to Cart</a></div></body></html>";
    }
    
    public function processCheckout() {
        header('Location: ' . BASE_URL . '/order-confirmation');
    }
    
    public function confirmation() {
        echo "<!DOCTYPE html><html><head><title>Order Confirmation</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-gray-100'><div class='container mx-auto px-4 py-8'><h1 class='text-3xl font-bold mb-4'>Order Confirmed!</h1><p>Thank you for your order.</p><a href='" . BASE_URL . "/shop' class='bg-blue-600 text-white px-4 py-2 rounded-lg inline-block mt-4'>Continue Shopping</a></div></body></html>";
    }
    
    public function userOrders() {
        if (!Auth::isAuthenticated()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        echo "<!DOCTYPE html><html><head><title>My Orders</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-gray-100'><div class='container mx-auto px-4 py-8'><h1 class='text-3xl font-bold mb-4'>My Orders</h1><p>You have no orders yet.</p><a href='" . BASE_URL . "/shop' class='bg-blue-600 text-white px-4 py-2 rounded-lg inline-block mt-4'>Start Shopping</a></div></body></html>";
    }
}