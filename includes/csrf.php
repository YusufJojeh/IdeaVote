<?php
// CSRF protection utilities (procedural)

if (!function_exists('csrf_start')) {
    function csrf_start(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            // The session should already be started by auth bootstrap
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return $_SESSION['csrf_token'] ?? '';
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        $token = htmlspecialchars(csrf_token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify(): bool {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true; // Only verify POST
        }
        $provided = $_POST['csrf_token'] ?? '';
        $current = $_SESSION['csrf_token'] ?? '';
        $ok = is_string($provided) && is_string($current) && hash_equals($current, $provided);
        if ($ok) {
            // Rotate token on success to limit replay
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            return true;
        }
        return false;
    }
}

?>


