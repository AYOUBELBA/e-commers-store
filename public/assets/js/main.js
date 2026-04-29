// public/assets/js/main.js
// Toast notification system
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `mb-3 p-4 rounded-lg shadow-lg ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white transform transition-all duration-300`;
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="ri-${type === 'success' ? 'checkbox-circle' : 'alert'}-line mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    toastContainer.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Price formatting
function formatPrice(price) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price);
}

// Loading skeleton component
class LoadingSkeleton {
    static show(containerId, count = 4) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const skeletons = [];
        for (let i = 0; i < count; i++) {
            skeletons.push('<div class="skeleton h-80 rounded-lg"></div>');
        }
        container.innerHTML = skeletons.join('');
    }
    
    static hide(containerId) {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = '';
        }
    }
}

// Modal handling
class Modal {
    constructor() {
        this.modal = null;
    }
    
    open(content, title = '') {
        this.modal = document.createElement('div');
        this.modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        this.modal.innerHTML = `
            <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full mx-4 p-6">
                ${title ? `<h3 class="text-xl font-bold mb-4">${title}</h3>` : ''}
                <div>${content}</div>
                <button onclick="window.modal.close()" class="mt-4 px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300">Close</button>
            </div>
        `;
        document.body.appendChild(this.modal);
    }
    
    close() {
        if (this.modal) {
            this.modal.remove();
            this.modal = null;
        }
    }
}

window.modal = new Modal();

// Form validation
function validateForm(formId, rules) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const inputs = form.querySelectorAll('input, select, textarea');
    let isValid = true;
    
    inputs.forEach(input => {
        const rule = rules[input.name];
        if (!rule) return;
        
        const value = input.value.trim();
        let error = null;
        
        if (rule.required && !value) {
            error = rule.requiredMessage || 'This field is required';
        } else if (value && rule.minLength && value.length < rule.minLength) {
            error = `Minimum ${rule.minLength} characters required`;
        } else if (value && rule.maxLength && value.length > rule.maxLength) {
            error = `Maximum ${rule.maxLength} characters allowed`;
        } else if (value && rule.pattern && !rule.pattern.test(value)) {
            error = rule.patternMessage || 'Invalid format';
        } else if (value && rule.match && value !== document.querySelector(rule.match).value) {
            error = 'Passwords do not match';
        }
        
        const errorElement = input.parentElement.querySelector('.error-message');
        if (error) {
            if (!errorElement) {
                const errorDiv = document.createElement('p');
                errorDiv.className = 'error-message text-red-500 text-sm mt-1';
                errorDiv.textContent = error;
                input.parentElement.appendChild(errorDiv);
            } else {
                errorElement.textContent = error;
            }
            input.classList.add('border-red-500');
            isValid = false;
        } else {
            if (errorElement) errorElement.remove();
            input.classList.remove('border-red-500');
        }
    });
    
    return isValid;
}

// Debounce utility
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Lazy loading images
const lazyLoadImages = () => {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    lazyLoadImages();
    
    // Add loading states to buttons
    document.querySelectorAll('button[type="submit"]').forEach(button => {
        button.addEventListener('click', function(e) {
            if (this.closest('form') && this.closest('form').checkValidity()) {
                this.disabled = true;
                this.innerHTML = '<i class="ri-loader-4-line animate-spin mr-2"></i> Loading...';
                
                setTimeout(() => {
                    this.disabled = false;
                    this.innerHTML = this.getAttribute('data-original-text') || this.innerHTML;
                }, 3000);
            }
        });
    });
});