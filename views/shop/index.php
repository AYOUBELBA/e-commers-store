<?php
$pageTitle = 'Shop';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="flex flex-col lg:flex-row gap-6">
    <!-- Sidebar Filters -->
    <div class="lg:w-80">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sticky top-24">
            <h3 class="font-bold text-lg mb-4 pb-2 border-b dark:border-gray-700">Filters</h3>
            
            <!-- Search -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Search Products</label>
                <input type="text" id="filterSearchInput" placeholder="Search..." 
                       class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            </div>
            
            <!-- Categories -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Categories</label>
                <select id="categoryFilter" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    <option value="">All Categories</option>
                </select>
            </div>
            
            <!-- Price Range -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Price Range</label>
                <div class="flex gap-2">
                    <input type="number" id="minPrice" placeholder="Min" class="w-1/2 px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    <input type="number" id="maxPrice" placeholder="Max" class="w-1/2 px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>
            </div>
            
            <!-- Sort -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Sort By</label>
                <select id="sortFilter" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    <option value="newest">Newest First</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                    <option value="name_asc">Name: A to Z</option>
                    <option value="name_desc">Name: Z to A</option>
                </select>
            </div>
            
            <!-- Buttons -->
            <div class="flex gap-2">
                <button id="applyFiltersBtn" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Apply Filters
                </button>
                <button id="resetFiltersBtn" class="flex-1 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="flex-1">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">All Products</h1>
            <div id="productsCount" class="text-gray-600 dark:text-gray-400"></div>
        </div>
        
        <div id="productsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="skeleton h-80 rounded-lg"></div>
            <div class="skeleton h-80 rounded-lg"></div>
            <div class="skeleton h-80 rounded-lg"></div>
        </div>
        
        <!-- Pagination -->
        <div id="pagination" class="flex justify-center gap-2 mt-8"></div>
    </div>
</div>

<script>
let allProducts = [];
let currentPage = 1;
const productsPerPage = 9;

// Load products with filters
async function loadProductsWithFilters() {
    try {
        // Show loading skeleton
        document.getElementById('productsGrid').innerHTML = `
            <div class="skeleton h-80 rounded-lg"></div>
            <div class="skeleton h-80 rounded-lg"></div>
            <div class="skeleton h-80 rounded-lg"></div>
        `;
        
        // Get filter values
        const search = document.getElementById('filterSearchInput')?.value || '';
        const categoryId = document.getElementById('categoryFilter')?.value || '';
        const minPrice = document.getElementById('minPrice')?.value || '';
        const maxPrice = document.getElementById('maxPrice')?.value || '';
        const sort = document.getElementById('sortFilter')?.value || 'newest';
        
        // Build URL with filters
        let url = '<?php echo BASE_URL; ?>/api/index.php?action=products';
        const params = [];
        
        if (search) params.push(`search=${encodeURIComponent(search)}`);
        if (categoryId) params.push(`category=${categoryId}`);
        if (minPrice) params.push(`min_price=${minPrice}`);
        if (maxPrice) params.push(`max_price=${maxPrice}`);
        if (sort) params.push(`sort=${sort}`);
        
        if (params.length) {
            url += '&' + params.join('&');
        }
        
        const response = await fetch(url);
        const products = await response.json();
        
        allProducts = products;
        updateProductsCount();
        displayProducts();
        
    } catch (error) {
        console.error('Error loading products:', error);
        document.getElementById('productsGrid').innerHTML = '<div class="col-span-3 text-center text-red-500 py-12">Error loading products. Please refresh the page.</div>';
    }
}

