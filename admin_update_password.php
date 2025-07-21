<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// Find admin user
$sql = "SELECT id FROM users WHERE is_admin=1 LIMIT 1";
$res = mysqli_query($conn, $sql);
$admin = mysqli_fetch_assoc($res);
if ($admin) {
    $admin_id = $admin['id'];
    $new_password = 'ab4445';
    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'si', $hash, $admin_id);
    if (mysqli_stmt_execute($stmt)) {
        $msg = 'Admin password updated and hashed successfully!';
    } else {
        $msg = 'Failed to update admin password.';
    }
    mysqli_stmt_close($stmt);
} else {
    $msg = 'No admin user found.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Admin Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height:100vh;">
    <div class="card shadow p-4">
        <h3 class="mb-3">Admin Password Update</h3>
        <div class="alert alert-info"> <?= htmlspecialchars($msg) ?> </div>
        <a href="login.php" class="btn btn-primary">Back to Login</a>
    </div>
</body>
</html> 