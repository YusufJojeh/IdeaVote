<?php
ob_start();
include 'includes/navbar.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';

$errors = [];
if (!empty($_SESSION['session_expired'])) {
    $errors[] = 'Your session has expired. Please log in again.';
    unset($_SESSION['session_expired']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'CSRF verification failed.';
    } else {
        // Rate limiting: block if > 5 attempts in last 15 minutes for IP or username
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $ipBin = get_client_ip_binary();
        $block = false;
        $since = date('Y-m-d H:i:s', time() - 15 * 60);
        $sql = "SELECT COUNT(*) as c FROM login_attempts WHERE occurred_at >= ? AND (ip = ? OR username = ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$since, $ipBin, $username]);
        $row = $stmt->fetch();
        if (($row['c'] ?? 0) >= 5) {
            $block = true;
        }
        if ($block) {
            $errors[] = 'Too many login attempts. Please try again later.';
        } else {
            if (!$username || !$password) {
                $errors[] = 'Please enter both username and password.';
            } else {
                $stmt = $pdo->prepare("SELECT id, username, password, is_admin FROM users WHERE username=? OR email=?");
                $stmt->execute([$username, $username]);
                if ($row = $stmt->fetch()) {
                    if (password_verify($password, $row['password'])) {
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $row['id'];
                        $_SESSION['username'] = $row['username'];
                        $_SESSION['is_admin'] = $row['is_admin'];
                        $_SESSION['last_activity'] = time();
                        header('Location: dashboard.php');
                        exit();
                    } else {
                        $errors[] = 'Incorrect password.';
                        $stmt2 = $pdo->prepare("INSERT INTO login_attempts (ip, username, occurred_at) VALUES (?, ?, NOW())");
                        $stmt2->execute([$ipBin, $username]);
                    }
                } else {
                    $errors[] = 'User not found.';
                    $stmt2 = $pdo->prepare("INSERT INTO login_attempts (ip, username, occurred_at) VALUES (?, ?, NOW())");
                    $stmt2->execute([$ipBin, $username]);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IdeaVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Inter', 'Segoe UI', Arial, sans-serif; }
        .navbar-lux { background: #fff !important; box-shadow: 0 8px 32px 0 rgba(24,24,24,0.06); border-bottom: 1px solid #eee; }
        .gold-gradient { background: linear-gradient(90deg, #FFD700 0%, #FFEF8E 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; color: #FFD700; }
        .login-glass { background: rgba(255,255,255,0.95); box-shadow: 0 8px 32px 0 rgba(24,24,24,0.08); border-radius: 32px; border: 1.5px solid #eee; padding: 2.5rem 2rem; }
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
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-glass shadow-lg">
                    <div class="text-center mb-4">
                        <i class="bi bi-box-arrow-in-right icon-gold fs-1"></i>
                        <h2 class="fw-bold gold-gradient">Login</h2>
                    </div>
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
                        <?= csrf_field(); ?>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-gold w-100 cta-btn">Login</button>
                    </form>
                    <div class="text-center mt-3">
                        Don't have an account? <a href="register.php" class="link-gold">Register here</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 