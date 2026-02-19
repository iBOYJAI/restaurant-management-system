<?php

/**
 * Utility Functions
 */

/**
 * Sanitize input to prevent XSS
 */
function sanitizeInput($data)
{
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Send JSON response
 */
function jsonResponse($success, $data = null, $message = null, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');

    $response = ['success' => $success];

    if ($message !== null) {
        $response['message'] = $message;
    }

    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response);
    exit;
}

/**
 * Upload image file
 */
function uploadImage($file, $prefix = 'item')
{
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }

    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and WEBP allowed'];
    }

    // Validate file size
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'File too large. Maximum size is 5MB'];
    }

    // Create uploads directory if it doesn't exist
    if (!file_exists(UPLOADS_PATH)) {
        mkdir(UPLOADS_PATH, 0777, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . '_' . time() . '_' . uniqid() . '.' . $extension;
    $targetPath = UPLOADS_PATH . '/' . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'url' => 'assets/uploads/' . $filename
        ];
    }

    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * Delete uploaded image
 */
function deleteImage($imageUrl)
{
    if (empty($imageUrl)) {
        return false;
    }

    $filename = basename($imageUrl);
    $filePath = UPLOADS_PATH . '/' . $filename;

    if (file_exists($filePath)) {
        return unlink($filePath);
    }

    return false;
}

/**
 * Format currency
 */
function formatCurrency($amount)
{
    return '₹' . number_format($amount, 2);
}

/**
 * Generate order number
 */
function generateOrderNumber()
{
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Validate required fields
 */
function validateRequired($data, $requiredFields)
{
    $errors = [];

    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            continue;
        }

        $value = $data[$field];

        if (is_array($value)) {
            if (empty($value)) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        } elseif (empty(trim($value))) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }

    return $errors;
}
