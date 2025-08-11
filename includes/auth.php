<?php
// Secure session bootstrap
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? null) == 443);
    $params = [
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ];
    session_set_cookie_params($params);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

require_once __DIR__ . '/csrf.php';
csrf_start();

// Idle timeout: 30 minutes
if (isset($_SESSION['last_activity']) && time() - (int)$_SESSION['last_activity'] > 1800) {
    session_unset();
    session_destroy();
    session_start();
    csrf_start();
    $_SESSION['session_expired'] = true;
}
$_SESSION['last_activity'] = time();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

function require_admin() {
    if (!is_admin()) {
        header('Location: index.php');
        exit();
    }
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function current_username() {
    return $_SESSION['username'] ?? '';
}
?> 