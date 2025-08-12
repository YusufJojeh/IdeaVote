<?php
/**
 * Quick Password Reset Script
 * 
 * This script resets ALL user passwords to "password" with proper hashing.
 * WARNING: This will overwrite ALL existing passwords!
 * 
 * Usage: Just run this file in your browser - http://localhost/IdeaVote/reset_passwords.php
 * Security: DELETE THIS FILE AFTER USE!
 */

// Include database connection
require_once 'includes/config.php';
require_once 'includes/db.php';

// Hash the password "password"
$hashed_password = password_hash('password', PASSWORD_DEFAULT);

try {
    // Update all users' passwords
    $stmt = $pdo->prepare("UPDATE users SET password = ?");
    $result = $stmt->execute([$hashed_password]);
    
    if ($result) {
        $affected_rows = $stmt->rowCount();
        echo "✅ SUCCESS: Reset passwords for {$affected_rows} user(s) to 'password'";
        echo "<br><br>All users can now login with their username and password 'password'";
        echo "<br><br>⚠️ DELETE THIS FILE NOW FOR SECURITY!";
    } else {
        echo "❌ ERROR: Failed to update passwords";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
?>
