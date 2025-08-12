<?php
/**
 * Reset All Passwords to "password"
 * 
 * This script resets all user passwords to "password" with proper hashing.
 * WARNING: This will overwrite ALL existing passwords!
 * 
 * Usage: Access this file in your browser and click the reset button.
 * Security: This should be removed after use in production.
 */

// Prevent CLI execution
if (php_sapi_name() === 'cli') {
    echo "This script is browser-only for security reasons." . PHP_EOL;
    exit(1);
}

// Include database connection
require_once 'includes/config.php';
require_once 'includes/db.php';

// Simple security check - you can modify this
$admin_check = true; // Set to false to disable admin check

if ($admin_check) {
    // Check if user is logged in and is admin
    session_start();
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        die('Access denied. Admin privileges required.');
    }
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_passwords'])) {
    try {
        // Hash the password "password"
        $hashed_password = password_hash('password', PASSWORD_DEFAULT);
        
        // Update all users' passwords
        $stmt = $pdo->prepare("UPDATE users SET password = ?");
        $result = $stmt->execute([$hashed_password]);
        
        if ($result) {
            // Get the number of affected rows
            $affected_rows = $stmt->rowCount();
            $message = "Successfully reset passwords for {$affected_rows} user(s) to 'password'";
        } else {
            $error = "Failed to update passwords";
        }
        
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get current user count for display
$user_count = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    $user_count = $result['count'];
} catch (PDOException $e) {
    $error = "Error getting user count: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset All Passwords - IdeaVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --gold:#FFD700; --gold-2:#FFEF8E;
            --bg:#ffffff; --text:#181818; --muted:#555d68; --card:#ffffff; --border:#e5e7eb;
        }
        body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:var(--bg);color:var(--text);}
        .btn-gold{background:linear-gradient(90deg,var(--gold),var(--gold-2));color:#111;border:0;font-weight:700}
        .btn-gold:hover{filter:brightness(1.05);transform:translateY(-1px)}
        .card{background:var(--card);border:1px solid var(--border);border-radius:1rem;box-shadow:0 10px 30px rgba(0,0,0,.1)}
        .warning-box{background:linear-gradient(135deg, rgba(255,193,7,.1), rgba(255,193,7,.05));border:2px solid #ffc107;border-radius:1rem;padding:1.5rem}
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="bi bi-shield-lock text-warning" style="font-size: 3rem;"></i>
                            <h2 class="mt-3 mb-2">Reset All Passwords</h2>
                            <p class="text-muted">Reset all user passwords to "password"</p>
                        </div>

                        <!-- Warning Box -->
                        <div class="warning-box mb-4">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-3" style="font-size: 1.5rem; margin-top: 0.2rem;"></i>
                                <div>
                                    <h5 class="text-warning mb-2">⚠️ WARNING</h5>
                                    <p class="mb-2"><strong>This action will:</strong></p>
                                    <ul class="mb-0">
                                        <li>Reset ALL user passwords to "password"</li>
                                        <li>Overwrite existing passwords permanently</li>
                                        <li>Affect <strong><?= $user_count ?> user(s)</strong> in the database</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Status Messages -->
                        <?php if ($message): ?>
                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Current Stats -->
                        <div class="row mb-4">
                            <div class="col-6">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="h4 mb-0 text-primary"><?= $user_count ?></div>
                                    <small class="text-muted">Total Users</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="h4 mb-0 text-success">password</div>
                                    <small class="text-muted">New Password</small>
                                </div>
                            </div>
                        </div>

                        <!-- Reset Form -->
                        <form method="POST" onsubmit="return confirmReset()">
                            <div class="d-grid gap-2">
                                <button type="submit" name="reset_passwords" class="btn btn-danger btn-lg">
                                    <i class="bi bi-arrow-clockwise me-2"></i>
                                    Reset All Passwords to "password"
                                </button>
                                <a href="admin.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>
                                    Back to Admin
                                </a>
                            </div>
                        </form>

                        <!-- Instructions -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6><i class="bi bi-info-circle me-2"></i>Instructions:</h6>
                            <ol class="mb-0 small">
                                <li>Click the reset button above</li>
                                <li>All users can now login with username and password "password"</li>
                                <li>Users should change their passwords after first login</li>
                                <li><strong>Delete this file after use for security!</strong></li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="text-center mt-4">
                    <small class="text-muted">
                        <i class="bi bi-shield-check me-1"></i>
                        This tool uses secure password hashing with PASSWORD_DEFAULT
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmReset() {
            return confirm(
                '⚠️ WARNING: This will reset ALL user passwords to "password".\n\n' +
                'This action cannot be undone!\n\n' +
                'Are you absolutely sure you want to continue?'
            );
        }

        // Auto-refresh page after successful reset to show updated count
        <?php if ($message): ?>
        setTimeout(function() {
            location.reload();
        }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
