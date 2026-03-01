<?php
/**
 * Fix Waiter login: ensure user "waiter" exists and can log in with password "password".
 * Run once from browser: http://localhost/restaurant/database/fix_waiter_password.php
 * Or CLI: php database/fix_waiter_password.php
 */
require_once __DIR__ . '/../backend/config/config.php';
require_once __DIR__ . '/../backend/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

if (empty($pdo)) {
    echo "Database not connected. Check backend/config/database.php and that MySQL is running.\n";
    exit(1);
}

$password = 'password';
$hash = password_hash($password, PASSWORD_BCRYPT);

// Get role_id for 'waiter'
$roleStmt = $pdo->query("SELECT id FROM roles WHERE name = 'waiter' LIMIT 1");
$roleRow = $roleStmt ? $roleStmt->fetch(PDO::FETCH_ASSOC) : null;
if (!$roleRow) {
    echo "Role 'waiter' not found. Run database/complete-setup.sql first.\n";
    exit(1);
}
$waiter_role_id = (int) $roleRow['id'];

// Check if waiter user exists
$userStmt = $pdo->prepare("SELECT id, username, is_active FROM users WHERE username = ?");
$userStmt->execute(['waiter']);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $pdo->prepare("UPDATE users SET password_hash = ?, is_active = 1 WHERE username = ?")
        ->execute([$hash, 'waiter']);
    echo "Waiter user updated. Log in with: username = waiter, password = password\n";
    echo "After login you are redirected to: frontend/waiter/dashboard.php\n";
} else {
    $pdo->prepare(
        "INSERT INTO users (restaurant_id, role_id, username, password_hash, full_name, phone, is_active) " .
        "VALUES (1, ?, 'waiter', ?, 'Ramu Waiter', '+91-7777777777', 1)"
    )->execute([$waiter_role_id, $hash]);
    echo "Waiter user created. Log in with: username = waiter, password = password\n";
    echo "After login you are redirected to: frontend/waiter/dashboard.php\n";
}
