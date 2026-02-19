<?php

/**
 * Feedback API - Customer Reviews & Ratings
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            // Submit feedback (public endpoint)
            $input = json_decode(file_get_contents('php://input'), true);

            $errors = validateRequired($input, ['order_id', 'customer_name', 'overall_rating']);
            if (!empty($errors)) {
                jsonResponse(false, null, implode(', ', $errors), 400);
            }

            // Verify order exists
            $orderStmt = $pdo->prepare("SELECT restaurant_id FROM orders WHERE id = ?");
            $orderStmt->execute([$input['order_id']]);
            $order = $orderStmt->fetch();

            if (!$order) {
                jsonResponse(false, null, 'Order not found', 404);
            }

            $stmt = $pdo->prepare("
                INSERT INTO feedback (
                    restaurant_id, order_id, customer_name, customer_email,
                    overall_rating, food_quality, service_rating, ambience_rating, comments
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $order['restaurant_id'],
                $input['order_id'],
                sanitizeInput($input['customer_name']),
                isset($input['customer_email']) ? sanitizeInput($input['customer_email']) : null,
                intval($input['overall_rating']),
                isset($input['food_quality']) ? intval($input['food_quality']) : null,
                isset($input['service_rating']) ? intval($input['service_rating']) : null,
                isset($input['ambience_rating']) ? intval($input['ambience_rating']) : null,
                isset($input['comments']) ? sanitizeInput($input['comments']) : null
            ]);

            $feedbackId = $pdo->lastInsertId();

            // Insert item ratings if provided
            if (isset($input['item_ratings']) && is_array($input['item_ratings'])) {
                $itemStmt = $pdo->prepare("
                    INSERT INTO item_ratings (feedback_id, menu_item_id, rating, comment)
                    VALUES (?, ?, ?, ?)
                ");

                foreach ($input['item_ratings'] as $itemRating) {
                    $itemStmt->execute([
                        $feedbackId,
                        $itemRating['menu_item_id'],
                        intval($itemRating['rating']),
                        isset($itemRating['comment']) ? sanitizeInput($itemRating['comment']) : null
                    ]);
                }
            }

            jsonResponse(true, ['id' => $feedbackId], 'Thank you for your feedback!', 201);
            break;

        case 'GET':
            // Get feedback for order or all feedback
            if (isset($_GET['order_id'])) {
                // Get feedback for specific order
                $stmt = $pdo->prepare("
                    SELECT f.*, 
                        (SELECT JSON_ARRAYAGG(JSON_OBJECT('menu_item_id', ir.menu_item_id, 'rating', ir.rating, 'comment', ir.comment))
                         FROM item_ratings ir WHERE ir.feedback_id = f.id) as item_ratings
                    FROM feedback f
                    WHERE f.order_id = ?
                ");
                $stmt->execute([$_GET['order_id']]);
                $feedback = $stmt->fetch();

                if ($feedback && $feedback['item_ratings']) {
                    $feedback['item_ratings'] = json_decode($feedback['item_ratings'], true);
                }

                jsonResponse(true, $feedback);
                jsonResponse(true, $ratings);
            } elseif (isset($_GET['mode']) && $_GET['mode'] === 'recent') {
                // Get recent public reviews
                $stmt = $pdo->query("
                    SELECT 
                        f.customer_name,
                        f.overall_rating,
                        f.comments,
                        f.created_at,
                        f.admin_response,
                        f.responded_at
                    FROM feedback f
                    WHERE f.comments IS NOT NULL AND f.comments != ''
                    ORDER BY f.created_at DESC
                    LIMIT 20
                ");
                $reviews = $stmt->fetchAll();
                jsonResponse(true, $reviews);
            } else {
                // Default: Get average ratings for menu items
                $stmt = $pdo->query("
                   SELECT 
                        mi.id,
                        mi.name,
                        AVG(ir.rating) as avg_rating,
                        COUNT(ir.id) as rating_count
                    FROM menu_items mi
                    LEFT JOIN item_ratings ir ON mi.id = ir.menu_item_id
                    GROUP BY mi.id, mi.name
                    HAVING rating_count > 0
                ");
                $ratings = $stmt->fetchAll();

                jsonResponse(true, $ratings);
            }
            break;

        default:
            jsonResponse(false, null, 'Method not allowed', 405);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Error: ' . $e->getMessage(), 500);
}
