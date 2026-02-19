<?php

/**
 * Authentication Functions
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Hash password securely
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Admin login
 */
function login($username, $password)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && verifyPassword($password, $user['password_hash'])) {
        // Update last login
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $updateStmt->execute([$user['id']]);

        // Get role info
        $roleStmt = $pdo->prepare("SELECT name FROM roles WHERE id = ?");
        $roleStmt->execute([$user['role_id']]);
        $role = $roleStmt->fetch();

        // Set session
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_name'] = $user['full_name'];
        $_SESSION['admin_role'] = $role['name'] ?? 'admin';

        return $user;
    }

    return false;
}

/**
 * Logout
 */
function logout()
{
    session_destroy();
    session_start();
}

/**
 * Check if admin is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require authentication (middleware)
 */
function requireAuth()
{
    if (!isLoggedIn()) {
        header('Location: /restaurant/frontend/admin/login.php');
        exit;
    }
}

/**
 * Check if current user has a specific role
 */
function hasRole($role)
{
    if (!isLoggedIn()) return false;
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === $role;
}

/**
 * Check if current user has any of the allowed roles
 */
function hasAnyRole($allowedRoles)
{
    if (!isLoggedIn()) return false;
    if (!isset($_SESSION['admin_role'])) return false;
    return in_array($_SESSION['admin_role'], $allowedRoles);
}

/**
 * Redirect user to their designated landing page
 */
function redirectByRole()
{
    $role = $_SESSION['admin_role'] ?? '';
    switch ($role) {
        case 'chef':
        case 'kitchen_staff':
            header('Location: /restaurant/frontend/kitchen/dashboard.php');
            break;
        case 'manager':
        case 'admin':
        case 'super_admin':
            header('Location: /restaurant/frontend/admin/dashboard.php');
            break;
        default:
            header('Location: /restaurant/frontend/admin/login.php');
    }
    exit;
}

/**
 * Require any of the allowed roles or redirect (middleware)
 */
function requireAnyRole($allowedRoles)
{
    requireAuth();
    if (!hasAnyRole($allowedRoles)) {
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Access denied: Insufficient role']);
            exit;
        } else {
            redirectByRole();
        }
    }
}

/**
 * Require a specific role or redirect (middleware)
 */
function requireRole($role)
{
    requireAnyRole([$role]);
}

/**
 * Get current admin info with role and permissions
 */
function getCurrentAdmin()
{
    global $pdo;

    if (!isLoggedIn()) {
        return null;
    }

    // Fetch full user data with role
    $stmt = $pdo->prepare("
        SELECT u.*, r.name as role_name, r.display_name as role_display, r.level as role_level,
               rest.name as restaurant_name, rest.slug as restaurant_slug
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        LEFT JOIN restaurants rest ON u.restaurant_id = rest.id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['admin_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        logout();
        return null;
    }

    return $user;
}

/**
 * Get user's restaurant ID
 */
function getUserRestaurant()
{
    $user = getCurrentAdmin();
    return $user ? $user['restaurant_id'] : null;
}

/**
 * Create notification
 */
function createNotification($data)
{
    global $pdo;

    $restaurantId = isset($data['restaurant_id']) ? $data['restaurant_id'] : getUserRestaurant();
    $userId = isset($data['user_id']) ? $data['user_id'] : null;
    $title = $data['title'];
    $message = isset($data['message']) ? $data['message'] : null;
    $type = isset($data['type']) ? $data['type'] : 'system';
    $priority = isset($data['priority']) ? $data['priority'] : 'normal';
    $relatedOrderId = isset($data['related_order_id']) ? $data['related_order_id'] : null;

    $stmt = $pdo->prepare("
        INSERT INTO notifications (restaurant_id, user_id, title, message, type, priority, related_order_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    return $stmt->execute([
        $restaurantId,
        $userId,
        $title,
        $message,
        $type,
        $priority,
        $relatedOrderId
    ]);
}

/**
 * Notify users by role
 */
function notifyByRole($roleNames, $title, $message, $type = 'system', $relatedOrderId = null)
{
    global $pdo;

    if (!is_array($roleNames)) {
        $roleNames = [$roleNames];
    }

    $placeholders = str_repeat('?,', count($roleNames) - 1) . '?';

    $stmt = $pdo->prepare("
        SELECT u.id, u.restaurant_id
        FROM users u
        JOIN roles r ON u.role_id = r.id
        WHERE r.name IN ($placeholders) AND u.is_active = 1
    ");
    $stmt->execute($roleNames);
    $users = $stmt->fetchAll();

    foreach ($users as $user) {
        createNotification([
            'restaurant_id' => $user['restaurant_id'],
            'user_id' => $user['id'],
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'related_order_id' => $relatedOrderId
        ]);
    }
}
