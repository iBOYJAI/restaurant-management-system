/**
 * Restaurant Switcher & Branding Component
 * Manages restaurant context and applies branding
 */

class RestaurantManager {
    constructor() {
        this.currentRestaurant = this.loadCurrentRestaurant();
        this.init();
    }

    init() {
        this.loadRestaurantData();
        this.createSwitcherUI();
        this.applyBranding();
    }

    loadCurrentRestaurant() {
        return parseInt(localStorage.getItem('current_restaurant_id')) || 1;
    }

    saveCurrentRestaurant(restaurantId) {
        localStorage.setItem('current_restaurant_id', restaurantId);
        this.currentRestaurant = restaurantId;
    }

    async loadRestaurantData() {
        try {
            // For now, use fallback data. In production, fetch from API
            const fallbackRestaurants = [
                {
                    id: 1,
                    name: 'Main Restaurant',
                    primary_color: '#FF6B35',
                    logo_url: null
                }
            ];

            this.restaurants = fallbackRestaurants;
            this.currentRestaurantData = this.restaurants.find(r => r.id === this.currentRestaurant) || this.restaurants[0];
        } catch (error) {
            console.error('Error loading restaurants:', error);
        }
    }

    createSwitcherUI() {
        // Only show switcher if user has access to multiple restaurants
        if (!this.shouldShowSwitcher()) return;

        const navbar = document.querySelector('.navbar-container') || document.querySelector('header') || document.querySelector('.header');
        if (!navbar) return;

        const switcherHTML = `
            <div class="restaurant-switcher" style="position: relative; display: inline-block; margin-right: var(--space-md);">
                <button class="restaurant-switcher-btn" onclick="restaurantManager.toggleSwitcher()" style="
                    background: white;
                    border: 2px solid var(--primary);
                    padding: 0.5rem 1rem;
                    border-radius: var(--radius-full);
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    gap: var(--space-sm);
                    font-weight: 600;
                    color: var(--primary);
                    transition: all var(--transition-fast);
                ">
                    🏪 <span id="currentRestaurantName">${this.currentRestaurantData.name}</span>
                    <span style="font-size: 0.75rem;">▼</span>
                </button>
                <div class="restaurant-dropdown" id="restaurantDropdown" style="
                    display: none;
                    position: absolute;
                    top: 100%;
                    left: 0;
                    margin-top: 0.5rem;
                    background: white;
                    border-radius: var(--radius-lg);
                    box-shadow: var(--shadow-xl);
                    min-width: 250px;
                    z-index: 1000;
                ">
                    <div style="padding: var(--space-md); border-bottom: 1px solid var(--border);">
                        <strong>Switch Restaurant</strong>
                    </div>
                    <div id="restaurantList"></div>
                </div>
            </div>
        `;

        navbar.insertAdjacentHTML('beforeend', switcherHTML);
        this.renderRestaurantList();

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.restaurant-switcher')) {
                this.closeSwitcher();
            }
        });
    }

    shouldShowSwitcher() {
        // Check if user is logged in and has super_admin role
        const user = this.getCurrentUser();
        return user && (user.role === 'super_admin' || this.restaurants.length > 1);
    }

    getCurrentUser() {
        // Placeholder - in real app, get from session
        return null;
    }

    renderRestaurantList() {
        const listContainer = document.getElementById('restaurantList');
        if (!listContainer) return;

        listContainer.innerHTML = this.restaurants.map(rest => `
            <div class="restaurant-option" onclick="restaurantManager.switchRestaurant(${rest.id})" style="
                padding: var(--space-md);
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: var(--space-sm);
                transition: background var(--transition-fast);
                border-left: 3px solid ${rest.id === this.currentRestaurant ? rest.primary_color : 'transparent'};
                background: ${rest.id === this.currentRestaurant ? 'var(--bg-secondary)' : 'white'};
            " onmouseover="this.style.background='var(--bg-secondary)'" onmouseout="this.style.background='${rest.id === this.currentRestaurant ? 'var(--bg-secondary)' : 'white'}'">
                <div style="
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    background: ${rest.primary_color};
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-size: 1.25rem;
                ">🍽️</div>
                <div style="flex: 1;">
                    <div style="font-weight: 600;">${rest.name}</div>
                    ${rest.id === this.currentRestaurant ? '<small style="color: var(--success);">✓ Active</small>' : ''}
                </div>
            </div>
        `).join('');
    }

    toggleSwitcher() {
        const dropdown = document.getElementById('restaurantDropdown');
        if (dropdown) {
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        }
    }

    closeSwitcher() {
        const dropdown = document.getElementById('restaurantDropdown');
        if (dropdown) {
            dropdown.style.display = 'none';
        }
    }

    switchRestaurant(restaurantId) {
        if (restaurantId === this.currentRestaurant) {
            this.closeSwitcher();
            return;
        }

        this.saveCurrentRestaurant(restaurantId);
        this.currentRestaurantData = this.restaurants.find(r => r.id === restaurantId);

        // Update UI
        const nameElement = document.getElementById('currentRestaurantName');
        if (nameElement) {
            nameElement.textContent = this.currentRestaurantData.name;
        }

        this.applyBranding();
        this.closeSwitcher();

        // Show toast and reload data
        if (typeof showToast === 'function') {
            showToast(`Switched to ${this.currentRestaurantData.name}`, 'success');
        }

        // Reload page to apply restaurant context
        setTimeout(() => window.location.reload(), 500);
    }

    applyBranding() {
        if (!this.currentRestaurantData) return;

        const primaryColor = this.currentRestaurantData.primary_color || '#FF6B35';

        // Apply CSS custom properties
        document.documentElement.style.setProperty('--primary', primaryColor);

        // Update page title if restaurant name is available
        const titleSuffix = ` - ${this.currentRestaurantData.name}`;
        if (!document.title.includes(titleSuffix)) {
            document.title += titleSuffix;
        }

        // Update meta theme color for mobile browsers
        let themeColorMetaElement = document.querySelector('meta[name="theme-color"]');
        if (!themeColorMetaElement) {
            themeColorMetaElement = document.createElement('meta');
            themeColorMetaElement.name = 'theme-color';
            document.head.appendChild(themeColorMetaElement);
        }
        themeColorMetaElement.content = primaryColor;

        // Apply to buttons and primary elements
        const style = document.createElement('style');
        style.textContent = `
            .restaurant-branding {
                --restaurant-primary: ${primaryColor};
            }
            .btn-primary {
                background: ${primaryColor} !important;
                border-color: ${primaryColor} !important;
            }
            .badge-primary {
                background: ${primaryColor} !important;
            }
        `;
        document.head.appendChild(style);
    }

    getRestaurantId() {
        return this.currentRestaurant;
    }

    getRestaurantData() {
        return this.currentRestaurantData;
    }
}

// Auto-initialize
let restaurantManager;
if (typeof window !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        restaurantManager = new RestaurantManager();
    });
}
