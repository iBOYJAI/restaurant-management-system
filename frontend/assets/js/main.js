/**
 * Main JavaScript - Shared Utilities
 * Restaurant Food Ordering System
 */

// ============================================
// Cart Management (LocalStorage)
// ============================================
const Cart = {
    // Get cart from localStorage
    get() {
        const cart = localStorage.getItem('restaurant_cart');
        return cart ? JSON.parse(cart) : [];
    },

    // Save cart to localStorage
    save(cart) {
        localStorage.setItem('restaurant_cart', JSON.stringify(cart));
        this.updateBadge();
    },

    // Add item to cart
    add(item, quantity = 1, notes = '') {
        const cart = this.get();
        const existingIndex = cart.findIndex(i => i.id === item.id && i.notes === notes);

        if (existingIndex > -1) {
            cart[existingIndex].quantity = parseInt(cart[existingIndex].quantity);
        } else {
            cart.push({
                id: item.id,
                name: item.name,
                price: item.price,
                image_url: item.image_url,
                quantity: quantity,
                notes: notes
            });
        }

        this.save(cart);
        showToast('Item added to cart!', 'success');
    },

    // Update item quantity
    updateQuantity(index, quantity) {
        const cart = this.get();
        if (cart[index]) {
            cart[index].quantity = parseInt(quantity);
            if (cart[index].quantity <= 0) {
                cart.splice(index, 1);
            }
            this.save(cart);
        }
    },

    // Remove item from cart
    remove(index) {
        const cart = this.get();
        cart.splice(index, 1);
        this.save(cart);
    },

    // Clear entire cart
    clear() {
        localStorage.removeItem('restaurant_cart');
        this.updateBadge();
    },

    // Get total items count
    getCount() {
        const cart = this.get();
        return cart.reduce((total, item) => total + item.quantity, 0);
    },

    // Get total amount
    getTotal() {
        const cart = this.get();
        return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    },

    // Update cart badge
    updateBadge() {
        const badge = document.querySelector('.cart-count');
        if (badge) {
            const count = this.getCount();
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }
    }
};

// ============================================
// AJAX Helper Functions
// ============================================

/**
 * API Request Helper
 * Automatically detects if running in subdirectory (like htdocs/restaurant)
 */
async function apiRequest(endpoint, method = 'GET', data = null) {
    // Get the base path from current location
    const pathParts = window.location.pathname.split('/');
    const basePath = pathParts[1] && pathParts[1] !== 'index.php' ? '/' + pathParts[1] : '';

    // Construct full URL
    const url = basePath + endpoint;

    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'include' // Important for session cookies
    };

    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, options);
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, message: 'Network error' };
    }
}

// ============================================
// Toast Notifications
// ============================================
function showToast(message, type = 'info', duration = 3000) {
    // Create toast container if it doesn't exist
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-weight: 600;">${getToastIcon(type)}</span>
            <span>${message}</span>
        </div>
    `;

    container.appendChild(toast);

    // Remove after duration
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

function getToastIcon(type) {
    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };
    return icons[type] || icons.info;
}

// ============================================
// Modal Management
// ============================================
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Close modal on backdrop click
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// ============================================
// Form Validation
// ============================================
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const inputs = form.querySelectorAll('[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = 'var(--danger)';
            isValid = false;
        } else {
            input.style.borderColor = '';
        }
    });

    return isValid;
}

// ============================================
// Utility Functions
// ============================================
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toFixed(2);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

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

// ============================================
// Loading States
// ============================================
function showLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.classList.add('loading');
    }
}

function hideLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.classList.remove('loading');
    }
}

// ============================================
// Initialize on DOM Ready
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    // Update cart badge
    Cart.updateBadge();

    // Auto-dismiss alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
