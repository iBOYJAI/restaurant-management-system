<?php

/**
 * Orders API
 * POST: Create new order
 * GET: Fetch orders (optionally filtered by status)
 * PUT: Update order status
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging (disable in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/functions.php';

    $method = $_SERVER['REQUEST_METHOD'];


    switch ($method) {
        case 'GET':
            // Fetch orders - filter by status and/or date period
            $status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : null;
            $period = isset($_GET['period']) ? sanitizeInput($_GET['period']) : null; // today | yesterday | last_week

            $sql = "SELECT
                        o.*,
                        COUNT(oi.id) as item_count
                    FROM orders o
                    LEFT JOIN order_items oi ON o.id = oi.order_id";
            $where = [];
            $params = []; // positional params only

            if ($status) {
                // Support comma-separated statuses e.g. status=placed,preparing,ready
                $statuses = array_values(array_filter(array_map('trim', explode(',', $status))));
                if (count($statuses) === 1) {
                    $where[] = "o.status = ?";
                    $params[] = $statuses[0];
                } elseif (count($statuses) > 1) {
                    $placeholders = implode(',', array_fill(0, count($statuses), '?'));
                    $where[] = "o.status IN ($placeholders)";
                    foreach ($statuses as $s) {
                        $params[] = $s;
                    }
                }
            }
            if ($period === 'today') {
                $where[] = "DATE(o.created_at) = CURDATE()";
            } elseif ($period === 'yesterday') {
                $where[] = "DATE(o.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            } elseif ($period === 'last_week') {
                $where[] = "o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            }

            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            $sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $orders = $stmt->fetchAll();

            // Fetch items for each order
            foreach ($orders as &$order) {
                $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $itemStmt->execute([$order['id']]);
                $order['items'] = $itemStmt->fetchAll();
                $order['total_amount'] = floatval($order['total_amount']);
            }

            jsonResponse(true, $orders);
            break;

        case 'POST':
            // Create new order
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            $errors = validateRequired($input, ['table_number', 'items']);
            if (!empty($errors)) {
                jsonResponse(false, null, implode(', ', $errors), 400);
            }

            if (empty($input['items']) || !is_array($input['items'])) {
                jsonResponse(false, null, 'Order must contain at least one item', 400);
            }

            // Begin transaction
            $pdo->beginTransaction();

            try {
                // Calculate total
                $totalAmount = 0;
                $orderItems = [];

                foreach ($input['items'] as $item) {
                    // Fetch item details
                    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ? AND is_available = 1");
                    $stmt->execute([$item['id']]);
                    $menuItem = $stmt->fetch();

                    if (!$menuItem) {
                        throw new Exception("Item not found or unavailable: " . $item['id']);
                    }

                    $quantity = intval($item['quantity']);
                    $itemTotal = $menuItem['price'] * $quantity;
                    $totalAmount += $itemTotal;

                    $orderItems[] = [
                        'menu_item_id' => $menuItem['id'],
                        'menu_item_name' => $menuItem['name'],
                        'quantity' => $quantity,
                        'price' => $menuItem['price'],
                        'item_notes' => isset($item['notes']) ? sanitizeInput($item['notes']) : null
                    ];
                }

                // Calculate GST (5%)
                $gstRate = 0.05;
                $taxAmount = $totalAmount * $gstRate;
                $grandTotal = $totalAmount + $taxAmount;

                // Create order
                $orderNumber = generateOrderNumber();
                $tableNumber = sanitizeInput($input['table_number']);
                $specialNotes = isset($input['special_notes']) ? sanitizeInput($input['special_notes']) : null;

                $stmt = $pdo->prepare("
                    INSERT INTO orders (order_number, table_number, total_amount, tax_amount, status, special_notes)
                    VALUES (?, ?, ?, ?, 'placed', ?)
                ");
                $stmt->execute([$orderNumber, $tableNumber, $grandTotal, $taxAmount, $specialNotes]);
                $orderId = $pdo->lastInsertId();

                // Insert order items
                $itemStmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, menu_item_id, menu_item_name, quantity, price, item_notes)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                foreach ($orderItems as $item) {
                    $itemStmt->execute([
                        $orderId,
                        $item['menu_item_id'],
                        $item['menu_item_name'],
                        $item['quantity'],
                        $item['price'],
                        $item['item_notes']
                    ]);
                }

                $pdo->commit();

                // Fetch complete order
                $orderStmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
                $orderStmt->execute([$orderId]);
                $order = $orderStmt->fetch();

                $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $itemsStmt->execute([$orderId]);
                $order['items'] = $itemsStmt->fetchAll();

                jsonResponse(true, $order, 'Order placed successfully', 201);
            } catch (Exception $e) {
                $pdo->rollBack();
                // Log the actual error for debugging
                error_log("Order Placement Error: " . $e->getMessage());
                jsonResponse(false, null, 'Error placing order: ' . $e->getMessage(), 500);
            }
            break;

        case 'PUT':
            // Update order status
            $input = json_decode(file_get_contents('php://input'), true);

            // Accept 'id' or 'order_id' for compatibility
            if (isset($input['id']) && !isset($input['order_id'])) {
                $input['order_id'] = $input['id'];
            }

            $errors = validateRequired($input, ['order_id', 'status']);
            if (!empty($errors)) {
                jsonResponse(false, null, implode(', ', $errors), 400);
            }

            $validStatuses = ['placed', 'preparing', 'ready', 'served', 'cancelled'];
            $status = sanitizeInput($input['status']);

            if (!in_array($status, $validStatuses)) {
                jsonResponse(false, null, 'Invalid status', 400);
            }

            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $input['order_id']]);

            if ($stmt->rowCount() > 0) {
                jsonResponse(true, ['order_id' => $input['order_id'], 'status' => $status], 'Order status updated');
            } else {
                // Check if order exists
                $checkStmt = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
                $checkStmt->execute([$input['order_id']]);
                $existingOrder = $checkStmt->fetch();

                if ($existingOrder) {
                    if ($existingOrder['status'] === $status) {
                        jsonResponse(true, ['order_id' => $input['order_id'], 'status' => $status], 'Order status already updated');
                    } else {
                        jsonResponse(false, null, 'Failed to update order status', 500);
                    }
                } else {
                    jsonResponse(false, null, 'Order not found', 404);
                }
            }
            break;

        default:
            jsonResponse(false, null, 'Method not allowed', 405);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Error: ' . $e->getMessage(), 500);
}
