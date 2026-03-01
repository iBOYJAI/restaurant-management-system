<?php

/**
 * Admin Dashboard - Restored with Role Safety
 */
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/includes/auth.php';

// Allow Managers, Admins, and Super Admins
requireAnyRole(['super_admin', 'admin', 'manager']);
$admin = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Restaurant</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700&display=swap" rel="stylesheet">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--space-md);
            margin-bottom: var(--space-lg);
        }

        .stat-card {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: var(--space-md);
            box-shadow: none;
        }

        .stat-card h4 {
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: var(--space-xs);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .quick-actions {
            display: flex;
            gap: var(--space-sm);
            margin-bottom: var(--space-lg);
            flex-wrap: wrap;
        }

        .card {
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: none;
            background: white;
        }

        .card-header {
            background: var(--bg-secondary);
            padding: var(--space-md);
            border-bottom: 1px solid var(--border);
        }

        .card-header h3 {
            margin: 0;
            font-size: 1.125rem;
        }

        .status-badges {
            display: flex;
            gap: var(--space-sm);
            flex-wrap: wrap;
            margin-bottom: var(--space-md);
        }
        .status-badge-item {
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 600;
        }
        .status-placed { background: #e0f2fe; color: #0369a1; }
        .status-preparing { background: #fef3c7; color: #92400e; }
        .status-ready { background: #d1fae5; color: #065f46; }
        .status-served { background: #f3f4f6; color: #374151; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
    </style>
</head>

<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <?php require_once 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="d-flex justify-between align-center mb-4">
                <div>
                    <h1>Dashboard</h1>
                    <p style="color: var(--text-secondary);">Welcome back, <?= htmlspecialchars($admin['full_name']) ?>!</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="../kitchen/dashboard.php" class="btn btn-outline btn-sm">Kitchen View</a>
                    <a href="../index.php" class="btn btn-outline btn-sm">Customer Menu</a>
                </div>
            </div>

            <!-- Statistics (Managers & Admins see these) -->
            <div class="stats-grid" id="statsGrid">
                <div class="stat-card">
                    <h4>Total Orders</h4>
                    <div class="stat-value" id="totalOrders">...</div>
                </div>
                <div class="stat-card">
                    <h4>Total Revenue</h4>
                    <div class="stat-value" id="totalRevenue">...</div>
                </div>
                <div class="stat-card">
                    <h4>Today's Orders</h4>
                    <div class="stat-value" id="todayOrders">...</div>
                </div>
            </div>

            <!-- Order status breakdown -->
            <div class="status-badges" id="statusBreakdown" style="display: none;">
                <span class="status-badge-item status-placed">Placed: <strong id="countPlaced">0</strong></span>
                <span class="status-badge-item status-preparing">Preparing: <strong id="countPreparing">0</strong></span>
                <span class="status-badge-item status-ready">Ready: <strong id="countReady">0</strong></span>
                <span class="status-badge-item status-served">Served: <strong id="countServed">0</strong></span>
                <span class="status-badge-item status-cancelled">Cancelled: <strong id="countCancelled">0</strong></span>
            </div>

            <!-- Quick Actions: Roles restricted here too -->
            <div class="quick-actions">
                <?php if (hasAnyRole(['admin', 'super_admin'])): ?>
                    <a href="menu-management.php" class="btn btn-primary">Add Menu Item</a>
                    <a href="category-management.php" class="btn btn-outline">Manage Categories</a>
                <?php endif; ?>

                <a href="order-history.php" class="btn btn-outline">View Order History</a>

                <?php if (hasAnyRole(['admin', 'super_admin', 'manager'])): ?>
                    <a href="feedback-dashboard.php" class="btn btn-outline">Customer Feedback</a>
                <?php endif; ?>
            </div>

            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header">
                    <h3>Recent Activity</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #fafafa; border-bottom: 2px solid var(--border); text-align: left; font-size: 0.85rem; text-transform: uppercase; color: var(--text-secondary);">
                                <th style="padding: 1rem var(--space-md);">Order #</th>
                                <th style="padding: 1rem var(--space-md);">Table</th>
                                <th style="padding: 1rem var(--space-md);">Status</th>
                                <th style="padding: 1rem var(--space-md);">Total</th>
                                <th style="padding: 1rem var(--space-md);">Time</th>
                            </tr>
                        </thead>
                        <tbody id="recentOrders">
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-light);">
                                    Syncing orders...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        async function loadDashboardData() {
            try {
                const response = await apiRequest('/backend/api/admin/statistics.php');
                if (response.success) {
                    const data = response.data;
                    document.getElementById('totalOrders').textContent = data.total_orders ?? 0;
                    document.getElementById('totalRevenue').textContent = formatCurrency(data.total_revenue ?? 0);
                    document.getElementById('todayOrders').textContent = data.today_orders ?? (data.recent_orders ? data.recent_orders.length : 0);

                    const byStatus = data.by_status || {};
                    document.getElementById('countPlaced').textContent = byStatus.placed || 0;
                    document.getElementById('countPreparing').textContent = byStatus.preparing || 0;
                    document.getElementById('countReady').textContent = byStatus.ready || 0;
                    document.getElementById('countServed').textContent = byStatus.served || 0;
                    document.getElementById('countCancelled').textContent = byStatus.cancelled || 0;
                    document.getElementById('statusBreakdown').style.display = 'flex';

                    displayRecentOrders(data.recent_orders || []);
                }
            } catch (error) {
                console.error('Dashboard Load Error:', error);
            }
        }

        function displayRecentOrders(orders) {
            const tbody = document.getElementById('recentOrders');
            if (!orders || orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem;">No recent orders.</td></tr>';
                return;
            }

            tbody.innerHTML = orders.slice(0, 10).map(order => `
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 1rem var(--space-md); font-family: monospace; font-weight: 700;">#${order.order_number}</td>
                    <td style="padding: 1rem var(--space-md);">Table ${order.table_number}</td>
                    <td style="padding: 1rem var(--space-md);"><span class="status-badge-item status-${order.status}">${order.status}</span></td>
                    <td style="padding: 1rem var(--space-md); font-weight: 700;">${formatCurrency(order.total_amount)}</td>
                    <td style="padding: 1rem var(--space-md); color: var(--text-secondary); font-size: 0.85rem;">${formatDate(order.created_at)}</td>
                </tr>
            `).join('');
        }

        document.addEventListener('DOMContentLoaded', loadDashboardData);
    </script>
</body>

</html>