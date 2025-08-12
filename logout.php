<?php
/**
 * Logout page
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Log the logout if user is logged in
if (is_logged_in()) {
    // Clear remember token cookie if exists
    if (isset($_COOKIE['remember_token'])) {
        $token_hash = hash('sha256', $_COOKIE['remember_token']);
        
        // Delete the session from database
        $stmt = $pdo->prepare("UPDATE user_sessions SET is_active = 0 WHERE session_id = ?");
        $stmt->execute([$token_hash]);
        
        // Delete the cookie
        setcookie('remember_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
    }
    
    // Log the logout action
    $user_id = current_user_id();
    $stmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action, table_name, record_id, old_data, ip_address, user_agent) 
                          VALUES (?, 'logout', 'users', ?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        $user_id,
        json_encode(['username' => current_username()]),
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}

// Logout the user
logout_user();

// Redirect to home page
header('Location: index.php');
exit();