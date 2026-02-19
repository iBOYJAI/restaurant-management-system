<?php

/**
 * Menu API - Customer facing
 * GET: Fetch all available menu items grouped by category
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    // Get optional category filter
    $categoryId = isset($_GET['category']) ? intval($_GET['category']) : null;

    // Get optional search query
    $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : null;

    // Build query
    $sql = "SELECT 
                c.id as category_id,
                c.name as category_name,
                c.display_order,
                mi.id,
                mi.name,
                mi.description,
                mi.price,
                mi.image_url,
                mi.is_available
            FROM categories c
            LEFT JOIN menu_items mi ON c.id = mi.category_id
            WHERE c.is_active = 1 AND (mi.is_available = 1 OR mi.id IS NULL)";

    if ($categoryId) {
        $sql .= " AND c.id = :category_id";
    }

    if ($search) {
        $sql .= " AND (mi.name LIKE :search OR mi.description LIKE :search)";
    }

    $sql .= " ORDER BY c.display_order, mi.name";

    $stmt = $pdo->prepare($sql);

    if ($categoryId) {
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
    }

    if ($search) {
        $searchTerm = "%{$search}%";
        $stmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
    }

    $stmt->execute();
    $results = $stmt->fetchAll();

    // Group items by category
    $menu = [];
    foreach ($results as $row) {
        $catId = $row['category_id'];

        if (!isset($menu[$catId])) {
            $menu[$catId] = [
                'id' => $catId,
                'name' => $row['category_name'],
                'display_order' => $row['display_order'],
                'items' => []
            ];
        }

        if ($row['id']) {
            $menu[$catId]['items'][] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'price' => floatval($row['price']),
                'image_url' => $row['image_url'],
                'is_available' => $row['is_available'] == 1
            ];
        }
    }

    // Convert to indexed array
    $menu = array_values($menu);

    jsonResponse(true, $menu);
} catch (Exception $e) {
    jsonResponse(false, null, 'Error fetching menu: ' . $e->getMessage(), 500);
}
