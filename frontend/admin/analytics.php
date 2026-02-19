<?php
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/includes/auth.php';
requireAuth();
requirePermission('analytics.view');
$user = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar styles are in includes/sidebar.php */

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: var(--space-lg);
            background: var(--bg-secondary);
        }

        .filters-bar {
            background: white;
            padding: var(--space-md);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-md);
            display: flex;
            gap: var(--space-md);
            flex-wrap: wrap;
            align-items: end;
            box-shadow: var(--shadow-sm);
        }

        .chart-container {
            background: white;
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--space-md);
            border: 1px solid var(--border);
        }

        .stat-card {
            background: white;
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            text-align: center;
            border: 1px solid var(--border);
            transition: transform var(--transition-fast);
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>
        <main class="main-content">
            <h1 class="mb-4">📈 Analytics & Reports</h1>

            <div class="filters-bar">
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="date" id="startDate" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input type="date" id="endDate" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary" onclick="loadAnalytics()">Apply Filters</button>
                </div>
                <div class="form-group" style="margin-left: auto;">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-outline" onclick="exportReport()">📄 Export Report</button>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--space-md); margin-bottom: var(--space-md);">
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="color: var(--text-secondary); font-size: 0.875rem;">Total Orders</div>
                        <div style="font-size: 2rem; font-weight: 700; color: var(--primary);" id="totalOrders">-</div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="color: var(--text-secondary); font-size: 0.875rem;">Total Revenue</div>
                        <div style="font-size: 2rem; font-weight: 700; color: var(--success);" id="totalRevenue">-</div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="color: var(--text-secondary); font-size: 0.875rem;">Avg Order Value</div>
                        <div style="font-size: 2rem; font-weight: 700; color: var(--info);" id="avgOrderValue">-</div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="color: var(--text-secondary); font-size: 0.875rem;">Unique Tables</div>
                        <div style="font-size: 2rem; font-weight: 700; color: var(--warning);" id="uniqueTables">-</div>
                    </div>
                </div>
            </div>

            <div class="chart-container">
                <h3>Sales Trend</h3>
                <canvas id="salesTrendChart" height="80"></canvas>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                <div class="chart-container">
                    <h3>Top 10 Popular Items</h3>
                    <canvas id="popularItemsChart" height="120"></canvas>
                </div>
                <div class="chart-container">
                    <h3>Revenue by Category</h3>
                    <canvas id="categoryRevenueChart" height="120"></canvas>
                </div>
            </div>

            <div class="chart-container">
                <h3>Peak Hours Analysis</h3>
                <canvas id="peakHoursChart" height="80"></canvas>
            </div>

            <div class="chart-container">
                <h3>Order Status Distribution</h3>
                <canvas id="statusDistChart" height="60"></canvas>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/notifications.js"></script>
    <script src="../assets/js/chart.min.js"></script>
    <script>
        let charts = {};

        // Set default dates
        document.getElementById('endDate').valueAsDate = new Date();
        const startDate = new Date();
        startDate.setDate(startDate.getDate() - 30);
        document.getElementById('startDate').valueAsDate = startDate;

        async function loadAnalytics() {
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;

            const response = await apiRequest(`/backend/api/admin/analytics.php?start_date=${start}&end_date=${end}`);

            if (response.success) {
                updateKPIs(response.data.key_metrics);
                renderCharts(response.data);
            }
        }

        function updateKPIs(metrics) {
            document.getElementById('totalOrders').textContent = metrics.total_orders || 0;
            document.getElementById('totalRevenue').textContent = formatCurrency(metrics.total_revenue || 0);
            document.getElementById('avgOrderValue').textContent = formatCurrency(metrics.avg_order_value || 0);
            document.getElementById('uniqueTables').textContent = metrics.unique_tables || 0;
        }

        function renderCharts(data) {
            // Sales Trend
            renderChart('salesTrendChart', 'line', {
                labels: data.sales_trends.map(d => d.date),
                datasets: [{
                    label: 'Revenue',
                    data: data.sales_trends.map(d => parseFloat(d.revenue)),
                    borderColor: '#FF6B35',
                    backgroundColor: 'rgba(255, 107, 53, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            });

            // Popular Items
            renderChart('popularItemsChart', 'bar', {
                labels: data.popular_items.slice(0, 10).map(d => d.item_name),
                datasets: [{
                    label: 'Times Ordered',
                    data: data.popular_items.slice(0, 10).map(d => parseInt(d.times_ordered)),
                    backgroundColor: '#4ECDC4'
                }]
            });

            // Category Revenue
            renderChart('categoryRevenueChart', 'doughnut', {
                labels: data.category_revenue.map(d => d.category),
                datasets: [{
                    data: data.category_revenue.map(d => parseFloat(d.revenue)),
                    backgroundColor: ['#FF6B35', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F67280']
                }]
            });

            // Peak Hours
            const peakData = Array(24).fill(0);
            data.peak_hours.forEach(h => {
                peakData[parseInt(h.hour)] = parseFloat(h.revenue);
            });
            renderChart('peakHoursChart', 'bar', {
                labels: Array.from({
                    length: 24
                }, (_, i) => i + ':00'),
                datasets: [{
                    label: 'Revenue by Hour',
                    data: peakData,
                    backgroundColor: '#45B7D1'
                }]
            });

            // Status Distribution
            renderChart('statusDistChart', 'pie', {
                labels: data.status_distribution.map(d => d.status.toUpperCase()),
                datasets: [{
                    data: data.status_distribution.map(d => parseInt(d.count)),
                    backgroundColor: ['#FF6B35', '#FFA07A', '#4ECDC4', '#45B7D1', '#98D8C8']
                }]
            });
        }

        function renderChart(id, type, data) {
            if (charts[id]) charts[id].destroy();
            const ctx = document.getElementById(id).getContext('2d');
            charts[id] = new Chart(ctx, {
                type: type,
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: type !== 'bar'
                        }
                    }
                }
            });
        }

        function exportReport() {
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;
            window.open(`../../backend/reports/sales-report.php?start=${start}&end=${end}`);
        }

        document.addEventListener('DOMContentLoaded', loadAnalytics);
    </script>
</body>

</html>