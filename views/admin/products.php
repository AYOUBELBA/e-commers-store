<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/store-projet/public');
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../auth/Auth.php';

if (!Auth::isAdmin()) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$db = Database::getInstance()->getConnection();

// Helper function to create slug
function createSlug($string) {
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $string)));
    return trim($slug, '-');
}

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success_message'] = 'Product deleted successfully';
    header('Location: ' . BASE_URL . '/admin/products');
    exit;
}

// Handle ADD PRODUCT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'] ?? '';
    $slug = createSlug($name);
    $description = $_POST['description'] ?? '';
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $category_id = $_POST['category_id'] ? (int)$_POST['category_id'] : null;
    $featured = isset($_POST['featured']) ? 1 : 0;
    $popular = isset($_POST['popular']) ? 1 : 0;
    $image_url = $_POST['image_url'] ?? '';
    
    $stmt = $db->prepare("INSERT INTO products (name, slug, description, price, stock, category_id, featured, popular, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $slug, $description, $price, $stock, $category_id, $featured, $popular, $image_url]);
    
    $_SESSION['success_message'] = 'Product added successfully';
    header('Location: ' . BASE_URL . '/admin/products');
    exit;
}

// Handle EDIT PRODUCT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)$_POST['product_id'];
    $name = $_POST['name'] ?? '';
    $slug = createSlug($name);
    $description = $_POST['description'] ?? '';
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $category_id = $_POST['category_id'] ? (int)$_POST['category_id'] : null;
    $featured = isset($_POST['featured']) ? 1 : 0;
    $popular = isset($_POST['popular']) ? 1 : 0;
    $image_url = $_POST['image_url'] ?? '';
    
    $stmt = $db->prepare("UPDATE products SET name=?, slug=?, description=?, price=?, stock=?, category_id=?, featured=?, popular=?, image_url=? WHERE id=?");
    $stmt->execute([$name, $slug, $description, $price, $stock, $category_id, $featured, $popular, $image_url, $id]);
    
    $_SESSION['success_message'] = 'Product updated successfully';
    header('Location: ' . BASE_URL . '/admin/products');
    exit;
}

// Get all products
$products = $db->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Get product for editing
$editProduct = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editProduct = $stmt->fetch();
}

$pageTitle = 'Manage Products';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="flex">
    <!-- Sidebar -->
    <div class="w-64 bg-gray-900 text-white min-h-screen rounded-lg mr-6">
        <div class="p-4 text-xl font-bold border-b border-gray-800">Admin Panel</div>
        <nav class="mt-4">
            <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="flex items-center px-4 py-3 hover:bg-gray-800"><i class="ri-dashboard-line mr-3"></i> Dashboard</a>
            <a href="<?php echo BASE_URL; ?>/admin/products" class="flex items-center px-4 py-3 bg-gray-800"><i class="ri-shopping-bag-line mr-3"></i> Products</a>
            <a href="<?php echo BASE_URL; ?>/admin/orders" class="flex items-center px-4 py-3 hover:bg-gray-800"><i class="ri-shopping-cart-line mr-3"></i> Orders</a>
            <a href="<?php echo BASE_URL; ?>/admin/users" class="flex items-center px-4 py-3 hover:bg-gray-800"><i class="ri-user-line mr-3"></i> Users</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Manage Products</h1>
            <button onclick="showAddProductModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="ri-add-line mr-2"></i>Add Product
            </button>
        </div>

        <!-- Products Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Image</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Featured</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No products found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                        <tr class="border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-6 py-4 text-gray-900 dark:text-gray-100"><?php echo $product['id']; ?></td>
                            <td class="px-6 py-4">
                                <?php if (!empty($product['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="w-12 h-12 object-cover rounded" onerror="this.src='https://via.placeholder.com/50'">
                                <?php else: ?>
                                    <div class="w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded flex items-center justify-center">
                                        <i class="ri-image-line text-gray-400 dark:text-gray-500"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($product['name']); ?></td>
                            <td class="px-6 py-4 text-gray-900 dark:text-gray-100">$<?php echo number_format($product['price'], 2); ?></td>
                            <td class="px-6 py-4">
                                <span class="<?php echo $product['stock'] < 10 ? 'text-red-500 font-bold' : 'text-gray-900 dark:text-gray-100'; ?>">
                                    <?php echo $product['stock']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($product['featured']): ?>
                                    <span class="px-2 py-1 text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full">Yes</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-full">No</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <a href="?edit=<?php echo $product['id']; ?>" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 mr-3">Edit</a>
                                <a href="?delete=<?php echo $product['id']; ?>" onclick="return confirm('Delete this product?')" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div id="addProductModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Add New Product</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Product Name *</label>
                <input type="text" name="name" required 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Description</label>
                <textarea name="description" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                 bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Price *</label>
                    <input type="number" name="price" step="0.01" required 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Stock *</label>
                    <input type="number" name="stock" required 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Category</label>
                <select name="category_id" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Image URL</label>
                <input type="url" name="image_url" placeholder="https://images.unsplash.com/..." 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Use any image URL from the web (Unsplash, Pexels, etc.)</p>
            </div>
            
            <div class="flex gap-4 mb-4">
                <label class="flex items-center text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="featured" class="mr-2 w-4 h-4"> Featured Product
                </label>
                <label class="flex items-center text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="popular" class="mr-2 w-4 h-4"> Popular Product
                </label>
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeAddProductModal()" 
                        class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Add Product
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<?php if ($editProduct): ?>
<div id="editProductModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Edit Product</h2>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="product_id" value="<?php echo $editProduct['id']; ?>">
            
            <?php if (!empty($editProduct['image_url'])): ?>
            <div class="mb-3 text-center">
                <img src="<?php echo htmlspecialchars($editProduct['image_url']); ?>" class="w-32 h-32 object-cover rounded mx-auto">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Current Image</p>
            </div>
            <?php endif; ?>
            
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Product Name *</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($editProduct['name']); ?>" required 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Description</label>
                <textarea name="description" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                 bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo htmlspecialchars($editProduct['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Price *</label>
                    <input type="number" name="price" step="0.01" value="<?php echo $editProduct['price']; ?>" required 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Stock *</label>
                    <input type="number" name="stock" value="<?php echo $editProduct['stock']; ?>" required 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Category</label>
                <select name="category_id" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $editProduct['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Image URL</label>
                <input type="url" name="image_url" value="<?php echo htmlspecialchars($editProduct['image_url'] ?? ''); ?>" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Change URL to update image</p>
            </div>
            
            <div class="flex gap-4 mb-4">
                <label class="flex items-center text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="featured" <?php echo $editProduct['featured'] ? 'checked' : ''; ?> class="mr-2 w-4 h-4"> Featured Product
                </label>
                <label class="flex items-center text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="popular" <?php echo $editProduct['popular'] ? 'checked' : ''; ?> class="mr-2 w-4 h-4"> Popular Product
                </label>
            </div>
            
            <div class="flex justify-end gap-3">
                <a href="<?php echo BASE_URL; ?>/admin/products" 
                   class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function showAddProductModal() {
    document.getElementById('addProductModal').classList.remove('hidden');
    document.getElementById('addProductModal').classList.add('flex');
}
function closeAddProductModal() {
    document.getElementById('addProductModal').classList.add('hidden');
    document.getElementById('addProductModal').classList.remove('flex');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>