<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireAuth();
requirePermission('analytics.view');

$startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end'] ?? date('Y-m-d');

// Fetch summary stats
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as avg_order_value
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ? 
    AND status != 'cancelled'
");
$stmt->execute([$startDate, $endDate]);
$summary = $stmt->fetch();

// Fetch daily breakdown
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as orders,
        SUM(total_amount) as revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ? 
    AND status != 'cancelled'
    GROUP BY DATE(created_at) 
    ORDER BY date DESC
");
$stmt->execute([$startDate, $endDate]);
$dailyStats = $stmt->fetchAll();

// Fetch category performance
$stmt = $pdo->prepare("
    SELECT 
        c.name as category,
        SUM(oi.quantity) as items_sold,
        SUM(oi.price * oi.quantity) as revenue
    FROM order_items oi
    JOIN menu_items mi ON oi.menu_item_id = mi.id
    JOIN categories c ON mi.category_id = c.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ? 
    AND o.status != 'cancelled'
    GROUP BY c.id
    ORDER BY revenue DESC
");
$stmt->execute([$startDate, $endDate]);
$categoryStats = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sales Report (<?php echo $startDate; ?> - <?php echo $endDate; ?>)</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-box {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #E63946;
            margin-top: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f4f4f4;
            font-weight: 600;
        }

        .text-right {
            text-align: right;
        }

        h2 {
            border-bottom: 2px solid #E63946;
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-size: 18px;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }

        .btn {
            display: inline-block;
            background: #333;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" class="btn">🖨️ Print Report</button>
    </div>

    <div class="header">
        <h1>Sales Report</h1>
        <p>Period: <strong><?php echo date('M d, Y', strtotime($startDate)); ?></strong> to <strong><?php echo date('M d, Y', strtotime($endDate)); ?></strong></p>
        <p>Generated on: <?php echo date('M d, Y H:i A'); ?></p>
    </div>

    <div class="summary-grid">
        <div class="stat-box">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">₹<?php echo number_format($summary['total_revenue'] ?? 0, 2); ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value"><?php echo number_format($summary['total_orders'] ?? 0); ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Avg Order Value</div>
            <div class="stat-value">₹<?php echo number_format($summary['avg_order_value'] ?? 0, 2); ?></div>
        </div>
    </div>

    <h2>Category Performance</h2>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th class="text-right">Items Sold</th>
                <th class="text-right">Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categoryStats as $stat): ?>
                <tr>
                    <td><?php echo htmlspecialchars($stat['category']); ?></td>
                    <td class="text-right"><?php echo $stat['items_sold']; ?></td>
                    <td class="text-right">₹<?php echo number_format($stat['revenue'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($categoryStats)): ?>
                <tr>
                    <td colspan="3" style="text-align: center;">No data found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Daily Breakdown</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th class="text-right">Orders</th>
                <th class="text-right">Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dailyStats as $day): ?>
                <tr>
                    <td><?php echo date('M d, Y', strtotime($day['date'])); ?></td>
                    <td class="text-right"><?php echo $day['orders']; ?></td>
                    <td class="text-right">₹<?php echo number_format($day['revenue'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($dailyStats)): ?>
                <tr>
                    <td colspan="3" style="text-align: center;">No data found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>