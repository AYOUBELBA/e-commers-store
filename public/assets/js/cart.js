// cart.js - Pure JavaScript, no PHP
const BASE_URL = 'http://localhost/store-projet/public';

// API endpoints
const API = {
    addToCart: BASE_URL + '/api/index.php?action=add-to-cart',
    getCart: BASE_URL + '/api/index.php?action=cart',
    cartCount: BASE_URL + '/api/index.php?action=cart-count',
    updateCart: BASE_URL + '/api/index.php?action=update-cart',
    removeFromCart: BASE_URL + '/api/index.php?action=remove-from-cart',
    products: BASE_URL + '/api/index.php?action=products',
    categories: BASE_URL + '/api/index.php?action=categories',
    search: BASE_URL + '/api/index.php?action=search'
};

// Show toast notification
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) return;
    
    const toast = document.createElement('div');
    toast.className = `mb-3 p-4 rounded-lg shadow-lg ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white transform transition-all duration-300`;
    toast.innerHTML = `<i class="ri-${type === 'success' ? 'checkbox-circle' : 'alert'}-line mr-2"></i>${message}`;
    toastContainer.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add to cart function
window.addToCart = async function(productId, quantity = 1) {
    console.log('Adding product:', productId);
    
    try {
        const response = await fetch(API.addToCart, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                product_id: productId, 
                quantity: quantity 
            })
        });
        
        const data = await response.json();
        console.log('Add to cart response:', data);
        
        if (data.success) {
            showToast('Added to cart!', 'success');
            // Update cart count
            await updateCartCount();
        } else {
            showToast(data.error || 'Failed to add item', 'error');
        }
    } catch (error) {
        console.error('Add to cart error:', error);
        showToast('Error: ' + error.message, 'error');
    }
};

// Update cart count
async function updateCartCount() {
    try {
        const response = await fetch(API.cartCount);
        const data = await response.json();
        const cartCount = document.getElementById('cartCount');
        if (cartCount) {
            cartCount.textContent = data.count || 0;
            cartCount.classList.add('update');
            setTimeout(() => cartCount.classList.remove('update'), 200);
        }
    } catch (error) {
        console.error('Error updating cart count:', error);
    }
}

// Escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load products
async function loadProducts(containerId = 'featuredProducts', limit = 4) {
    try {
        const response = await fetch(API.products);
        const products = await response.json();
        
        if (!products || products.length === 0) {
            const container = document.getElementById(containerId);
            if (container) {
                container.innerHTML = '<div class="col-span-4 text-center text-gray-500">No products found</div>';
            }
            return;
        }
        
        const displayProducts = products.slice(0, limit);
        const html = displayProducts.map(product => `
            <div class="product-card bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300">
                <img src="${product.image_url || 'https://placehold.co/300x200?text=No+Image'}" 
                     alt="${escapeHtml(product.name)}" 
                     class="product-image w-full h-48 object-cover"
                     onerror="this.src='https://placehold.co/300x200?text=No+Image'">
                <div class="p-4">
                    <h3 class="font-semibold text-lg mb-2">${escapeHtml(product.name)}</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-2">${product.category_name || 'General'}</p>
                    <div class="flex items-center justify-between">
                        <p class="text-blue-600 font-bold text-xl">$${parseFloat(product.price).toFixed(2)}</p>
                        <button onclick="addToCart(${product.id})" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
        
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading products:', error);
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = '<div class="col-span-4 text-center text-red-500">Error loading products</div>';
        }
    }
}

// Load categories
async function loadCategories(containerId = 'categories') {
    try {
        const response = await fetch(API.categories);
        const categories = await response.json();
        
        if (!categories || categories.length === 0) {
            const container = document.getElementById(containerId);
            if (container) {
                container.innerHTML = '<div class="col-span-5 text-center text-gray-500">No categories found</div>';
            }
            return;
        }
        
        const html = categories.slice(0, 5).map(category => `
            <a href="${BASE_URL}/shop?category=${category.slug}" 
               class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center hover:shadow-lg transition group">
                <i class="ri-folder-line text-4xl text-blue-600 mb-2 group-hover:scale-110 transition"></i>
                <h3 class="font-semibold">${escapeHtml(category.name)}</h3>
            </a>
        `).join('');
        
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = '<div class="col-span-5 text-center text-red-500">Error loading categories</div>';
        }
    }
}

// Load cart items (for cart page)
async function loadCartItems() {
    try {
        const response = await fetch(API.getCart);
        const items = await response.json();
        const container = document.getElementById('cartItems');
        
        if (!container) return;
        
        if (!items || items.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="ri-shopping-cart-line text-6xl text-gray-400 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Your cart is empty</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Looks like you haven't added anything yet</p>
                    <a href="${BASE_URL}/shop" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Continue Shopping</a>
                </div>
            `;
            const summary = document.getElementById('cartSummary');
            if (summary) summary.classList.add('hidden');
            return;
        }
        
        let subtotal = 0;
        let html = `
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
        `;
        
        for (const item of items) {
            const itemTotal = parseFloat(item.price) * parseInt(item.quantity);
            subtotal += itemTotal;
            
            html += `
                <div class="p-4 flex flex-wrap md:flex-nowrap gap-4">
                    <img src="${item.image_url || 'https://placehold.co/100'}" 
                         class="w-24 h-24 object-cover rounded" 
                         onerror="this.src='https://placehold.co/100'">
                    <div class="flex-1">
                        <h3 class="font-semibold text-lg">${escapeHtml(item.name)}</h3>
                        <p class="text-blue-600 font-bold">$${parseFloat(item.price).toFixed(2)}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="updateCartQuantity(${item.product_id}, ${parseInt(item.quantity) - 1})" 
                                class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300">-</button>
                        <span class="w-12 text-center font-semibold">${item.quantity}</span>
                        <button onclick="updateCartQuantity(${item.product_id}, ${parseInt(item.quantity) + 1})" 
                                class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300">+</button>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-lg">$${itemTotal.toFixed(2)}</p>
                        <button onclick="removeCartItem(${item.product_id})" 
                                class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                    </div>
                </div>
            `;
        }
        
        const tax = subtotal * 0.10;
        const shipping = subtotal >= 50 ? 0 : 5.99;
        const total = subtotal + tax + shipping;
        
        html += `
            </div>
            <div class="p-4 bg-gray-50 dark:bg-gray-700 border-t">
                <div class="max-w-md ml-auto space-y-2">
                    <div class="flex justify-between"><span>Subtotal:</span><span>$${subtotal.toFixed(2)}</span></div>
                    <div class="flex justify-between"><span>Tax (10%):</span><span>$${tax.toFixed(2)}</span></div>
                    <div class="flex justify-between"><span>Shipping:</span><span>${shipping === 0 ? 'Free' : '$' + shipping.toFixed(2)}</span></div>
                    <div class="border-t pt-2 mt-2 font-bold text-lg">
                        <div class="flex justify-between"><span>Total:</span><span>$${total.toFixed(2)}</span></div>
                    </div>
                    <a href="${BASE_URL}/checkout" class="block w-full bg-blue-600 text-white text-center py-3 rounded-lg hover:bg-blue-700 mt-4">
                        Proceed to Checkout
                    </a>
                </div>
            </div>
        `;
        
        container.innerHTML = html;
        const summary = document.getElementById('cartSummary');
        if (summary) summary.classList.remove('hidden');
        
    } catch (error) {
        console.error('Error loading cart:', error);
        const container = document.getElementById('cartItems');
        if (container) {
            container.innerHTML = '<div class="text-center py-12 text-red-500">Error loading cart</div>';
        }
    }
}

// Update cart quantity
async function updateCartQuantity(productId, quantity) {
    if (quantity < 1) {
        await removeCartItem(productId);
        return;
    }
    
    try {
        const response = await fetch(API.updateCart, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId, quantity: quantity })
        });
        
        if (response.ok) {
            await loadCartItems();
            await updateCartCount();
        }
    } catch (error) {
        console.error('Error updating quantity:', error);
        showToast('Failed to update quantity', 'error');
    }
}

// Remove cart item
async function removeCartItem(productId) {
    try {
        const response = await fetch(API.removeFromCart + '&id=' + productId);
        if (response.ok) {
            await loadCartItems();
            await updateCartCount();
            showToast('Item removed', 'success');
        }
    } catch (error) {
        console.error('Error removing item:', error);
        showToast('Failed to remove item', 'error');
    }
}

// Initialize cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});