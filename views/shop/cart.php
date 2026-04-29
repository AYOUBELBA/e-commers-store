<?php
$pageTitle = 'Shopping Cart';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-8">Shopping Cart</h1>
    
    <div id="cartItems" class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="text-center py-12">
            <i class="ri-shopping-cart-line text-6xl text-gray-400 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">Loading cart...</h3>
        </div>
    </div>
    
    <div id="cartSummary" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hidden">
        <h2 class="text-xl font-bold mb-4">Order Summary</h2>
        <div class="space-y-2 mb-4">
            <div class="flex justify-between"><span>Subtotal:</span><span id="subtotal">$0.00</span></div>
            <div class="flex justify-between"><span>Tax (10%):</span><span id="tax">$0.00</span></div>
            <div class="flex justify-between"><span>Shipping:</span><span id="shipping">$0.00</span></div>
            <div class="border-t pt-2 mt-2 font-bold">
                <div class="flex justify-between"><span>Total:</span><span id="total">$0.00</span></div>
            </div>
        </div>
        <a href="<?php echo BASE_URL; ?>/checkout" class="block w-full bg-blue-600 text-white text-center py-3 rounded-lg hover:bg-blue-700">Proceed to Checkout</a>
    </div>
</div>

<script>
async function loadCart() {
    try {
        const res = await fetch('<?php echo BASE_URL; ?>/api/index.php?action=cart');
        const items = await res.json();
        const container = document.getElementById('cartItems');
        
        if (items.length === 0) {
            container.innerHTML = `<div class="text-center py-12"><i class="ri-shopping-cart-line text-6xl text-gray-400 mb-4"></i><h3 class="text-xl font-semibold mb-2">Your cart is empty</h3><p class="text-gray-600 mb-4">Looks like you haven't added anything yet</p><a href="<?php echo BASE_URL; ?>/shop" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg">Continue Shopping</a></div>`;
            document.getElementById('cartSummary').classList.add('hidden');
            return;
        }
        
        let subtotal = 0;
        let html = '';
        for (const item of items) {
            const itemTotal = parseFloat(item.price) * item.quantity;
            subtotal += itemTotal;
            html += `<div class="flex flex-wrap md:flex-nowrap gap-4 p-4 border-b dark:border-gray-700">
                        <img src="${item.image_url || 'https://via.placeholder.com/100'}" class="w-24 h-24 object-cover rounded" onerror="this.src='https://via.placeholder.com/100'">
                        <div class="flex-1">
                            <h3 class="font-semibold">${item.name}</h3>
                            <p class="text-blue-600 font-bold">$${parseFloat(item.price).toFixed(2)}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="updateQuantity(${item.product_id}, ${item.quantity - 1})" class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300">-</button>
                            <span class="w-12 text-center">${item.quantity}</span>
                            <button onclick="updateQuantity(${item.product_id}, ${item.quantity + 1})" class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300">+</button>
                        </div>
                        <div class="text-right">
                            <p class="font-bold">$${itemTotal.toFixed(2)}</p>
                            <button onclick="removeItem(${item.product_id})" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                        </div>
                    </div>`;
        }
        
        container.innerHTML = html;
        
        const tax = subtotal * 0.10;
        const shipping = subtotal >= 50 ? 0 : 5.99;
        const total = subtotal + tax + shipping;
        
        document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
        document.getElementById('tax').textContent = `$${tax.toFixed(2)}`;
        document.getElementById('shipping').textContent = shipping === 0 ? 'Free' : `$${shipping.toFixed(2)}`;
        document.getElementById('total').textContent = `$${total.toFixed(2)}`;
        document.getElementById('cartSummary').classList.remove('hidden');
    } catch(e) { 
        console.error(e);
        document.getElementById('cartItems').innerHTML = `<div class="text-center py-12 text-red-500">Error loading cart</div>`;
    }
}

async function updateQuantity(productId, quantity) {
    if (quantity < 1) return removeItem(productId);
    try {
        const res = await fetch('<?php echo BASE_URL; ?>/api/index.php?action=update-cart', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId, quantity: quantity })
        });
        if (res.ok) { loadCart(); updateCartCount(); }
    } catch(e) { console.error(e); }
}

async function removeItem(productId) {
    try {
        const res = await fetch('<?php echo BASE_URL; ?>/api/index.php?action=remove-from-cart&id=' + productId);
        if (res.ok) { loadCart(); updateCartCount(); showToast('Item removed', 'success'); }
    } catch(e) { console.error(e); }
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
    toast.innerHTML = `<i class="ri-${type === 'success' ? 'checkbox-circle' : 'alert'}-line mr-2"></i>${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function updateCartCount() {
    fetch('<?php echo BASE_URL; ?>/api/index.php?action=cart-count')
        .then(res => res.json())
        .then(data => {
            const cartCount = document.getElementById('cartCount');
            if (cartCount) cartCount.textContent = data.count || 0;
        })
        .catch(() => {});
}

// Load cart when page loads
loadCart();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>