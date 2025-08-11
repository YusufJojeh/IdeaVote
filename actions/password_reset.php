<?php
session_start();
include '../includes/config.php';
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/auth.php';
include '../includes/csrf.php';
include '../includes/i18n.php';

$errors = [];
$success = false;
$step = 'request'; // 'request', 'reset', 'success'

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    if (csrf_verify()) {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email) || !validate_email($email)) {
            $errors[] = 'Please enter a valid email address.';
        } else {
            // Check if user exists
            $sql = "SELECT id, username FROM users WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() === 0) {
                $errors[] = 'No account found with that email address.';
            } else {
                $user = $stmt->fetch();
                
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
                
                // Store reset token
                $sql = "INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$email, $token, $expires]);
                
                // In a real application, you would send an email here
                // For demo purposes, we'll show the reset link
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/password_reset.php?token=" . $token;
                $success = true;
            }
        }
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    if (csrf_verify()) {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($token)) {
            $errors[] = 'Invalid reset token.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match.';
        } else {
            // Verify token
            $sql = "SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$token]);
            
            if ($stmt->rowCount() === 0) {
                $errors[] = 'Invalid or expired reset token.';
            } else {
                $reset = $stmt->fetch();
                
                // Update password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ? WHERE email = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$hashedPassword, $reset['email']]);
                
                // Mark token as used
                $sql = "UPDATE password_resets SET used = 1 WHERE token = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$token]);
                
                $step = 'success';
            }
        }
    }
}

// Check if we have a token in URL
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    // Verify token
    $sql = "SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token]);
    
    if ($stmt->rowCount() === 0) {
        $errors[] = 'Invalid or expired reset token.';
    } else {
        $step = 'reset';
    }
}

include '../includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isResetPage ? 'Reset Password' : 'Forgot Password' ?> - IdeaVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .btn-primary { background: linear-gradient(45deg, #667eea, #764ba2); border: none; }
        .form-control { border-radius: 10px; border: 2px solid #e9ecef; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h3 class="text-primary">
                                <i class="fas fa-lock me-2"></i>
                                <?= $isResetPage ? 'Reset Password' : 'Forgot Password' ?>
                            </h3>
                            <p class="text-muted">
                                <?= $isResetPage ? 'Enter your new password below' : 'Enter your email to receive a reset link' ?>
                            </p>
                        </div>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <?php if ($isResetPage): ?>
                            <!-- Reset Password Form -->
                            <form method="POST" id="resetForm">
                                <input type="hidden" name="action" value="reset">
                                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                                <?= csrf_field() ?>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirm_password" required minlength="6">
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                            </form>
                        <?php else: ?>
                            <!-- Request Reset Form -->
                            <form method="POST" id="requestForm">
                                <input type="hidden" name="action" value="request">
                                <?= csrf_field() ?>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                            </form>
                        <?php endif; ?>
                        
                        <div class="text-center mt-3">
                            <a href="/login.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Password confirmation validation
        document.getElementById('resetForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }
        });
        
        // Form submission feedback
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const button = this.querySelector('button[type="submit"]');
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            });
        });
    </script>
</body>
</html>
