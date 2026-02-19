<?php
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/includes/auth.php';

// Ensure any staff role can access this redirect
requireAnyRole(['super_admin', 'admin', 'chef', 'kitchen_staff', 'manager']);

// Redirect to the unified kitchen view
header('Location: ../kitchen/dashboard.php');
exit;
