<?php
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../layouts/header.php';
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/store-projet/public');
}

// Get stats from database
$db = Database::getInstance()->getConnection();
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$totalRevenue = $db->query("SELECT SUM(total_amount) FROM orders WHERE status = 'delivered'")->fetchColumn() ?: 0;
$pendingOrders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

// Recent orders
$recentOrders = $db->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5")->fetchAll();

// Low stock products
$lowStock = $db->query("SELECT COUNT(*) FROM products WHERE stock < 10")->fetchColumn();
?>

<div class="flex">
    <!-- Admin Sidebar -->
    <div class="w-64 bg-gray-900 text-white min-h-screen rounded-lg mr-6">
        <div class="p-4 text-xl font-bold border-b border-gray-800">Admin Panel</div>
        <nav class="mt-4">
            <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="flex items-center px-4 py-3 bg-gray-800">
                <i class="ri-dashboard-line mr-3"></i> Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/products" class="flex items-center px-4 py-3 hover:bg-gray-800">
                <i class="ri-shopping-bag-line mr-3"></i> Products
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/orders" class="flex items-center px-4 py-3 hover:bg-gray-800">
                <i class="ri-shopping-cart-line mr-3"></i> Orders
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/users" class="flex items-center px-4 py-3 hover:bg-gray-800">
                <i class="ri-user-line mr-3"></i> Users
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/categories" class="flex items-center px-4 py-3 hover:bg-gray-800">
                <i class="ri-folder-line mr-3"></i> Categories
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1">
        <h1 class="text-3xl font-bold mb-8">Dashboard Overview</h1>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-gray-500 text-sm">Products</p><p class="text-3xl font-bold"><?php echo $totalProducts; ?></p></div>
                    <i class="ri-shopping-bag-line text-4xl text-blue-500"></i>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-gray-500 text-sm">Orders</p><p class="text-3xl font-bold"><?php echo $totalOrders; ?></p></div>
                    <i class="ri-shopping-cart-line text-4xl text-green-500"></i>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-gray-500 text-sm">Users</p><p class="text-3xl font-bold"><?php echo $totalUsers; ?></p></div>
                    <i class="ri-user-line text-4xl text-purple-500"></i>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-gray-500 text-sm">Revenue</p><p class="text-3xl font-bold">$<?php echo number_format($totalRevenue, 2); ?></p></div>
                    <i class="ri-money-dollar-circle-line text-4xl text-yellow-500"></i>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-gray-500 text-sm">Low Stock</p><p class="text-3xl font-bold text-red-500"><?php echo $lowStock; ?></p></div>
                    <i class="ri-alert-line text-4xl text-red-500"></i>
                </div>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b"><h2 class="text-xl font-bold">Recent Orders</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr><th class="px-6 py-3 text-left">Order #</th><th class="px-6 py-3 text-left">Customer</th><th class="px-6 py-3 text-left">Amount</th><th class="px-6 py-3 text-left">Status</th><th class="px-6 py-3 text-left">Date</th><th class="px-6 py-3 text-left">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr class="border-t">
                            <td class="px-6 py-4"><?php echo $order['order_number']; ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($order['user_name']); ?></td>
                            <td class="px-6 py-4">$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full <?php echo $order['status'] === 'delivered' ? 'bg-green-100 text-green-800' : ($order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'); ?>"><?php echo ucfirst($order['status']); ?></span></td>
                            <td class="px-6 py-4"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td class="px-6 py-4"><button class="text-blue-600 hover:text-blue-800">View</button></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($recentOrders)): ?>
                        <tr><td colspan="6" class="text-center py-8 text-gray-500">No orders found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>