<?php

/**
 * Analytics API - Offline Dashboard Data
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAuth();
requirePermission('analytics.view');

$restaurantId = getUserRestaurant();
$user = getCurrentAdmin();

try {
    $analyticsData = [];

    // Date range filtering
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

    // === Quick Stats Mode (For Reports Page) ===
    if (isset($_GET['mode']) && $_GET['mode'] === 'quick_stats') {
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');

        // Today's Revenue
        $stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE restaurant_id = ? AND DATE(created_at) = ? AND status != 'cancelled'");
        $stmt->execute([$restaurantId, $today]);
        $todayRevenue = $stmt->fetchColumn() ?: 0;

        // Monthly Revenue
        $stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE restaurant_id = ? AND DATE(created_at) BETWEEN ? AND ? AND status != 'cancelled'");
        $stmt->execute([$restaurantId, $monthStart, $today]);
        $monthRevenue = $stmt->fetchColumn() ?: 0;

        jsonResponse(true, [
            'today_revenue' => $todayRevenue,
            'month_revenue' => $monthRevenue
        ]);
        exit;
    }

    // === Sales Trends ===
    $stmt = $pdo->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as order_count,
            SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END) as revenue,
            AVG(CASE WHEN status != 'cancelled' THEN total_amount END) as avg_order_value
        FROM orders
        WHERE restaurant_id = ? AND DATE(created_at) BETWEEN ? AND ?
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $stmt->execute([$restaurantId, $startDate, $endDate]);
    $analyticsData['sales_trends'] = $stmt->fetchAll();

    // === Popular Items ===
    $stmt = $pdo->prepare("
        SELECT 
            mi.name as item_name,
            c.name as category,
            COUNT(oi.id) as times_ordered,
            SUM(oi.quantity) as total_quantity,
            SUM(oi.price * oi.quantity) as revenue
        FROM menu_items mi
        JOIN order_items oi ON mi.id = oi.menu_item_id
        JOIN orders o ON oi.order_id = o.id
        JOIN categories c ON mi.category_id = c.id
        WHERE mi.restaurant_id = ? AND o.status != 'cancelled'
            AND DATE(o.created_at) BETWEEN ? AND ?
        GROUP BY mi.id, mi.name, c.name
        ORDER BY total_quantity DESC
        LIMIT 10
    ");
    $stmt->execute([$restaurantId, $startDate, $endDate]);
    $analyticsData['popular_items'] = $stmt->fetchAll();

    // === Revenue by Category ===
    $stmt = $pdo->prepare("
        SELECT 
            c.name as category,
            COUNT(DISTINCT o.id) as order_count,
            SUM(oi.price * oi.quantity) as revenue
        FROM categories c
        JOIN menu_items mi ON c.id = mi.category_id
        JOIN order_items oi ON mi.id = oi.menu_item_id
        JOIN orders o ON oi.order_id = o.id
        WHERE c.restaurant_id = ? AND o.status != 'cancelled'
            AND DATE(o.created_at) BETWEEN ? AND ?
        GROUP BY c.id, c.name
        ORDER BY revenue DESC
    ");
    $stmt->execute([$restaurantId, $startDate, $endDate]);
    $analyticsData['category_revenue'] = $stmt->fetchAll();

    // === Peak Hours ===
    $stmt = $pdo->prepare("
        SELECT 
            HOUR(created_at) as hour,
            COUNT(*) as order_count,
            SUM(total_amount) as revenue
        FROM orders
        WHERE restaurant_id = ? AND status != 'cancelled'
            AND DATE(created_at) BETWEEN ? AND ?
        GROUP BY HOUR(created_at)
        ORDER BY hour ASC
    ");
    $stmt->execute([$restaurantId, $startDate, $endDate]);
    $analyticsData['peak_hours'] = $stmt->fetchAll();

    // === Order Status Distribution ===
    $stmt = $pdo->prepare("
        SELECT 
            status,
            COUNT(*) as count,
            SUM(total_amount) as total_value
        FROM orders
        WHERE restaurant_id = ? 
            AND DATE(created_at) BETWEEN ? AND ?
        GROUP BY status
    ");
    $stmt->execute([$restaurantId, $startDate, $endDate]);
    $analyticsData['status_distribution'] = $stmt->fetchAll();

    // === Key Metrics ===
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_orders,
            SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END) as total_revenue,
            AVG(CASE WHEN status != 'cancelled' THEN total_amount END) as avg_order_value,
            COUNT(DISTINCT table_number) as unique_tables
        FROM orders
        WHERE restaurant_id = ? 
            AND DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->execute([$restaurantId, $startDate, $endDate]);
    $analyticsData['key_metrics'] = $stmt->fetch();

    // === Customer Satisfaction (if feedback exists) ===
    $stmt = $pdo->prepare("
        SELECT 
            AVG(overall_rating) as avg_overall,
            AVG(food_quality) as avg_food,
            AVG(service_rating) as avg_service,
            COUNT(*) as total_feedback
        FROM feedback
        WHERE restaurant_id = ? 
            AND DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->execute([$restaurantId, $startDate, $endDate]);
    $analyticsData['satisfaction'] = $stmt->fetch();

    jsonResponse(true, $analyticsData);
} catch (Exception $e) {
    jsonResponse(false, null, 'Error: ' . $e->getMessage(), 500);
}
