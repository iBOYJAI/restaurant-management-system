<?php

/**
 * Admin Categories API
 * Requires authentication
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

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("SELECT * FROM categories ORDER BY display_order");
            $categories = $stmt->fetchAll();
            jsonResponse(true, $categories);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $name = sanitizeInput($input['name'] ?? '');
            $displayOrder = intval($input['display_order'] ?? 0);
            $isActive = isset($input['is_active']) ? intval($input['is_active']) : 1;

            if (empty($name)) {
                jsonResponse(false, null, 'Category name is required', 400);
            }

            $stmt = $pdo->prepare("INSERT INTO categories (name, display_order, is_active) VALUES (?, ?, ?)");
            $stmt->execute([$name, $displayOrder, $isActive]);

            jsonResponse(true, ['id' => $pdo->lastInsertId()], 'Category created', 201);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);
            $name = sanitizeInput($input['name'] ?? '');
            $displayOrder = intval($input['display_order'] ?? 0);
            $isActive = intval($input['is_active'] ?? 1);

            if ($id <= 0 || empty($name)) {
                jsonResponse(false, null, 'Invalid input', 400);
            }

            $stmt = $pdo->prepare("UPDATE categories SET name = ?, display_order = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $displayOrder, $isActive, $id]);

            jsonResponse(true, null, 'Category updated');
            break;

        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);

            if ($id <= 0) {
                jsonResponse(false, null, 'Invalid ID', 400);
            }

            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);

            jsonResponse(true, null, 'Category deleted');
            break;

        default:
            jsonResponse(false, null, 'Method not allowed', 405);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Error: ' . $e->getMessage(), 500);
}
