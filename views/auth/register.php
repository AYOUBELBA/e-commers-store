<?php
$pageTitle = 'Register';
require_once __DIR__ . '/../layouts/header.php';
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/store-projet/public');
}
?>

<div class="min-h-screen flex items-center justify-center py-12">
    <div class="max-w-md w-full bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold">Create Account</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Join us today</p>
        </div>
        
        <form method="POST" action="<?php echo BASE_URL; ?>/register">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Full Name</label>
                <input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Email Address</label>
                <input type="email" name="email" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Confirm Password</label>
                <input type="password" name="confirm_password" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">Register</button>
        </form>
        
        <p class="text-center mt-6">Already have an account? <a href="<?php echo BASE_URL; ?>/login" class="text-blue-600 hover:underline">Sign In</a></p>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>