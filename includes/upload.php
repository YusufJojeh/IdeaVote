<?php
/**
 * File upload handling functions
 */

// Allowed file types
$allowed_image_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

// Maximum file size (in bytes) - 2MB
$max_file_size = 2 * 1024 * 1024;

// Upload directories
$upload_dirs = [
    'users' => 'uploads/users/',
    'ideas' => 'uploads/ideas/',
    'categories' => 'uploads/categories/'
];

/**
 * Upload an image file
 * 
 * @param array $file $_FILES array element
 * @param string $type Upload type (users, ideas, categories)
 * @return array Result with status and path or error
 */
function upload_image($file, $type = 'ideas') {
    global $allowed_image_types, $max_file_size, $upload_dirs;
    
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'File upload failed'];
    }
    
    // Check file size
    if ($file['size'] > $max_file_size) {
        return ['ok' => false, 'error' => 'File is too large (max 2MB)'];
    }
    
    // Check file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    
    if (!in_array($mime_type, $allowed_image_types)) {
        return ['ok' => false, 'error' => 'Invalid file type. Allowed types: JPEG, PNG, GIF, WEBP'];
    }
    
    // Get upload directory
    $upload_dir = $upload_dirs[$type] ?? $upload_dirs['ideas'];
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['ok' => true, 'path' => $filepath];
    } else {
        return ['ok' => false, 'error' => 'Failed to move uploaded file'];
    }
}

/**
 * Delete an uploaded file
 * 
 * @param string $filepath Path to the file
 * @return bool True if deleted, false otherwise
 */
function delete_uploaded_file($filepath) {
    if (file_exists($filepath) && is_file($filepath)) {
        return unlink($filepath);
    }
    return false;
}