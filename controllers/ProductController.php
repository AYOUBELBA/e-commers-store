<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

class ProductController {
    
    public function home() {
        require_once __DIR__ . '/../views/home.php';
    }
    
    public function shop() {
        require_once __DIR__ . '/../views/shop/index.php';
    }
    
    public function showProduct($slug) {
        $productModel = new Product();
        $product = $productModel->findBySlug($slug);
        
        if (!$product) {
            http_response_code(404);
            echo "<h1>Product Not Found</h1><a href='" . BASE_URL . "/shop'>Back to Shop</a>";
            return;
        }
        
        echo "<!DOCTYPE html><html><head><title>" . htmlspecialchars($product['name']) . "</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-gray-100'><div class='container mx-auto px-4 py-8'><a href='" . BASE_URL . "/shop' class='text-blue-600 mb-4 inline-block'>&larr; Back to Shop</a><div class='bg-white rounded-lg shadow p-6'><img src='" . $product['image_url'] . "' class='w-full max-w-md mx-auto rounded-lg'><h1 class='text-3xl font-bold mt-4'>" . htmlspecialchars($product['name']) . "</h1><p class='text-gray-600 mt-2'>" . htmlspecialchars($product['description']) . "</p><p class='text-2xl font-bold text-blue-600 mt-4'>$" . $product['price'] . "</p><p class='mt-2'>Stock: " . $product['stock'] . "</p><button onclick='addToCart(" . $product['id'] . ")' class='mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700'>Add to Cart</button></div></div><script>function addToCart(id){fetch('" . BASE_URL . "/api/cart',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({product_id:id,quantity:1})}).then(res=>res.json()).then(data=>{alert('Added to cart!')}).catch(()=>{alert('Please login first')})}</script></body></html>";
    }
}