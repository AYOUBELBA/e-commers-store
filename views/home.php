<?php
$pageTitle = 'Home';
require_once __DIR__ . '/layouts/header.php';
?>

<!-- Hero Banner -->
<div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 md:p-12 mb-12 text-white">
    <div class="max-w-2xl">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Welcome to <?php echo APP_NAME; ?></h1>
        <p class="text-xl mb-6">Your premier online shopping destination for quality products at competitive prices.</p>
        <a href="<?php echo BASE_URL; ?>/shop" class="inline-block bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition transform hover:scale-105">
            Shop Now <i class="ri-arrow-right-line ml-2"></i>
        </a>
    </div>
</div>

<!-- Features -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
    <div class="text-center p-4">
        <i class="ri-truck-line text-4xl text-blue-600 mb-2"></i>
        <h3 class="font-semibold">Free Shipping</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">On orders over $50</p>
    </div>
    <div class="text-center p-4">
        <i class="ri-shield-check-line text-4xl text-blue-600 mb-2"></i>
        <h3 class="font-semibold">Secure Payment</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">100% secure transactions</p>
    </div>
    <div class="text-center p-4">
        <i class="ri-customer-service-line text-4xl text-blue-600 mb-2"></i>
        <h3 class="font-semibold">24/7 Support</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Dedicated customer service</p>
    </div>
    <div class="text-center p-4">
        <i class="ri-refund-line text-4xl text-blue-600 mb-2"></i>
        <h3 class="font-semibold">30-Day Returns</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Hassle-free returns</p>
    </div>
</div>

<!-- Featured Products -->
<h2 class="text-3xl font-bold mb-6">Featured Products</h2>
<div id="featuredProducts" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
    <div class="skeleton h-80 rounded-lg"></div>
    <div class="skeleton h-80 rounded-lg"></div>
    <div class="skeleton h-80 rounded-lg"></div>
    <div class="skeleton h-80 rounded-lg"></div>
</div>

<!-- Categories -->
<h2 class="text-3xl font-bold mb-6">Shop by Category</h2>
<div id="categories" class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-12">
    <div class="skeleton h-32 rounded-lg"></div>
    <div class="skeleton h-32 rounded-lg"></div>
    <div class="skeleton h-32 rounded-lg"></div>
    <div class="skeleton h-32 rounded-lg"></div>
    <div class="skeleton h-32 rounded-lg"></div>
</div>

<script>
// Load products and categories when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (typeof loadProducts === 'function') {
        loadProducts('featuredProducts', 4);
    }
    if (typeof loadCategories === 'function') {
        loadCategories('categories');
    }
});
</script>

<style>
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}
@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.product-card:hover .product-image {
    transform: scale(1.05);
}
.product-image {
    transition: transform 0.3s ease;
}
.cart-count.update {
    transform: scale(1.2);
    transition: transform 0.2s;
}
</style>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>