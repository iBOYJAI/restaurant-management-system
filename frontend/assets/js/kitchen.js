/**
 * Kitchen Dashboard JavaScript
 * Real-time order management with auto-refresh
 */

let refreshInterval;
let isDragging = false;
let draggedOrderId = null;

// ============================================
// Fetch and Display Orders
// ============================================
async function fetchOrders() {
    try {
        const response = await apiRequest('/backend/api/orders.php');

        if (response.success) {
            displayOrders(response.data);
        } else {
            console.error('Error fetching orders:', response);
        }
    } catch (error) {
        console.error('Network Error:', error);
    }
}

function displayOrders(orders) {
    // If dragging, don't update DOM to prevent conflicts
    if (isDragging) return;

    // Filter for Today's Orders only
    const today = new Date().toISOString().split('T')[0];
    let filteredOrders = orders.filter(order => order.created_at.startsWith(today));

    // Clear all columns
    ['placed', 'preparing', 'ready', 'served'].forEach(status => {
        const column = document.getElementById(`${status}-orders`);
        if (column) {
            column.innerHTML = '';
        }
    });

    // Group orders by status
    const counts = { placed: 0, preparing: 0, ready: 0, served: 0 };

    // Sort all orders by time (newest first for Completed, typically)
    // For Kanban, we usually want Newest at the bottom for queues, 
    // but for Completed, we want Newest at the top.
    filteredOrders.forEach(order => {
        if (order.status !== 'cancelled') {
            const column = document.getElementById(`${order.status}-orders`);
            if (column) {
                // For 'served' (Completed), we apply a limit of 20
                if (order.status === 'served') {
                    if (counts.served < 20) {
                        column.appendChild(createOrderCard(order));
                        counts.served++;
                    }
                } else {
                    column.appendChild(createOrderCard(order));
                    if (counts.hasOwnProperty(order.status)) {
                        counts[order.status]++;
                    }
                }
            }
        }
    });

    // Update counts (Note: badge count might differ from visible count if limited, 
    // but the task asks to implement a limit for the column, so we show the limited count)
    Object.keys(counts).forEach(status => {
        const badge = document.getElementById(`${status}-count`);
        if (badge) badge.textContent = counts[status];
    });
}

function createOrderCard(order) {
    const card = document.createElement('div');
    card.className = `kds-card`;
    card.dataset.orderId = order.id;
    card.draggable = true;

    // Items list
    const itemsList = order.items.map(item => `
        <div class="order-item-row">
            <span class="item-qty">${item.quantity}x</span>
            <span class="item-name">
                ${item.menu_item_name}
                ${item.item_notes ? `<span class="item-notes">Note: ${item.item_notes}</span>` : ''}
            </span>
        </div>
    `).join('');

    // Special notes
    const specialNotes = order.special_notes
        ? `<div class="special-note"><strong>Note:</strong> ${order.special_notes}</div>`
        : '';

    // Action Button
    const buttonHtml = getStatusButton(order);

    card.innerHTML = `
        <div class="card-header">
            <div>
                <div class="order-id">#${order.order_number}</div>
                <div class="order-meta">Table ${order.table_number}</div>
            </div>
            <div class="order-timer">${formatTimeAgo(order.created_at)}</div>
        </div>
        <div class="card-body">
            ${itemsList}
            ${specialNotes}
        </div>
        <div class="card-footer">
            ${buttonHtml}
        </div>
    `;

    // Add drag event listeners
    card.addEventListener('dragstart', handleDragStart);
    card.addEventListener('dragend', handleDragEnd);

    return card;
}

function getStatusButton(order) {
    const statusFlow = {
        'placed': { next: 'preparing', label: 'Start Preparing', class: 'btn-preparing' },
        'preparing': { next: 'ready', label: 'Mark Ready', class: 'btn-ready' },
        'ready': { next: 'served', label: 'Mark Served', class: 'btn-served' },
        'served': null
    };

    const current = statusFlow[order.status];
    if (!current) return '<span style="color:#666; font-size:0.8rem;">Completed</span>';

    return `
        <button class="kds-btn ${current.class}" onclick="updateOrderStatus(${order.id}, '${current.next}')">
            ${current.label}
        </button>
    `;
}

// ============================================
// Update Order Status
// ============================================
async function updateOrderStatus(orderId, newStatus) {
    try {
        const response = await apiRequest('/backend/api/orders.php', 'PUT', {
            order_id: orderId,
            status: newStatus
        });

        if (response.success) {
            // Optimistic update or wait for fetch
            fetchOrders();
        } else {
            showToast(response.message || 'Error updating order', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error updating order status', 'error');
    }
}

// ============================================
// Drag and Drop
// ============================================
function handleDragStart(e) {
    isDragging = true;
    draggedOrderId = e.target.closest('.kds-card').dataset.orderId;
    e.target.closest('.kds-card').style.opacity = '0.5';
    e.dataTransfer.effectAllowed = 'move';
}

function handleDragEnd(e) {
    isDragging = false;
    e.target.closest('.kds-card').style.opacity = '1';
    draggedOrderId = null;
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    return false;
}

function handleDrop(e, newStatus) {
    e.stopPropagation();
    e.preventDefault();

    if (draggedOrderId) {
        updateOrderStatus(parseInt(draggedOrderId), newStatus);
    }
    return false;
}

// ============================================
// Helpers & Init
// ============================================
function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // seconds

    if (diff < 60) return `${diff}s`;
    if (diff < 3600) return `${Math.floor(diff / 60)}m`;
    return `${Math.floor(diff / 3600)}h`;
}

document.addEventListener('DOMContentLoaded', () => {
    // Set up drop zones
    ['placed', 'preparing', 'ready', 'served'].forEach(status => {
        const column = document.getElementById(`col-${status}`); // The column container
        // Note: We need to drop ON the column, but the items are inside #-orders.
        // Let's attach to the column itself for easier drop area.
        if (column) {
            column.addEventListener('dragover', handleDragOver);
            column.addEventListener('drop', (e) => handleDrop(e, status));
        }
    });

    startAutoRefresh();

    document.addEventListener('visibilitychange', () => {
        document.hidden ? stopAutoRefresh() : startAutoRefresh();
    });
});

function startAutoRefresh() {
    fetchOrders();
    refreshInterval = setInterval(fetchOrders, 5000);
}

function stopAutoRefresh() {
    clearInterval(refreshInterval);
}
