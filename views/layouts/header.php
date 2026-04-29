<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/store-projet/public');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? APP_NAME; ?> - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        /* Custom animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeIn 0.5s ease-out; }
        
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.2);
        }
        
        .product-image {
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        
        .cart-count {
            transition: transform 0.2s;
        }
        
        .cart-count.update {
            transform: scale(1.2);
        }
        
        /* Dark mode styles */
        .dark {
            background-color: #1a1a2e;
            color: #f0f0f0;
        }
        
        .dark .bg-white { background-color: #1f2937; }
        .dark .bg-gray-50 { background-color: #111827; }
        .dark .bg-gray-100 { background-color: #1f2937; }
        .dark .border-gray-200 { border-color: #374151; }
        .dark .text-gray-600 { color: #9ca3af; }
        .dark .text-gray-700 { color: #d1d5db; }
        .dark .text-gray-900 { color: #f3f4f6; }
        
        @media (max-width: 768px) {
            .container { padding-left: 1rem; padding-right: 1rem; }
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 dark:text-gray-100">
    <!-- Navigation Bar -->
    <nav class="bg-white dark:bg-gray-800 shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex flex-wrap justify-between items-center">
                <!-- Logo -->
                <a href="<?php echo BASE_URL; ?>" class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    <?php echo APP_NAME; ?>
                </a>
                
                <!-- Search Bar -->
                <div class="relative w-full max-w-md mx-4">
                    <input type="text" id="searchInput" placeholder="Search products..." 
                           class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="ri-search-line absolute right-3 top-3 text-gray-400"></i>
                    <div id="searchSuggestions" class="absolute hidden bg-white dark:bg-gray-800 w-full mt-1 rounded-lg shadow-lg z-50 max-h-96 overflow-y-auto"></div>
                </div>
                
                <!-- Icons -->
                <div class="flex items-center space-x-4">
                    <button id="themeToggle" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="ri-moon-line dark:ri-sun-line text-xl"></i>
                    </button>
                    
                    <a href="<?php echo BASE_URL; ?>/wishlist" class="relative p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full">
                        <i class="ri-heart-line text-xl"></i>
                    </a>
                    
                    <a href="<?php echo BASE_URL; ?>/cart" class="relative p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full">
                        <i class="ri-shopping-cart-line text-xl"></i>
                        <span id="cartCount" class="cart-count absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
                    </a>
                    
                    <!-- User Menu -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="relative group">
                            <button class="flex items-center space-x-2 p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                                <i class="ri-user-line text-xl"></i>
                                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                                <i class="ri-arrow-down-s-line"></i>
                            </button>
                            <div class="absolute right-0 hidden group-hover:block bg-white dark:bg-gray-800 shadow-lg rounded-lg w-48 mt-2 z-50">
                                <a href="<?php echo BASE_URL; ?>/profile" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <i class="ri-user-settings-line mr-2"></i> My Profile
                                </a>
                                <a href="<?php echo BASE_URL; ?>/orders" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <i class="ri-shopping-bag-line mr-2"></i> My Orders
                                </a>
                                <a href="<?php echo BASE_URL; ?>/wishlist" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <i class="ri-heart-line mr-2"></i> Wishlist
                                </a>
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                    <div class="border-t dark:border-gray-700 my-1"></div>
                                    <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="block px-4 py-2 text-blue-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <i class="ri-dashboard-line mr-2"></i> Admin Panel
                                    </a>
                                <?php endif; ?>
                                <div class="border-t dark:border-gray-700 my-1"></div>
                                <a href="<?php echo BASE_URL; ?>/logout" class="block px-4 py-2 text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <i class="ri-logout-box-line mr-2"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/login" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Login</a>
                        <a href="<?php echo BASE_URL; ?>/register" class="px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900 transition">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="fixed top-20 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50" id="successMessage">
            <i class="ri-checkbox-circle-line mr-2"></i>
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
        <script>setTimeout(() => document.getElementById('successMessage')?.remove(), 3000);</script>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="fixed top-20 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50" id="errorMessage">
            <i class="ri-alert-line mr-2"></i>
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
        <script>setTimeout(() => document.getElementById('errorMessage')?.remove(), 3000);</script>
    <?php endif; ?>

    <main class="container mx-auto px-4 py-8 fade-in">