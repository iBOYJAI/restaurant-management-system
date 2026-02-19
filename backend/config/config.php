<?php
// Prevent HTML error output in API responses
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Application Configuration
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Base paths
define('BASE_PATH', dirname(dirname(__DIR__)));
define('BACKEND_PATH', BASE_PATH . '/backend');
define('FRONTEND_PATH', BASE_PATH . '/frontend');
define('UPLOADS_PATH', FRONTEND_PATH . '/assets/uploads');

// Base URL (adjust if needed)
define('BASE_URL', 'http://localhost:8000');

// Upload settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']);

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
