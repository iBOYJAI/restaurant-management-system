<?php

/**
 * Order History - Redesigned with 'Vibre' Aesthetic
 */
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/includes/auth.php';

requireAnyRole(['super_admin', 'admin', 'chef', 'manager']);
$admin = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Vibre Premium</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700&display=swap" rel="stylesheet">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
            background: #fdfdfd;
        }

        .header-section {
            margin-bottom: var(--space-xl);
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .filter-vibre {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            border: 1px solid #f0f0f0;
            display: flex;
            gap: 1.5rem;
            align-items: center;
            margin-bottom: var(--space-lg);
        }

        .premium-table-card {
            background: white;
            border-radius: 24px;
            border: 1px solid #f0f0f0;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
        }

        .v-table {
            width: 100%;
            border-collapse: collapse;
        }

        .v-table th {
            text-align: left;
            padding: 1.25rem 1.5rem;
            background: #fcfcfc;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 800;
            color: #999;
            letter-spacing: 1px;
            border-bottom: 2px solid #f8f8f8;
        }

        .v-table td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f8f8f8;
            vertical-align: middle;
        }

        .v-table tr:hover {
            background: #fafafa;
        }

        .o-number {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: #1a1a1a;
            font-size: 1rem;
        }

        .o-price {
            font-weight: 700;
            color: var(--primary);
        }

        .o-date {
            font-size: 0.8rem;
            color: #888;
        }

        .o-table-num {
            background: #f0f0f0;
            color: #555;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        /* Status Badges with Gradients */
        .v-badge {
            padding: 6px 14px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: capitalize;
            border: none;
            display: inline-block;
        }

        .st-placed {
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            color: #0369a1;
        }

        .st-preparing {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .st-ready {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .st-served {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            color: #374151;
        }

        .st-cancelled {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        .btn-view {
            background: #1a1a1a;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-view:hover {
            background: #333;
            transform: scale(1.05);
        }

        /* Modal Styles */
        .order-modal-header {
            padding: 2rem;
            background: #fafafa;
            border-radius: 24px 24px 0 0;
        }

        .order-modal-body {
            padding: 2rem;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="header-section header-flex">
                <div>
                    <h1 style="font-family: 'Outfit', sans-serif;">Order History</h1>
                    <p style="color: var(--text-secondary); margin-top: 4px;">Archive of every culinary moment.</p>
                </div>
                <div class="actions">
                    <button class="btn btn-outline" onclick="loadOrders()" style="border-radius: 12px;">🔄 Refresh List</button>
                </div>
            </div>

            <div class="filter-vibre">
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 0.7rem; font-weight: 800; color: #bbb; text-transform: uppercase;">By Day</label>
                    <select id="periodFilter" class="form-control" onchange="loadOrders()" style="border-radius: 12px; border: 1px solid #eee; width: 180px; font-weight: 600;">
                        <option value="">All time</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="last_week">Last 7 days</option>
                    </select>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 0.7rem; font-weight: 800; color: #bbb; text-transform: uppercase;">By Status</label>
                    <select id="statusFilter" class="form-control" onchange="loadOrders()" style="border-radius: 12px; border: 1px solid #eee; width: 220px; font-weight: 600;">
                        <option value="">All Transactions</option>
                        <option value="placed">Newly Placed</option>
                        <option value="preparing">In Kitchen</option>
                        <option value="ready">Ready for Pickup</option>
                        <option value="served">Successfully Served</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <div class="premium-table-card">
                <table class="v-table">
                    <thead>
                        <tr>
                            <th>Transaction</th>
                            <th>Table</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date & Time</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody id="orderList">
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 4rem; color: #ccc;">Syncing history...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content" style="max-width: 600px; border-radius: 24px; border: none; padding: 0;">
            <div id="modalContent">
                <!-- Injected via JS -->
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        async function loadOrders() {
            const status = document.getElementById('statusFilter').value;
            const period = document.getElementById('periodFilter').value;
            let url = '/backend/api/orders.php';
            const q = [];
            if (status) q.push('status=' + encodeURIComponent(status));
            if (period) q.push('period=' + encodeURIComponent(period));
            if (q.length) url += '?' + q.join('&');
            const response = await apiRequest(url);
            if (response.success) displayOrders(response.data);
        }

        function displayOrders(orders) {
            const tbody = document.getElementById('orderList');
            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 5rem;">No transactions found in this view.</td></tr>';
                return;
            }

            tbody.innerHTML = orders.map(order => `
                <tr>
                    <td><span class="o-number">#${order.order_number}</span></td>
                    <td><span class="o-table-num">T-${order.table_number}</span></td>
                    <td><span class="o-price">${formatCurrency(order.total_amount)}</span></td>
                    <td><span class="v-badge st-${order.status}">${order.status}</span></td>
                    <td><span class="o-date">${formatDate(order.created_at)}</span></td>
                    <td><button class="btn-view" onclick='viewOrder(${JSON.stringify(order)})'>Details</button></td>
                </tr>
            `).join('');
        }

        function viewOrder(order) {
            const subtotal = order.total_amount - (order.tax_amount || 0);
            const itemsList = order.items.map(item => `
                <div class="item-row">
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <span style="background: #f0f0f0; padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 0.8rem;">${item.quantity}x</span>
                        <div>
                            <div style="font-weight: 600;">${item.menu_item_name}</div>
                            ${item.item_notes ? `<small style="color: #f56c6c; display: block; font-style: italic;">Note: ${item.item_notes}</small>` : ''}
                        </div>
                    </div>
                    <div style="font-weight: 700;">${formatCurrency(item.price * item.quantity)}</div>
                </div>
            `).join('');

            document.getElementById('modalContent').innerHTML = `
                <div class="order-modal-header">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <h2 style="font-family: 'Outfit', sans-serif;">Receipt #${order.order_number}</h2>
                            <p style="color: #888; margin-top: 4px;">Table ${order.table_number} • ${formatDate(order.created_at)}</p>
                        </div>
                        <button onclick="closeModal('orderModal')" style="background:none; border:none; font-size: 1.5rem; cursor:pointer; color:#bbb;">×</button>
                    </div>
                </div>
                <div class="order-modal-body">
                    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
                        <span class="v-badge st-${order.status}" style="font-size: 0.8rem; padding: 8px 16px;">${order.status.toUpperCase()}</span>
                        <div style="text-align: right;">
                            <small style="color: #bbb; display: block; font-size: 0.65rem; text-transform: uppercase;">Payment Type</small>
                            <span style="font-weight: 700; font-size: 0.9rem;">Counter / Cash</span>
                        </div>
                    </div>

                    <div style="margin-bottom: 2rem;">
                        <h4 style="text-transform: uppercase; font-size: 0.7rem; color: #999; letter-spacing: 1px; margin-bottom: 1rem; border-bottom: 1px solid #f0f0f0; padding-bottom: 8px;">Order Items</h4>
                        ${itemsList}
                    </div>

                    ${order.special_notes ? `
                    <div style="margin-bottom: 2rem; background: #fffcf0; padding: 1rem; border-radius: 12px; border: 1px solid #ffecb3;">
                        <h4 style="text-transform: uppercase; font-size: 0.7rem; color: #b78a00; letter-spacing: 1px; margin-bottom: 0.5rem;">Chef's Instructions</h4>
                        <p style="font-size: 0.9rem; color: #664d00; font-style: italic;">"${order.special_notes}"</p>
                    </div>
                    ` : ''}

                    <div style="background: #fafafa; padding: 1.5rem; border-radius: 16px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: #666;">
                            <span>Subtotal</span>
                            <span>${formatCurrency(subtotal)}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px; color: #666;">
                            <span>Tax (GST 5%)</span>
                            <span>${formatCurrency(order.tax_amount || 0)}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 12px;">
                            <span style="font-weight: 800; font-size: 1.1rem; color: #1a1a1a;">Grand Total</span>
                            <span style="font-weight: 800; font-size: 1.4rem; color: var(--primary);">${formatCurrency(order.total_amount)}</span>
                        </div>
                    </div>
                </div>
            `;
            openModal('orderModal');
        }

        document.addEventListener('DOMContentLoaded', loadOrders);
    </script>
</body>

</html>