<?php

/**
 * Admin Login Handler
 */

require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/includes/auth.php';
require_once __DIR__ . '/../../backend/includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Invalid request method', 405);
}

$username = sanitizeInput($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    jsonResponse(false, null, 'Username and password are required', 400);
}

$user = login($username, $password);
if ($user) {
    jsonResponse(true, ['role' => $_SESSION['admin_role']], 'Login successful');
} else {
    jsonResponse(false, null, 'Invalid username or password', 401);
}
