<?php
/**
 * CSRF protection functions
 */

/**
 * Generate a CSRF token and store it in the session
 * 
 * @return string CSRF token
 */
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate a CSRF form field
 * 
 * @return string HTML input field with CSRF token
 */
function csrf_field() {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Verify CSRF token
 * 
 * @return bool True if token is valid, false otherwise
 */
function csrf_verify() {
    $token = $_POST['csrf_token'] ?? '';
    $valid = isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    
    // Regenerate token after verification for added security
    if ($valid) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $valid;
}