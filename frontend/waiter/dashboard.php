<?php
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/includes/auth.php';
requireAuth();
requireRole(['waiter', 'manager', 'admin']);
$user = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiter Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: var(--bg-secondary);
        }

        .header {
            background: var(--gradient-dark);
            color: white;
            padding: var(--space-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-lg);
        }

        .header h1 {
            margin: 0;
            font-size: 1.5rem;
        }

        .content {
            padding: var(--space-md);
            max-width: 1400px;
            margin: 0 auto;
        }

        .table-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: var(--space-md);
            margin-bottom: var(--space-lg);
        }

        .table-card {
            background: white;
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            text-align: center;
            cursor: pointer;
            transition: all var(--transition-fast);
            border: 3px solid transparent;
        }

        .table-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }

        .table-card.occupied {
            border-color: var(--warning);
            background: #fff8e1;
        }

        .table-card.active {
            border-color: var(--primary);
        }

        .table-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .table-status {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: var(--space-xs);
        }

        .orders-section {
            background: white;
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }

        .order-item {
            padding: var(--space-md);
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .quick-actions {
            display: flex;
            gap: var(--space-sm);
            margin-bottom: var(--space-lg);
            flex-wrap: wrap;
        }
    </style>
</head>

<body>
    <div class="header">
        <div>
            <h1>🍽️ Waiter Dashboard</h1>
            <small>Welcome, <?= htmlspecialchars($user['full_name']) ?></small>
        </div>
        <div>
            <button class="btn btn-outline" style="color: white; border-color: white;" onclick="window.location.reload()">🔄 Refresh</button>
            <button class="btn btn-outline" style="color: white; border-color: white;" onclick="window.location.href='../admin/logout.php'">Logout</button>
        </div>
    </div>

    <div class="content">
        <div class="quick-actions">
            <button class="btn btn-primary" onclick="takeNewOrder()">➕ New Order</button>
            <button class="btn btn-outline" onclick="viewAllOrders()">📋 All Orders</button>
            <button class="btn btn-outline" onclick="viewKitchen()">👨‍🍳 Kitchen Status</button>
        </div>

        <h2>Table Overview</h2>
        <div class="table-grid" id="tableGrid"></div>

        <div class="orders-section">
            <h3>Active Orders</h3>
            <div id="activeOrders">
                <div style="text-align: center; padding: var(--space-lg); color: var(--text-secondary);">
                    Loading orders...
                </div>
            </div>
        </div>
    </div>

    <div id="tableModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Table Details</h3>
                <button class="close-modal" onclick="closeModal('tableModal')">×</button>
            </div>
            <div class="modal-body" id="tableDetails"></div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/notifications.js"></script>
    <script>
        let tables = {};
        let orders = [];

        async function loadDashboard() {
            await Promise.all([loadTables(), loadOrders()]);
            renderTables();
            renderOrders();
        }

        async function loadTables() {
            // Get unique tables from orders
            const response = await apiRequest('/backend/api/orders.php');
            if (response.success) {
                const tableNums = [...new Set(response.data.map(o => o.table_number))];
                tables = {};
                for (let i = 1; i <= 20; i++) {
                    tables[i] = {
                        number: i,
                        occupied: tableNums.includes(i.toString()),
                        activeOrders: response.data.filter(o => o.table_number == i && ['placed', 'preparing', 'ready'].includes(o.status)).length
                    };
                }
            }
        }

        async function loadOrders() {
            const response = await apiRequest('/backend/api/orders.php?status=placed,preparing,ready');
            if (response.success) {
                orders = response.data;
            }
        }

        function renderTables() {
            const grid = document.getElementById('tableGrid');
            grid.innerHTML = Object.values(tables).map(table => `
                <div class="table-card ${table.occupied ? 'occupied' : ''}" onclick="viewTable(${table.number})">
                    <div class="table-number">${table.number}</div>
                    <div class="table-status">
                        ${table.activeOrders > 0 ? `${table.activeOrders} active order(s)` : 'Available'}
                    </div>
                </div>
            `).join('');
        }

        function renderOrders() {
            const container = document.getElementById('activeOrders');
            if (orders.length === 0) {
                container.innerHTML = '<div style="text-align: center; padding: var(--space-lg); color: var(--text-secondary);">No active orders</div>';
                return;
            }

            container.innerHTML = orders.map(order => `
                <div class="order-item">
                    <div>
                        <strong>#${order.order_number}</strong> - Table ${order.table_number}<br>
                        <small style="color: var(--text-secondary);">${order.items.length} items • ${formatCurrency(order.total_amount)}</small>
                    </div>
                    <div>
                        <span class="badge status-${order.status}">${order.status}</span>
                        ${order.status === 'ready' ? `<button class="btn btn-sm btn-success ml-2" onclick="markServed(${order.id})">Served</button>` : ''}
                    </div>
                </div>
            `).join('');
        }

        function viewTable(tableNum) {
            const tableOrders = orders.filter(o => o.table_number == tableNum);

            document.getElementById('modalTitle').textContent = `Table ${tableNum}`;

            if (tableOrders.length === 0) {
                document.getElementById('tableDetails').innerHTML = `
                    <p>No active orders for this table.</p>
                    <button class="btn btn-primary" onclick="takeNewOrder(${tableNum})">Take Order</button>
                `;
            } else {
                document.getElementById('tableDetails').innerHTML = `
                    <h4>Active Orders:</h4>
                    ${tableOrders.map(order => `
                        <div style="padding: var(--space-md); background: var(--bg-secondary); border-radius: var(--radius-md); margin-bottom: var(--space-sm);">
                            <div><strong>Order #${order.order_number}</strong></div>
                            <div>Status: <span class="badge status-${order.status}">${order.status}</span></div>
                            <div>Total: ${formatCurrency(order.total_amount)}</div>
                            <div style="margin-top: var(--space-sm);">
                                ${order.status === 'ready' ? `<button class="btn btn-sm btn-success" onclick="markServed(${order.id})">Mark as Served</button>` : ''}
                            </div>
                        </div>
                    `).join('')}
                    <button class="btn btn-outline mt-3" onclick="takeNewOrder(${tableNum})">Add Another Order</button>
                `;
            }

            openModal('tableModal');
        }

        async function markServed(orderId) {
            const response = await apiRequest('/backend/api/orders.php', 'PUT', {
                id: orderId,
                status: 'served'
            });

            if (response.success) {
                showToast('Order marked as served!', 'success');
                closeModal('tableModal');
                loadDashboard();
            }
        }

        function takeNewOrder(tableNum = null) {
            const table = tableNum || prompt('Enter table number:');
            if (table) {
                window.location.href = `../index.php?table=${table}`;
            }
        }

        function viewAllOrders() {
            window.location.href = '../admin/order-history.php';
        }

        function viewKitchen() {
            window.location.href = '../kitchen/dashboard.php';
        }

        // Auto-refresh every 10 seconds
        setInterval(loadDashboard, 10000);

        document.addEventListener('DOMContentLoaded', loadDashboard);
    </script>
</body>

</html>