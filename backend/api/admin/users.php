<?php

/**
 * User Management API - Role-based Access
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require admin permission
requireAuth();
requirePermission('users.view');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // List all users
            requirePermission('users.view');

            $restaurantId = getUserRestaurant();
            $user = getCurrentAdmin();

            $sql = "SELECT u.*, r.display_name as role_display, rest.name as restaurant_name
                    FROM users u
                    LEFT JOIN roles r ON u.role_id = r.id
                    LEFT JOIN restaurants rest ON u.restaurant_id = rest.id";

            // Super admin sees all users, others see only their restaurant
            if ($user['role_name'] !== 'super_admin') {
                $sql .= " WHERE u.restaurant_id = :restaurant_id";
            }

            $sql .= " ORDER BY u.created_at DESC";

            $stmt = $pdo->prepare($sql);
            if ($user['role_name'] !== 'super_admin') {
                $stmt->bindValue(':restaurant_id', $restaurantId, PDO::PARAM_INT);
            }
            $stmt->execute();

            $users = $stmt->fetchAll();
            jsonResponse(true, $users);
            break;

        case 'POST':
            // Create new user
            requirePermission('users.create');

            $input = json_decode(file_get_contents('php://input'), true);

            $errors = validateRequired($input, ['username', 'password', 'full_name', 'role_id']);
            if (!empty($errors)) {
                jsonResponse(false, null, implode(', ', $errors), 400);
            }

            $username = sanitizeInput($input['username']);
            $password = $input['password'];
            $fullName = sanitizeInput($input['full_name']);
            $roleId = intval($input['role_id']);
            $phone = isset($input['phone']) ? sanitizeInput($input['phone']) : null;
            $restaurantId = isset($input['restaurant_id']) ? intval($input['restaurant_id']) : getUserRestaurant();

            // Hash password
            $passwordHash = hashPassword($password);

            $stmt = $pdo->prepare("
                INSERT INTO users (username, password_hash, full_name, role_id, restaurant_id, phone)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$username, $passwordHash, $fullName, $roleId, $restaurantId, $phone]);

            jsonResponse(true, ['id' => $pdo->lastInsertId()], 'User created successfully', 201);
            break;

        case 'PUT':
            // Update user
            requirePermission('users.edit');

            $input = json_decode(file_get_contents('php://input'), true);

            $id = intval($input['id'] ?? 0);
            if ($id <= 0) {
                jsonResponse(false, null, 'Invalid user ID', 400);
            }

            $fullName = sanitizeInput($input['full_name']);
            $roleId = intval($input['role_id']);
            $phone = isset($input['phone']) ? sanitizeInput($input['phone']) : null;
            $isActive = isset($input['is_active']) ? intval($input['is_active']) : 1;

            $updateFields = [
                'full_name' => $fullName,
                'role_id' => $roleId,
                'phone' => $phone,
                'is_active' => $isActive
            ];

            // Update password if provided
            if (!empty($input['password'])) {
                $updateFields['password_hash'] = hashPassword($input['password']);
            }

            $setClause = implode(', ', array_map(fn($key) => "$key = ?", array_keys($updateFields)));
            $values = array_values($updateFields);
            $values[] = $id;

            $stmt = $pdo->prepare("UPDATE users SET $setClause WHERE id = ?");
            $stmt->execute($values);

            jsonResponse(true, null, 'User updated successfully');
            break;

        case 'DELETE':
            // Deactivate user (soft delete)
            requirePermission('users.delete');

            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);

            if ($id <= 0) {
                jsonResponse(false, null, 'Invalid user ID', 400);
            }

            // Don't allow deleting self
            if ($id == $_SESSION['admin_id']) {
                jsonResponse(false, null, 'Cannot delete your own account', 400);
            }

            $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);

            jsonResponse(true, null, 'User deactivated successfully');
            break;

        default:
            jsonResponse(false, null, 'Method not allowed', 405);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Error: ' . $e->getMessage(), 500);
}
