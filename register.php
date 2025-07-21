<?php
ob_start();
include 'includes/navbar.php';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!validate_username($username)) {
        $errors[] = 'Please enter a valid username (3-30 letters, numbers, or underscores).';
    }
    if (!validate_email($email)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username=? OR email=?");
    mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $errors[] = 'Username or email already exists.';
    }
    mysqli_stmt_close($stmt);

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $avatar_url = 'https://api.dicebear.com/6.x/initials/svg?seed=' . urlencode($username);
        $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password, image_url) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssss', $username, $email, $hash, $avatar_url);
        if (mysqli_stmt_execute($stmt)) {
            $success = true;
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - IdeaVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Inter', 'Segoe UI', Arial, sans-serif; }
        .navbar-lux { background: #fff !important; box-shadow: 0 8px 32px 0 rgba(24,24,24,0.06); border-bottom: 1px solid #eee; }
        .gold-gradient { background: linear-gradient(90deg, #FFD700 0%, #FFEF8E 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; color: #FFD700; }
        .register-glass { background: rgba(255,255,255,0.95); box-shadow: 0 8px 32px 0 rgba(24,24,24,0.08); border-radius: 32px; border: 1.5px solid #eee; padding: 2.5rem 2rem; }
        .btn-gold { background: linear-gradient(90deg, #FFD700 0%, #FFEF8E 100%); color: #fff; font-weight: bold; border: none; box-shadow: 0 2px 12px rgba(255,215,0,0.10); }
        .btn-gold:hover { background: linear-gradient(90deg, #FFEF8E 0%, #FFD700 100%); color: #181818; }
        .form-label { color: #181818; font-weight: 500; }
        .form-control { background: #fff; border-radius: 12px; border: 1.5px solid #eee; }
        .icon-gold { color: #FFD700; }
        .link-gold { color: #FFD700; text-decoration: underline; }
        .link-gold:hover { color: #FFEF8E; }
    </style>
</head>
<body>
    <?php
    // The navbar is now included at the very top of the file
    ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="register-glass shadow-lg">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-plus-fill icon-gold fs-1"></i>
                        <h2 class="fw-bold gold-gradient">Create Account</h2>
                    </div>
                    <?php if ($success): ?>
                        <div class="alert alert-success text-center">Registration successful! <a href="login.php" class="link-gold">Login here</a>.</div>
                    <?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form method="POST" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-gold w-100 cta-btn">Register</button>
                    </form>
                    <div class="text-center mt-3">
                        Already have an account? <a href="login.php" class="link-gold">Login here</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 