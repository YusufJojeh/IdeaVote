<?php
// Web only: Reset ALL users' passwords to a provided new password (hashed)
// Behavior:
// - GET: show a minimal form to enter the new password
// - POST: CSRF-checked; update all users' password to password_hash(new_password), and return plain "success"

// Ensure errors in mysqli throw exceptions for easier handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Disallow CLI execution
if (php_sapi_name() === 'cli') {
    echo "This tool is browser-only." . PHP_EOL;
    exit(1);
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/csrf.php';
csrf_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'error: csrf';
        exit();
    }

    $newPassword = (string)($_POST['new_password'] ?? '');
    if (strlen($newPassword) < 6) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'error: password too short';
        exit();
    }

    try {
        mysqli_begin_transaction($conn);
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, 'UPDATE users SET password = ?');
        mysqli_stmt_bind_param($stmt, 's', $hash);
        mysqli_stmt_execute($stmt);
        mysqli_commit($conn);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'success';
        exit();
    } catch (Throwable $e) {
        if (isset($conn)) {
            mysqli_rollback($conn);
        }
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'error: ' . $e->getMessage();
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset All Passwords</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-3">Reset All Passwords</h4>
                    <p class="text-muted">Enter the new password to set for all users. This will overwrite existing passwords.</p>
                    <form method="POST">
                        <?= csrf_field(); ?>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New password for all users</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-danger">Reset All Passwords</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>