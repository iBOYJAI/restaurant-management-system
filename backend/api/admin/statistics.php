<?php

/**
 * Admin Statistics API
 * Dashboard metrics
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Check authentication
if (!isLoggedIn()) {
    jsonResponse(false, null, 'Unauthorized', 401);
}

try {
    $stats = [];

    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = $stmt->fetch()['count'];

    // Total revenue
    $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE status != 'cancelled'");
    $stats['total_revenue'] = floatval($stmt->fetch()['revenue']);

    // Today's orders
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
    $stats['today_orders'] = $stmt->fetch()['count'];

    // Today's revenue
    $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'");
    $stats['today_revenue'] = floatval($stmt->fetch()['revenue']);

    // Orders by status
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $stats['by_status'] = $statusCounts;

    // Recent orders
    $stmt = $pdo->query("
        SELECT o.*, COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    $stats['recent_orders'] = $stmt->fetchAll();

    jsonResponse(true, $stats);
} catch (Exception $e) {
    jsonResponse(false, null, 'Error: ' . $e->getMessage(), 500);
}
