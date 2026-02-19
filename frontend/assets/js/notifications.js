/**
 * Notification System - Offline Implementation
 * Uses polling (no external WebSocket services)
 */

class NotificationManager {
    constructor(options = {}) {
        this.pollInterval = options.pollInterval || 3000; // 3 seconds
        this.audioEnabled = options.audioEnabled !== false;
        this.browserNotifications = options.browserNotifications !== false;
        this.pollTimer = null;
        this.lastNotificationId = 0;
        this.unreadCount = 0;

        this.init();
    }

    init() {
        // Request browser notification permission
        if (this.browserNotifications && 'Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Create audio element for notification sound
        if (this.audioEnabled) {
            this.notificationSound = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZSA0PVa3m7q9WEnd');
        }

        // Start polling
        this.startPolling();

        // Create notification UI
        this.createNotificationUI();
    }

    createNotificationUI() {
        // Create bell icon and dropdown if it doesn't exist
        const existingBell = document.querySelector('.notification-bell');
        if (existingBell) return;

        const navbar = document.querySelector('.navbar-nav') || document.querySelector('.navbar-container');
        if (!navbar) return;

        const notificationHTML = `
            <div class="notification-container" style="position: relative;">
                <button class="notification-bell" onclick="notificationManager.toggleDropdown()" style="position: relative; background: none; border: none; cursor: pointer; font-size: 1.5rem; padding: 0.5rem;">
                    🔔
                    <span class="notification-badge" style="display: none; position: absolute; top: 0; right: 0; background: var(--danger); color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 0.75rem; display: flex; align-items: center; justify-content: center;">0</span>
                </button>
                <div class="notification-dropdown" style="display: none; position: absolute; right: 0; top: 100%; width: 350px; max-height: 400px; overflow-y: auto; background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-xl); z-index: 1000; margin-top: 0.5rem;">
                    <div class="notification-header" style="padding: var(--space-md); border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="margin: 0;">Notifications</h4>
                        <button onclick="notificationManager.markAllRead()" class="btn btn-sm btn-outline">Mark all read</button>
                    </div>
                    <div class="notification-list" id="notificationList">
                        <div style="padding: var(--space-lg); text-align: center; color: var(--text-secondary);">
                            No notifications
                        </div>
                    </div>
                </div>
            </div>
        `;

        navbar.insertAdjacentHTML('beforeend', notificationHTML);

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const container = document.querySelector('.notification-container');
            if (container && !container.contains(e.target)) {
                this.closeDropdown();
            }
        });
    }

    async startPolling() {
        await this.fetchNotifications();
        this.pollTimer = setInterval(() => this.fetchNotifications(), this.pollInterval);
    }

    stopPolling() {
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
        }
    }

    async fetchNotifications() {
        try {
            const response = await apiRequest('/backend/api/notifications.php');

            if (response.success) {
                this.updateNotifications(response.data.notifications);
                this.updateBadge(response.data.unread_count);

                // Check for new notifications
                this.checkForNewNotifications(response.data.notifications);
            }
        } catch (error) {
            console.error('Error fetching notifications:', error);
        }
    }

    updateNotifications(notifications) {
        const listContainer = document.getElementById('notificationList');
        if (!listContainer) return;

        if (notifications.length === 0) {
            listContainer.innerHTML = `
                <div style="padding: var(--space-lg); text-align: center; color: var(--text-secondary);">
                    No notifications
                </div>
            `;
            return;
        }

        listContainer.innerHTML = notifications.map(notif => this.renderNotification(notif)).join('');
    }

    renderNotification(notif) {
        const isUnread = notif.is_read == 0;
        const icon = this.getNotificationIcon(notif.type);
        const time = this.formatTime(notif.created_at);

        return `
            <div class="notification-item ${isUnread ? 'unread' : ''}" 
                 style="padding: var(--space-md); border-bottom: 1px solid var(--border); ${isUnread ? 'background: var(--bg-secondary);' : ''} cursor: pointer;"
                 onclick="notificationManager.handleNotificationClick(${notif.id}, ${notif.related_order_id || 'null'})">
                <div style="display: flex; gap: var(--space-sm); align-items: start;">
                    <div style="font-size: 1.5rem;">${icon}</div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">${notif.title}</div>
                        <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.25rem;">${notif.message || ''}</div>
                        <div style="font-size: 0.75rem; color: var(--text-light);">${time}</div>
                    </div>
                    ${isUnread ? '<div style="width: 8px; height: 8px; background: var(--primary); border-radius: 50%;"></div>' : ''}
                </div>
            </div>
        `;
    }

    getNotificationIcon(type) {
        const icons = {
            'order_placed': '🛒',
            'order_updated': '✓',
            'feedback_received': '⭐',
            'system': 'ℹ️',
            'alert': '⚠️'
        };
        return icons[type] || '🔔';
    }

    formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);

        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
        if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;
        return date.toLocaleDateString();
    }

    updateBadge(count) {
        this.unreadCount = count;
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }
    }

    checkForNewNotifications(notifications) {
        if (notifications.length === 0) return;

        const latestId = notifications[0].id;

        // If this is a new notification (higher ID than last seen)
        if (latestId > this.lastNotificationId && this.lastNotificationId > 0) {
            const newNotifs = notifications.filter(n => n.id > this.lastNotificationId);
            newNotifs.forEach(notif => {
                this.showNotification(notif);
                if (this.audioEnabled) {
                    this.playSound();
                }
            });
        }

        this.lastNotificationId = latestId;
    }

    showNotification(notif) {
        // Browser notification
        if (this.browserNotifications && 'Notification' in window && Notification.permission === 'granted') {
            new Notification(notif.title, {
                body: notif.message,
                icon: '/frontend/assets/images/notification-icon.png',
                tag: `notif-${notif.id}`
            });
        }

        // Toast notification
        showToast(notif.title + (notif.message ? ': ' + notif.message : ''), 'info', 5000);
    }

    playSound() {
        if (this.notificationSound) {
            this.notificationSound.play().catch(err => console.log('Audio play failed:', err));
        }
    }

    toggleDropdown() {
        const dropdown = document.querySelector('.notification-dropdown');
        if (dropdown) {
            const isVisible = dropdown.style.display === 'block';
            dropdown.style.display = isVisible ? 'none' : 'block';
        }
    }

    closeDropdown() {
        const dropdown = document.querySelector('.notification-dropdown');
        if (dropdown) {
            dropdown.style.display = 'none';
        }
    }

    async handleNotificationClick(notificationId, orderId) {
        // Mark as read
        await this.markAsRead(notificationId);

        // Navigate to related order if exists
        if (orderId) {
            window.location.href = `/frontend/admin/order-history.php?order_id=${orderId}`;
        }

        this.closeDropdown();
    }

    async markAsRead(notificationId) {
        try {
            await apiRequest('/backend/api/notifications.php', 'PUT', {
                notification_id: notificationId
            });
            this.fetchNotifications(); // Refresh list
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async markAllRead() {
        try {
            await apiRequest('/backend/api/notifications.php', 'PUT', {
                mark_all_read: true
            });
            this.fetchNotifications(); // Refresh list
            showToast('All notifications marked as read', 'success');
        } catch (error) {
            console.error('Error marking all as read:', error);
        }
    }
}

// Auto-initialize for authenticated pages
let notificationManager;
if (typeof isLoggedIn !== 'undefined' && isLoggedIn()) {
    document.addEventListener('DOMContentLoaded', () => {
        notificationManager = new NotificationManager({
            pollInterval: 3000, // Poll every 3 seconds
            audioEnabled: true,
            browserNotifications: true
        });
    });
}
