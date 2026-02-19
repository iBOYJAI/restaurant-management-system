<?php
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/includes/auth.php';
requireAuth();
$admin = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Revenue - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: var(--space-lg);
            background: var(--bg-secondary);
        }

        .report-card {
            background: white;
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            margin-bottom: var(--space-md);
            transition: transform 0.2s;
        }

        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-md);
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="main-content">
            <h1 class="mb-4">📊 Reports & Revenue</h1>

            <div class="grid-2">
                <!-- Sales Report -->
                <div class="report-card">
                    <h3>📑 Sales Report</h3>
                    <p class="text-secondary mb-3">Detailed breakdown of daily sales, revenue, and order counts.</p>
                    <div class="form-group">
                        <label>Date Range</label>
                        <div class="d-flex gap-2">
                            <input type="date" id="salesStart" class="form-control">
                            <input type="date" id="salesEnd" class="form-control">
                        </div>
                    </div>
                    <button class="btn btn-primary w-full" onclick="generateReport('sales')">Generate PDF Report</button>
                </div>

                <!-- GST Report -->
                <div class="report-card">
                    <h3>🏛️ GST / Tax Report</h3>
                    <p class="text-secondary mb-3">Tax collected summary for accounting and filing purposes.</p>
                    <div class="form-group">
                        <label>Date Range</label>
                        <div class="d-flex gap-2">
                            <input type="date" id="taxStart" class="form-control">
                            <input type="date" id="taxEnd" class="form-control">
                        </div>
                    </div>
                    <button class="btn btn-outline w-full" onclick="generateReport('sales')">Generate Tax Statement</button>
                </div>
            </div>

            <!-- Revenue Summary -->
            <div class="report-card mt-4">
                <h3 class="mb-3">Revenue Quick View</h3>
                <div class="grid-2">
                    <div class="p-4 bg-light rounded text-center">
                        <div class="text-secondary text-sm">Today's Revenue</div>
                        <div class="text-2xl font-bold text-success" id="todayRev">-</div>
                    </div>
                    <div class="p-4 bg-light rounded text-center">
                        <div class="text-secondary text-sm">Monthly Revenue</div>
                        <div class="text-2xl font-bold text-primary" id="monthRev">-</div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Set default dates
        const today = new Date().toISOString().split('T')[0];
        document.querySelectorAll('input[type="date"]').forEach(input => input.value = today);

        function generateReport(type) {
            const start = document.getElementById(type + 'Start').value;
            const end = document.getElementById(type + 'End').value;
            // Using the existing sales-report generator for now, can extend later
            window.open(`../../backend/reports/sales-report.php?start=${start}&end=${end}&type=${type}`);
        }

        async function loadQuickStats() {
            try {
                const res = await apiRequest('/backend/api/admin/analytics.php?mode=quick_stats');
                if (res.success) {
                    document.getElementById('todayRev').textContent = formatCurrency(res.data.today_revenue);
                    document.getElementById('monthRev').textContent = formatCurrency(res.data.month_revenue);
                }
            } catch (e) {
                console.error('Failed to load quick stats:', e);
            }
        }

        // Load stats on page load
        document.addEventListener('DOMContentLoaded', loadQuickStats);
    </script>
</body>

</html>