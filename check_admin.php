<?php
/**
 * Check Admin Status
 * Run this to debug admin detection issues
 */

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>�� Admin Status Check</h2>";

// Check if logged in
if (!is_logged_in()) {
    echo "❌ Not logged in<br>";
    echo "<a href='login.php'>Go to Login</a>";
    exit;
}

$userId = current_user_id();
$username = current_username();

echo "✅ Logged in as: $username (ID: $userId)<br><br>";

// Check session data
echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre><br>";

// Check database admin status
echo "<h3>Database Admin Status:</h3>";
try {
    $stmt = $pdo->prepare("SELECT id, username, email, is_admin FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "Database is_admin value: " . $user['is_admin'] . " (" . gettype($user['is_admin']) . ")<br>";
        echo "Session is_admin value: " . (isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 'NOT SET') . " (" . gettype($_SESSION['is_admin'] ?? 'null') . ")<br>";
    } else {
        echo "❌ User not found in database<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test is_admin() function
echo "<h3>Function Results:</h3>";
echo "is_admin() result: " . (is_admin() ? 'TRUE' : 'FALSE') . "<br>";
echo "is_logged_in() result: " . (is_logged_in() ? 'TRUE' : 'FALSE') . "<br>";

// Fix admin status if needed
echo "<h3>Fix Admin Status:</h3>";
if (isset($_GET['fix']) && $_GET['fix'] === 'admin') {
    try {
        $stmt = $pdo->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
        $stmt->execute([$userId]);
        echo "✅ Admin status updated in database<br>";
        
        // Update session
        $_SESSION['is_admin'] = true;
        echo "✅ Session updated<br>";
        
        echo "<script>location.reload();</script>";
    } catch (Exception $e) {
        echo "❌ Error updating: " . $e->getMessage() . "<br>";
    }
} else {
    echo "<a href='?fix=admin' class='btn btn-primary'>Fix Admin Status</a><br>";
}

echo "<br><a href='index.php'>Back to Home</a>";
?>
