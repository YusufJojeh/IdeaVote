<?php
ob_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';
require_once 'includes/i18n.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit();
}

// Initialize variables
$username = '';
$error = '';
$remember = false;

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = __('Please enter both username/email and password');
    } else {
        try {
            // Attempt to authenticate by username OR email
            $stmt = $pdo->prepare("SELECT id, username, email, password, is_admin, language, theme FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Log successful login
                login_user($user['id'], $user['username'], $user['is_admin'], $user['language'], $user['theme']);
                
                // Create persistent login if requested
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $token_hash = hash('sha256', $token);
                    $expires = date('Y-m-d H:i:s', time() + 30 * 24 * 60 * 60); // 30 days
                    
                    // Try to insert session token (ignore if table doesn't exist)
                    try {
                        $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, session_id, device_info, ip_address, user_agent) 
                                              VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $user['id'],
                            $token_hash,
                            json_encode(['remember' => true]),
                            $_SERVER['REMOTE_ADDR'],
                            $_SERVER['HTTP_USER_AGENT'] ?? null
                        ]);
                        
                        // Set cookie
                        setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/', '', isset($_SERVER['HTTPS']), true);
                    } catch (Exception $e) {
                        // Ignore session table errors
                    }
                }
                
                // Redirect to intended page or dashboard
                $redirect = $_SESSION['login_redirect'] ?? 'index.php';
                unset($_SESSION['login_redirect']);
                header("Location: $redirect");
                exit();
            } else {
                $error = __('Invalid username/email or password');
            }
        } catch (Exception $e) {
            $error = __('Login error. Please try again.');
        }
    }
}

include 'includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="<?= current_language() ?>" dir="<?= lang_dir() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('Login') ?> - IdeaVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        :root{
            --gold:#FFD700; --gold-2:#FFEF8E;
            --bg:#ffffff; --text:#181818; --muted:#555d68; --card:#ffffff; --border:#e5e7eb;
        }
        body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:var(--bg);color:var(--text);}
        .btn-gold{background:linear-gradient(90deg,var(--gold),var(--gold-2));color:#111;border:0;font-weight:700}
        .btn-gold:hover{filter:brightness(1.05);transform:translateY(-1px)}
        .card{background:var(--card);border:1px solid var(--border);border-radius:1.5rem;box-shadow:0 10px 30px rgba(0,0,0,.1)}
        .gold-gradient{background:linear-gradient(90deg,var(--gold),var(--gold-2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;color:var(--gold)}
        
        .login-container {
            max-width: 450px;
            margin: 2rem auto;
        }
        
        .login-card {
            background: var(--card);
            border-radius: 1.5rem;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,.1);
            border: 1px solid var(--border);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h1 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: var(--muted);
        }
        
        .password-field {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--muted);
            z-index: 10;
        }
        
        .form-control {
            border: 2px solid var(--border);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .form-control:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(255,215,0,.1);
        }
        
        .input-group-text {
            background: var(--card);
            border: 2px solid var(--border);
            border-right: none;
            border-radius: 0.75rem 0 0 0.75rem;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 0.75rem 0.75rem 0;
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="login-card">
            <div class="login-header">
                <h1 class="gold-gradient"><?= __('Welcome Back') ?></h1>
                <p><?= __('Sign in to continue to IdeaVote') ?></p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" novalidate>
                <?= csrf_field(); ?>
                
                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold"><?= __('Username or Email') ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="username" name="username" required 
                               value="<?= htmlspecialchars($username) ?>" autocomplete="username"
                               placeholder="<?= __('Enter your username or email') ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold"><?= __('Password') ?></label>
                    <div class="input-group password-field">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required 
                               autocomplete="current-password" placeholder="<?= __('Enter your password') ?>">
                        <span class="password-toggle" onclick="togglePasswordVisibility()">
                            <i class="bi bi-eye"></i>
                        </span>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" <?= $remember ? 'checked' : '' ?>>
                        <label class="form-check-label" for="remember">
                            <?= __('Remember Me') ?>
                        </label>
                    </div>
                    <a href="password_reset.php" class="text-decoration-none text-muted"><?= __('Forgot Password?') ?></a>
                </div>
                
                <button type="submit" class="btn btn-gold w-100 py-3 mb-3 fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-2"></i><?= __('Login') ?>
                </button>
                
                <div class="text-center">
                    <p class="mb-0 text-muted">
                        <?= __('Don\'t have an account?') ?> 
                        <a href="register.php" class="text-decoration-none fw-semibold"><?= __('Register') ?></a>
                    </p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/theme.js"></script>
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordToggle.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
        
        // Auto-focus username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>