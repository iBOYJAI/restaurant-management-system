<?php
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/includes/auth.php';
requireAuth();
requireAnyRole(['chef', 'manager', 'admin']);
$user = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chef Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/print.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #1a1a1a;
            color: white;
        }

        .header {
            background: #000;
            padding: var(--space-lg);
            text-align: center;
            border-bottom: 4px solid var(--primary);
        }

        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            color: var(--primary);
        }

        .kitchen-board {
            padding: var(--space-lg);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--space-lg);
        }

        .order-column {
            background: #2a2a2a;
            border-radius: var(--radius-lg);
            padding: var(--space-md);
        }

        .column-header {
            font-size: 1.5rem;
            font-weight: 700;
            padding: var(--space-md);
            text-align: center;
            border-radius: var(--radius-md);
            margin-bottom: var(--space-md);
        }

        .col-placed {
            background: #ff5722;
        }

        .col-preparing {
            background: #ff9800;
        }

        .col-ready {
            background: #4caf50;
        }

        .kitchen-order {
            background: #fff;
            color: #000;
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-md);
            box-shadow: var(--shadow-lg);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-sm);
            padding-bottom: var(--space-sm);
            border-bottom: 2px solid #eee;
        }

        .order-number {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .table-badge {
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-full);
            font-weight: 600;
        }

        .order-items {
            margin: var(--space-sm) 0;
        }

        .order-item {
            padding: var(--space-xs) 0;
            border-bottom: 1px dashed #ddd;
        }

        .item-qty {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            min-width: 30px;
            text-align: center;
            font-weight: 700;
        }

        .item-notes {
            background: #fffde7;
            padding: var(--space-xs);
            margin-top: var(--space-xs);
            border-left: 3px solid #ffd600;
            font-style: italic;
        }

        .order-actions {
            display: flex;
            gap: var(--space-xs);
            margin-top: var(--space-md);
        }

        .time-badge {
            font-size: 0.75rem;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>👨‍🍳 KITCHEN</h1>
        <div style="margin-top: var(--space-sm); font-size: 1rem;">Chef: <?= htmlspecialchars($user['full_name']) ?></div>
    </div>

    <div class="kitchen-board">
        <div class="order-column">
            <div class="column-header col-placed">
                🆕 NEW ORDERS (<span id="placedCount">0</span>)
            </div>
            <div id="placedOrders"></div>
        </div>

        <div class="order-column">
            <div class="column-header col-preparing">
                👨‍🍳 PREPARING (<span id="preparingCount">0</span>)
            </div>
            <div id="preparingOrders"></div>
        </div>

        <div class="order-column">
            <div class="column-header col-ready">
                ✓ READY (<span id="readyCount">0</span>)
            </div>
            <div id="readyOrders"></div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/notifications.js"></script>
    <script src="../assets/js/print.js"></script>
    <script>
        async function loadOrders() {
            const response = await apiRequest('/backend/api/orders.php?status=placed,preparing,ready');

            if (response.success) {
                displayOrders(response.data);
            }
        }

        function displayOrders(orders) {
            const columns = {
                placed: document.getElementById('placedOrders'),
                preparing: document.getElementById('preparingOrders'),
                ready: document.getElementById('readyOrders')
            };

            // Clear columns
            Object.values(columns).forEach(col => col.innerHTML = '');

            // Group by status
            const grouped = {
                placed: [],
                preparing: [],
                ready: []
            };
            orders.forEach(order => {
                if (grouped[order.status]) {
                    grouped[order.status].push(order);
                }
            });

            // Update counts
            document.getElementById('placedCount').textContent = grouped.placed.length;
            document.getElementById('preparingCount').textContent = grouped.preparing.length;
            document.getElementById('readyCount').textContent = grouped.ready.length;

            // Render orders
            Object.keys(grouped).forEach(status => {
                if (grouped[status].length === 0) {
                    columns[status].innerHTML = '<div style="text-align: center; padding: var(--space-lg); color: #666;">No orders</div>';
                } else {
                    columns[status].innerHTML = grouped[status].map(order => renderOrder(order)).join('');
                }
            });
        }

        function renderOrder(order) {
            const timeAgo = getTimeAgo(order.created_at);

            return `
                <div class="kitchen-order">
                    <div class="order-header">
                        <div class="order-number">#${order.order_number}</div>
                        <div class="table-badge">TABLE ${order.table_number}</div>
                    </div>
                    <div class="time-badge">⏱️ ${timeAgo}</div>
                    <div class="order-items">
                        ${order.items.map(item => `
                            <div class="order-item">
                                <span class="item-qty">${item.quantity}x</span>
                                <strong>${item.menu_item_name}</strong>
                                ${item.item_notes ? `<div class="item-notes">⚠️ ${item.item_notes}</div>` : ''}
                            </div>
                        `).join('')}
                    </div>
                    ${order.special_notes ? `<div class="item-notes" style="margin-top: var(--space-sm);"><strong>Special Notes:</strong><br>${order.special_notes}</div>` : ''}
                    <div class="order-actions">
                        ${order.status === 'placed' ? `
                            <button class="btn btn-warning w-full" onclick="updateStatus(${order.id}, 'preparing')">Start Preparing</button>
                        ` : ''}
                        ${order.status === 'preparing' ? `
                            <button class="btn btn-success w-full" onclick="updateStatus(${order.id}, 'ready')">Mark Ready</button>
                        ` : ''}
                        <button class="btn btn-outline w-full" onclick="printKitchenReceipt(${JSON.stringify(order).replace(/"/g, '&quot;')})">🖨️ Print</button>
                    </div>
                </div>
            `;
        }

        function getTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);

            if (seconds < 60) return `${seconds}s ago`;
            if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
            return `${Math.floor(seconds / 3600)}h ago`;
        }

        async function updateStatus(orderId, newStatus) {
            const response = await apiRequest('/backend/api/orders.php', 'PUT', {
                id: orderId,
                status: newStatus
            });

            if (response.success) {
                showToast(`Order updated to ${newStatus}`, 'success');
                loadOrders();
            }
        }

        // Auto-refresh every 5 seconds
        setInterval(loadOrders, 5000);

        document.addEventListener('DOMContentLoaded', loadOrders);
    </script>
</body>

</html>