<?php

/**
 * Admin Logout
 */

require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/includes/auth.php';

logout();
header('Location: login.php');
exit;
