<?php
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/includes/auth.php';
requireAuth();
requireAnyRole(['manager', 'admin', 'super_admin']);
$user = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700&display=swap" rel="stylesheet">
    <style>
        .manager-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: var(--gradient-dark);
            color: white;
            padding: var(--space-md);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: var(--space-lg);
            padding-bottom: var(--space-md);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-nav li {
            margin-bottom: var(--space-xs);
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.8);
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: var(--space-lg);
            background: var(--bg-secondary);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--space-md);
            margin-bottom: var(--space-lg);
        }

        .stat-card {
            background: white;
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin: var(--space-sm) 0;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .chart-container {
            background: white;
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: var(--space-md);
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--space-md);
        }
    </style>
</head>

<body>
    <div class="manager-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">👔 Manager Panel</div>
            <ul class="sidebar-nav">
                <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="../admin/analytics.php">📈 Analytics</a></li>
                <li><a href="../admin/ order-history.php">📋 Orders</a></li>
                <li><a href="../admin/feedback-dashboard.php">⭐ Feedback</a></li>
                <li><a href="../admin/menu-management.php">🍔 Menu</a></li>
                <li><a href="../kitchen/dashboard.php">👨‍🍳 Kitchen View</a></li>
                <li><a href="../admin/logout.php">🚪 Logout</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <div class="d-flex justify-between align-center mb-4">
                <div>
                    <h1>Manager Dashboard</h1>
                    <p style="color: var(--text-secondary);">Welcome back, <?= htmlspecialchars($user['full_name']) ?></p>
                </div>
                <div>
                    <select id="dateRange" class="form-control" onchange="loadDashboard()">
                        <option value="today">Today</option>
                        <option value="week" selected>This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
            </div>

            <div class="stats-grid" id="statsGrid">
                <div class="stat-card">
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-value" id="totalOrders">-</div>
                    <div style="font-size: 0.875rem; color: var(--success);">↑ vs last period</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Revenue</div>
                    <div class="stat-value" id="totalRevenue">-</div>
                    <div style="font-size: 0.875rem; color: var(--success);">↑ vs last period</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Avg Order Value</div>
                    <div class="stat-value" id="avgOrder">-</div>
                    <div style="font-size: 0.875rem;">Per transaction</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Customer Satisfaction</div>
                    <div class="stat-value" id="satisfaction">-</div>
                    <div style="font-size: 0.875rem;">⭐ Average rating</div>
                </div>
            </div>

            <div class="chart-container">
                <h3>Sales Trend</h3>
                <canvas id="salesChart" height="80"></canvas>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                <div class="chart-container">
                    <h3>Popular Items</h3>
                    <canvas id="popularChart" height="120"></canvas>
                </div>
                <div class="chart-container">
                    <h3>Category Revenue</h3>
                    <canvas id="categoryChart" height="120"></canvas>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <button class="btn btn-primary" onclick="window.location.href='../admin/order-history.php'">View All Orders</button>
                        <button class="btn btn-outline" onclick="generateReport()">Generate Report</button>
                        <button class="btn btn-outline" onclick="window.location.href='../admin/feedback-dashboard.php'">View Feedback</button>
                        <button class="btn btn-outline" onclick="window.location.href='../kitchen/dashboard.php'">Kitchen Status</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/notifications.js"></script>
    <script src="../assets/js/chart.min.js"></script>
    <script>
        let salesChart, popularChart, categoryChart;

        async function loadDashboard() {
            const range = document.getElementById('dateRange').value;
            const dates = getDateRange(range);

            const response = await apiRequest(`/backend/api/admin/analytics.php?start_date=${dates.start}&end_date=${dates.end}`);

            if (response.success) {
                updateStats(response.data);
                updateCharts(response.data);
            }
        }

        function getDateRange(range) {
            const end = new Date();
            const start = new Date();

            switch (range) {
                case 'today':
                    start.setHours(0, 0, 0, 0);
                    break;
                case 'week':
                    start.setDate(start.getDate() - 7);
                    break;
                case 'month':
                    start.setMonth(start.getMonth() - 1);
                    break;
                case 'year':
                    start.setFullYear(start.getFullYear() - 1);
                    break;
            }

            return {
                start: start.toISOString().split('T')[0],
                end: end.toISOString().split('T')[0]
            };
        }

        function updateStats(data) {
            document.getElementById('totalOrders').textContent = data.key_metrics.total_orders || 0;
            document.getElementById('totalRevenue').textContent = formatCurrency(data.key_metrics.total_revenue || 0);
            document.getElementById('avgOrder').textContent = formatCurrency(data.key_metrics.avg_order_value || 0);
            document.getElementById('satisfaction').textContent = (data.satisfaction.avg_overall || 0).toFixed(1) + '/5';
        }

        function updateCharts(data) {
            // Sales Trend Chart
            if (salesChart) salesChart.destroy();
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            salesChart = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: data.sales_trends.map(d => d.date),
                    datasets: [{
                        label: 'Revenue',
                        data: data.sales_trends.map(d => d.revenue),
                        borderColor: '#FF6B35',
                        backgroundColor: 'rgba(255, 107, 53, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true
                }
            });

            // Popular Items Chart
            if (popularChart) popularChart.destroy();
            const popularCtx = document.getElementById('popularChart').getContext('2d');
            popularChart = new Chart(popularCtx, {
                type: 'bar',
                data: {
                    labels: data.popular_items.slice(0, 5).map(d => d.item_name),
                    datasets: [{
                        label: 'Orders',
                        data: data.popular_items.slice(0, 5).map(d => d.times_ordered),
                        backgroundColor: '#FF6B35'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true
                }
            });

            // Category Chart
            if (categoryChart) categoryChart.destroy();
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            categoryChart = new Chart(categoryCtx, {
                type: 'pie',
                data: {
                    labels: data.category_revenue.map(d => d.category),
                    datasets: [{
                        data: data.category_revenue.map(d => d.revenue),
                        backgroundColor: ['#FF6B35', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true
                }
            });
        }

        async function generateReport() {
            showToast('Generating report...', 'info');
            window.location.href = '/backend/reports/sales-report.php';
        }

        document.addEventListener('DOMContentLoaded', loadDashboard);
    </script>
</body>

</html>