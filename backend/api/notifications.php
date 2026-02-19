<?php

/**
 * Notifications API - Offline Polling System
 * No external dependencies - pure PHP polling
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Require authentication
if (!isLoggedIn()) {
    jsonResponse(false, null, 'Unauthorized', 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$user = getCurrentAdmin();

try {
    switch ($method) {
        case 'GET':
            // Fetch notifications for current user
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
            $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';

            $sql = "SELECT n.*, o.order_number, o.table_number
                    FROM notifications n
                    LEFT JOIN orders o ON n.related_order_id = o.id
                    WHERE n.user_id = :user_id OR n.user_id IS NULL";

            if ($unreadOnly) {
                $sql .= " AND n.is_read = 0";
            }

            $sql .= " ORDER BY n.created_at DESC LIMIT :limit";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':user_id', $user['id'], PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $notifications = $stmt->fetchAll();

            // Get unread count
            $countStmt = $pdo->prepare("
                SELECT COUNT(*) as unread_count 
                FROM notifications 
                WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0
            ");
            $countStmt->execute([$user['id']]);
            $unreadCount = $countStmt->fetch()['unread_count'];

            jsonResponse(true, [
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
            break;

        case 'PUT':
            // Mark as read
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['notification_id'])) {
                // Mark single notification
                $stmt = $pdo->prepare("
                    UPDATE notifications 
                    SET is_read = 1, read_at = NOW() 
                    WHERE id = ? AND (user_id = ? OR user_id IS NULL)
                ");
                $stmt->execute([$input['notification_id'], $user['id']]);
                jsonResponse(true, null, 'Notification marked as read');
            } elseif (isset($input['mark_all_read']) && $input['mark_all_read']) {
                // Mark all as read
                $stmt = $pdo->prepare("
                    UPDATE notifications 
                    SET is_read = 1, read_at = NOW() 
                    WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0
                ");
                $stmt->execute([$user['id']]);
                jsonResponse(true, null, 'All notifications marked as read');
            } else {
                jsonResponse(false, null, 'Invalid request', 400);
            }
            break;

        case 'DELETE':
            // Delete notification
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['notification_id'])) {
                jsonResponse(false, null, 'Notification ID required', 400);
            }

            $stmt = $pdo->prepare("
                DELETE FROM notifications 
                WHERE id = ? AND (user_id = ? OR user_id IS NULL)
            ");
            $stmt->execute([$input['notification_id'], $user['id']]);

            jsonResponse(true, null, 'Notification deleted');
            break;

        default:
            jsonResponse(false, null, 'Method not allowed', 405);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Error: ' . $e->getMessage(), 500);
}
