<?php
/**
 * Authentication functions
 */

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is an admin
 * 
 * @return bool True if admin, false otherwise
 */
function is_admin() {
    // Check if user is logged in first
    if (!is_logged_in()) {
        return false;
    }
    
    // Check session admin status
    if (isset($_SESSION['is_admin'])) {
        return $_SESSION['is_admin'] == 1 || $_SESSION['is_admin'] === true;
    }
    
    // If session doesn't have admin status, check database
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Update session with correct admin status
            $_SESSION['is_admin'] = (bool)$user['is_admin'];
            return (bool)$user['is_admin'];
        }
    } catch (Exception $e) {
        // Log error if needed
        return false;
    }
    
    return false;
}

/**
 * Get current user ID
 * 
 * @return int|null User ID if logged in, null otherwise
 */
function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 * 
 * @return string|null Username if logged in, null otherwise
 */
function current_username() {
    return $_SESSION['username'] ?? null;
}

/**
 * Require user to be logged in
 * Redirects to login page if not logged in
 * 
 * @param string $redirect_url URL to redirect to after login
 * @return void
 */
function require_login($redirect_url = null) {
    if (!is_logged_in()) {
        $redirect = $redirect_url ?? $_SERVER['REQUEST_URI'];
        $_SESSION['login_redirect'] = $redirect;
        header('Location: login.php');
        exit();
    }
}

/**
 * Require user to be an admin
 * Redirects to home page if not admin
 * 
 * @return void
 */
function require_admin() {
    if (!is_admin()) {
        header('Location: index.php');
        exit();
    }
}

/**
 * Log user in
 * 
 * @param int $user_id User ID
 * @param string $username Username
 * @param bool $is_admin Whether user is admin
 * @param string $language User's language preference
 * @param string $theme User's theme preference
 * @return void
 */
function login_user($user_id, $username, $is_admin = false, $language = 'en', $theme = 'auto') {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['is_admin'] = (bool)$is_admin; // Convert to boolean
    $_SESSION['language'] = $language;
    $_SESSION['theme'] = $theme;
    $_SESSION['last_activity'] = time();
}

/**
 * Log user out
 * 
 * @return void
 */
function logout_user() {
    // Clear all session data
    $_SESSION = [];
    
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Check if user session is still valid
 * 
 * @return bool True if session is valid, false otherwise
 */
function is_session_valid() {
    if (!is_logged_in()) {
        return false;
    }
    
    // Check if session has expired (30 minutes)
    $timeout = 30 * 60; // 30 minutes
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        logout_user();
        return false;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    return true;
}
?>