<?php

/**
 * Admin Menu Items API
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
            // Fetch all menu items
            $stmt = $pdo->query("
                SELECT mi.*, c.name as category_name
                FROM menu_items mi
                JOIN categories c ON mi.category_id = c.id
                ORDER BY c.display_order, mi.name
            ");
            $items = $stmt->fetchAll();

            jsonResponse(true, $items);
            break;

        case 'POST':
            // Check if this is an update (pseudo-PUT) via POST (for file support)
            // Or a new item creation
            $id = intval($_POST['id'] ?? 0);
            $action = isset($_POST['_method']) && strtoupper($_POST['_method']) === 'PUT' ? 'update' : ($id > 0 ? 'update' : 'create');

            if ($action === 'create') {
                // Create new menu item
                $name = sanitizeInput($_POST['name'] ?? '');
                $description = sanitizeInput($_POST['description'] ?? '');
                $price = floatval($_POST['price'] ?? 0);
                $categoryId = intval($_POST['category_id'] ?? 0);
                $isAvailable = isset($_POST['is_available']) ? 1 : 0;

                // Validate
                if (empty($name) || $price <= 0 || $categoryId <= 0) {
                    jsonResponse(false, null, 'Name, valid price, and category are required', 400);
                }

                // Handle multiple image uploads
                $images = [];
                for ($i = 1; $i <= 5; $i++) {
                    $field = $i === 1 ? 'image' : "image$i";
                    $images[$i] = null;
                    if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                        $upload = uploadImage($_FILES[$field], 'menu');
                        if ($upload['success']) {
                            $images[$i] = $upload['url'];
                        }
                    }
                }

                $stmt = $pdo->prepare("
                    INSERT INTO menu_items (category_id, name, description, price, image_url, image_url2, image_url3, image_url4, image_url5, is_available)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$categoryId, $name, $description, $price, $images[1], $images[2], $images[3], $images[4], $images[5], $isAvailable]);

                $newId = $pdo->lastInsertId();
                jsonResponse(true, ['id' => $newId], 'Menu item created successfully', 201);
            } else { // UPDATE
                $name = sanitizeInput($_POST['name'] ?? '');
                $description = sanitizeInput($_POST['description'] ?? '');
                $price = floatval($_POST['price'] ?? 0);
                $categoryId = intval($_POST['category_id'] ?? 0);
                $isAvailable = isset($_POST['is_available']) ? 1 : 0;

                if ($id <= 0 || empty($name) || $price <= 0 || $categoryId <= 0) {
                    jsonResponse(false, null, 'Invalid input for update', 400);
                }

                // Fetch current item to manage image deletion
                $stmtOld = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
                $stmtOld->execute([$id]);
                $oldItem = $stmtOld->fetch();

                if (!$oldItem) {
                    jsonResponse(false, null, 'Item not found', 404);
                }

                // Handle multiple image uploads
                $imageUpdates = [];
                $params = [$name, $description, $price, $categoryId, $isAvailable];
                $query = "UPDATE menu_items SET name = ?, description = ?, price = ?, category_id = ?, is_available = ?";

                for ($i = 1; $i <= 5; $i++) {
                    $field = $i === 1 ? 'image' : "image$i";
                    $col = $i === 1 ? 'image_url' : "image_url$i";

                    if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                        $upload = uploadImage($_FILES[$field], 'menu');
                        if ($upload['success']) {
                            // Delete old image if it exists
                            if ($oldItem[$col]) {
                                deleteImage($oldItem[$col]);
                            }
                            $query .= ", $col = ?";
                            $params[] = $upload['url'];
                        }
                    }
                }

                $query .= " WHERE id = ?";
                $params[] = $id;

                $stmt = $pdo->prepare($query);
                $stmt->execute($params);

                jsonResponse(true, ['id' => $id], 'Menu item updated successfully');
            }
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) {
                parse_str(file_get_contents('php://input'), $input);
            }
            $input = $input ?? [];

            $id = intval($input['id'] ?? 0);
            $name = sanitizeInput($input['name'] ?? '');
            $description = sanitizeInput($input['description'] ?? '');
            $price = floatval($input['price'] ?? 0);
            $categoryId = intval($input['category_id'] ?? 0);
            $isAvailable = isset($input['is_available']) ? intval($input['is_available']) : 1;

            if ($id <= 0 || empty($name) || $price <= 0 || $categoryId <= 0) {
                jsonResponse(false, null, 'Invalid input', 400);
            }

            $stmt = $pdo->prepare("
                UPDATE menu_items
                SET name = ?, description = ?, price = ?, category_id = ?, is_available = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $price, $categoryId, $isAvailable, $id]);

            jsonResponse(true, ['id' => $id], 'Menu item updated successfully');
            break;

        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) {
                parse_str(file_get_contents('php://input'), $input);
            }
            $id = intval($input['id'] ?? 0);

            if ($id <= 0) {
                jsonResponse(false, null, 'Invalid ID', 400);
            }

            // Get image URL before deleting
            $stmt = $pdo->prepare("SELECT image_url FROM menu_items WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch();

            // Delete item
            $deleteStmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
            $deleteStmt->execute([$id]);

            // Delete image file if exists
            if ($item && $item['image_url']) {
                deleteImage($item['image_url']);
            }

            jsonResponse(true, null, 'Menu item deleted successfully');
            break;

        case 'PATCH':
            // Toggle availability
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) {
                parse_str(file_get_contents('php://input'), $input);
            }
            $id = intval($input['id'] ?? 0);

            if ($id <= 0) {
                jsonResponse(false, null, 'Invalid ID', 400);
            }

            $stmt = $pdo->prepare("UPDATE menu_items SET is_available = NOT is_available WHERE id = ?");
            $stmt->execute([$id]);

            jsonResponse(true, ['id' => $id], 'Availability toggled');
            break;

        default:
            jsonResponse(false, null, 'Method not allowed', 405);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Error: ' . $e->getMessage(), 500);
}