// Display products with pagination
function displayProducts() {
    const start = (currentPage - 1) * productsPerPage;
    const end = start + productsPerPage;
    const paginatedProducts = allProducts.slice(start, end);
    
    if (paginatedProducts.length === 0) {
        document.getElementById('productsGrid').innerHTML = '<div class="col-span-3 text-center text-gray-500 py-12">No products found. Try adjusting your filters.</div>';
        document.getElementById('pagination').innerHTML = '';
        return;
    }
    
    const html = paginatedProducts.map(product => `
        <div class="product-card bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-xl transition group">
            <div class="relative overflow-hidden">
                <img src="${product.image_url || 'https://placehold.co/300x200?text=No+Image'}" 
                     alt="${escapeHtml(product.name)}" 
                     class="product-image w-full h-48 object-cover group-hover:scale-105 transition duration-300"
                     onerror="this.src='https://placehold.co/300x200?text=No+Image'">
                ${product.featured ? '<span class="absolute top-2 left-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded">Featured</span>' : ''}
                ${product.stock < 10 ? '<span class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">Low Stock</span>' : ''}
            </div>
            <div class="p-4">
                <h3 class="font-semibold text-lg mb-1 line-clamp-1">${escapeHtml(product.name)}</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-2">${product.category_name || 'General'}</p>
                <div class="flex items-center justify-between mt-2">
                    <div>
                        <p class="text-blue-600 font-bold text-xl">$${parseFloat(product.price).toFixed(2)}</p>
                        ${product.compare_price ? `<p class="text-gray-400 text-sm line-through">$${parseFloat(product.compare_price).toFixed(2)}</p>` : ''}
                    </div>
                    <button onclick="addToCart(${product.id})" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    
    document.getElementById('productsGrid').innerHTML = html;
    updatePagination();
}

// Update products count
function updateProductsCount() {
    const count = allProducts.length;
    const countElement = document.getElementById('productsCount');
    if (countElement) {
        countElement.innerHTML = `${count} product${count !== 1 ? 's' : ''} found`;
    }
}

// Update pagination
function updatePagination() {
    const totalPages = Math.ceil(allProducts.length / productsPerPage);
    const paginationElement = document.getElementById('pagination');
    
    if (!paginationElement) return;
    
    if (totalPages <= 1) {
        paginationElement.innerHTML = '';
        return;
    }
    
    let paginationHtml = '';
    
    // Previous button
    if (currentPage > 1) {
        paginationHtml += `<button onclick="changePage(${currentPage - 1})" class="px-3 py-1 border rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">← Prev</button>`;
    }
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === currentPage) {
            paginationHtml += `<button class="px-3 py-1 bg-blue-600 text-white rounded-lg">${i}</button>`;
        } else if (Math.abs(i - currentPage) <= 2 || i === 1 || i === totalPages) {
            paginationHtml += `<button onclick="changePage(${i})" class="px-3 py-1 border rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">${i}</button>`;
        } else if (Math.abs(i - currentPage) === 3) {
            paginationHtml += `<span class="px-2">...</span>`;
        }
    }
    
    // Next button
    if (currentPage < totalPages) {
        paginationHtml += `<button onclick="changePage(${currentPage + 1})" class="px-3 py-1 border rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">Next →</button>`;
    }
    
    paginationElement.innerHTML = paginationHtml;
}

// Change page
function changePage(page) {
    currentPage = page;
    displayProducts();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Load categories for filter
async function loadCategoriesForFilter() {
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/api/index.php?action=categories');
        const categories = await response.json();
        
        const select = document.getElementById('categoryFilter');
        if (!select) return;
        
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Apply filters
function applyFilters() {
    currentPage = 1;
    loadProductsWithFilters();
}

// Reset filters
function resetFilters() {
    const filterSearch = document.getElementById('filterSearchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const minPrice = document.getElementById('minPrice');
    const maxPrice = document.getElementById('maxPrice');
    const sortFilter = document.getElementById('sortFilter');
    
    if (filterSearch) filterSearch.value = '';
    if (categoryFilter) categoryFilter.value = '';
    if (minPrice) minPrice.value = '';
    if (maxPrice) maxPrice.value = '';
    if (sortFilter) sortFilter.value = 'newest';
    
    currentPage = 1;
    loadProductsWithFilters();
}

// Escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadCategoriesForFilter();
    loadProductsWithFilters();
    
    const applyBtn = document.getElementById('applyFiltersBtn');
    const resetBtn = document.getElementById('resetFiltersBtn');
    const filterSearch = document.getElementById('filterSearchInput');
    
    if (applyBtn) applyBtn.addEventListener('click', applyFilters);
    if (resetBtn) resetBtn.addEventListener('click', resetFilters);
    if (filterSearch) {
        filterSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') applyFilters();
        });
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
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.product-card:hover .product-image {
    transform: scale(1.05);
}
.product-image {
    transition: transform 0.3s ease;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>