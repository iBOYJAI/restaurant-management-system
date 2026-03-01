<?php

/**
 * Admin Restaurant / Store Settings API
 * GET: Fetch store details (name)
 * PUT: Update store name
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isLoggedIn()) {
    jsonResponse(false, null, 'Unauthorized', 401);
}

requireAnyRole(['super_admin', 'admin']);

$method = $_SERVER['REQUEST_METHOD'];
$restaurantId = 1;

try {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->prepare("SELECT id, name, slug, address, phone, email FROM restaurants WHERE id = ?");
            $stmt->execute([$restaurantId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                jsonResponse(false, null, 'Restaurant not found', 404);
            }
            jsonResponse(true, $row);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) {
                jsonResponse(false, null, 'Invalid JSON', 400);
            }
            $name = isset($input['name']) ? trim(sanitizeInput($input['name'])) : '';
            if ($name === '') {
                jsonResponse(false, null, 'Store name is required', 400);
            }
            $stmt = $pdo->prepare("UPDATE restaurants SET name = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$name, $restaurantId]);
            if ($stmt->rowCount() > 0 || true) {
                jsonResponse(true, ['name' => $name], 'Store name updated');
            } else {
                jsonResponse(false, null, 'Update failed', 500);
            }
            break;

        default:
            jsonResponse(false, null, 'Method not allowed', 405);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Error: ' . $e->getMessage(), 500);
}
