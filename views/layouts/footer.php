    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-12">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="font-bold text-lg mb-4"><?php echo APP_NAME; ?></h3>
                    <p class="text-gray-600 dark:text-gray-400">Your premier online shopping destination.</p>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="<?php echo BASE_URL; ?>/shop" class="text-gray-600 dark:text-gray-400 hover:text-blue-600">Shop</a></li>
                        <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-blue-600">About Us</a></li>
                        <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-blue-600">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-4">Customer Service</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-blue-600">FAQs</a></li>
                        <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-blue-600">Shipping Info</a></li>
                        <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-blue-600">Returns Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-4">Newsletter</h3>
                    <form id="newsletterForm" class="space-y-2">
                        <input type="email" placeholder="Your email" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700">
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Subscribe</button>
                    </form>
                </div>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 mt-8 pt-8 text-center text-gray-600 dark:text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Toast Container -->
    <div id="toastContainer" class="toast"></div>

    <!-- JavaScript -->
    <script src="<?php echo BASE_URL; ?>/assets/js/cart.js"></script>
    
    <script>
        // Dark mode
        const darkMode = localStorage.getItem('darkMode') === 'true';
        if (darkMode) document.documentElement.classList.add('dark');
        
        document.getElementById('themeToggle')?.addEventListener('click', () => {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', isDark);
        });
        
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const suggestionsDiv = document.getElementById('searchSuggestions');
        
        if (searchInput) {
            let timeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(timeout);
                const query = e.target.value;
                if (query.length < 2) {
                    suggestionsDiv.classList.add('hidden');
                    return;
                }
                
                timeout = setTimeout(async () => {
                    try {
                        const res = await fetch(`<?php echo BASE_URL; ?>/api/index.php?action=search&q=${encodeURIComponent(query)}`);
                        const products = await res.json();
                        if (products.length) {
                            suggestionsDiv.innerHTML = products.map(p => `
                                <a href="<?php echo BASE_URL; ?>/product/${p.slug}" class="flex items-center p-3 hover:bg-gray-100 dark:hover:bg-gray-700 border-b">
                                    <img src="${p.image_url || 'https://placehold.co/50'}" class="w-12 h-12 object-cover rounded">
                                    <div class="ml-3">
                                        <div class="font-semibold">${escapeHtml(p.name)}</div>
                                        <div class="text-blue-600">$${p.price}</div>
                                    </div>
                                </a>
                            `).join('');
                            suggestionsDiv.classList.remove('hidden');
                        } else {
                            suggestionsDiv.classList.add('hidden');
                        }
                    } catch(e) { console.error(e); }
                }, 300);
            });
            
            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target)) suggestionsDiv.classList.add('hidden');
            });
        }
        
        // Newsletter
        document.getElementById('newsletterForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            if (typeof showToast === 'function') {
                showToast('Subscribed successfully!', 'success');
            }
            e.target.reset();
        });
        
        // Escape HTML helper
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>