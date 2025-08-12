<?php
ob_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';
require_once 'includes/i18n.php';
require_once 'includes/notifications.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit();
}

// Initialize variables
$username = '';
$email = '';
$errors = [];
$success = false;

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);
    
    // Validate username
    if (empty($username)) {
        $errors[] = __('Username is required');
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $errors[] = __('Username must be 3-20 characters and contain only letters, numbers, and underscores');
    } else {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $errors[] = __('Username already exists');
        }
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = __('Email is required');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = __('Invalid email format');
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errors[] = __('Email already exists');
        }
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = __('Password is required');
    } elseif (strlen($password) < 8) {
        $errors[] = __('Password must be at least 8 characters long');
    } elseif (!preg_match('/[A-Z]/', $password) || 
              !preg_match('/[a-z]/', $password) || 
              !preg_match('/[0-9]/', $password)) {
        $errors[] = __('Password must include at least one uppercase letter, one lowercase letter, and one number');
    }
    
    // Validate password confirmation
    if ($password !== $confirm_password) {
        $errors[] = __('Passwords do not match');
    }
    
    // Validate terms
    if (!$terms) {
        $errors[] = __('You must agree to the Terms and Conditions');
    }
    
    // If no errors, create user
    if (empty($errors)) {
        try {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Get current theme and language
            $theme = $_COOKIE['theme'] ?? 'auto';
            $language = current_language();
            
            // Create user
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, language, theme, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$username, $email, $password_hash, $language, $theme]);
            $user_id = $pdo->lastInsertId();
            
            // Log the registration
            $stmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action, table_name, record_id, new_data, ip_address, user_agent) 
                                  VALUES (?, 'user_created', 'users', ?, ?, ?, ?)");
            $stmt->execute([
                1, // Admin ID (system)
                $user_id,
                json_encode(['username' => $username]),
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            // Create welcome notification
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'system', ?, ?)");
            $stmt->execute([
                $user_id,
                __('Welcome'),
                __('Welcome to IdeaVote!')
            ]);
            
            // Set success flag
            $success = true;
            
            // Auto-login the user
            login_user($user_id, $username, false, $language, $theme);
            
            // Redirect to dashboard
            header('Location: dashboard.php');
            exit();
            
        } catch (PDOException $e) {
            $errors[] = __('Registration failed') . ': ' . $e->getMessage();
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
    <title><?= __('Register') ?> - IdeaVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        .register-container {
            max-width: 550px;
            margin: 2rem auto;
        }
        
        .register-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(var(--shadow-rgb), 0.1);
            border: 1px solid var(--border-color);
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-header h1 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .register-header p {
            color: var(--text-muted);
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
            color: var(--text-muted);
            z-index: 10;
        }
        
        .password-strength {
            height: 5px;
            border-radius: 5px;
            margin-top: 8px;
            transition: all 0.3s ease;
        }
        
        .password-strength-text {
            font-size: 0.8rem;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container register-container">
        <div class="register-card">
            <div class="register-header">
                <h1 class="gold-gradient"><?= __('Create Account') ?></h1>
                <p><?= __('Join IdeaVote to share and vote on ideas') ?></p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" novalidate>
                <?= csrf_field(); ?>
                
                <div class="mb-3">
                    <label for="username" class="form-label"><?= __('Username') ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="username" name="username" required 
                               value="<?= htmlspecialchars($username) ?>" autocomplete="username">
                    </div>
                    <div class="form-text"><?= __('3-20 characters, letters, numbers, and underscores only') ?></div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label"><?= __('Email Address') ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required 
                               value="<?= htmlspecialchars($email) ?>" autocomplete="email">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label"><?= __('Password') ?></label>
                    <div class="input-group password-field">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required 
                               autocomplete="new-password" oninput="checkPasswordStrength()">
                        <span class="password-toggle" onclick="togglePasswordVisibility('password')">
                            <i class="bi bi-eye"></i>
                        </span>
                    </div>
                    <div class="password-strength" id="passwordStrength"></div>
                    <div class="password-strength-text" id="passwordStrengthText"></div>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="form-label"><?= __('Confirm Password') ?></label>
                    <div class="input-group password-field">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required 
                               autocomplete="new-password" oninput="checkPasswordMatch()">
                        <span class="password-toggle" onclick="togglePasswordVisibility('confirm_password')">
                            <i class="bi bi-eye"></i>
                        </span>
                    </div>
                    <div class="form-text" id="passwordMatchText"></div>
                </div>
                
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            <?= __('I agree to the') ?> 
                            <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal"><?= __('Terms and Conditions') ?></a>
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-gold w-100 py-2 mb-3">
                    <?= __('Register') ?>
                </button>
                
                <div class="text-center">
                    <p class="mb-0">
                        <?= __('Already have an account?') ?> 
                        <a href="login.php" class="text-decoration-none"><?= __('Login') ?></a>
                    </p>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel"><?= __('Terms and Conditions') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5>1. <?= __('Acceptance of Terms') ?></h5>
                    <p><?= __('By accessing and using IdeaVote, you agree to be bound by these Terms and Conditions.') ?></p>
                    
                    <h5>2. <?= __('User Accounts') ?></h5>
                    <p><?= __('You are responsible for maintaining the confidentiality of your account and password.') ?></p>
                    
                    <h5>3. <?= __('User Content') ?></h5>
                    <p><?= __('You retain ownership of content you submit, but grant us a license to use, modify, and display it.') ?></p>
                    
                    <h5>4. <?= __('Prohibited Conduct') ?></h5>
                    <p><?= __('You agree not to use IdeaVote for any illegal purposes or to violate any laws.') ?></p>
                    
                    <h5>5. <?= __('Privacy') ?></h5>
                    <p><?= __('Your use of IdeaVote is also governed by our Privacy Policy.') ?></p>
                    
                    <h5>6. <?= __('Termination') ?></h5>
                    <p><?= __('We reserve the right to terminate or suspend your account at our sole discretion.') ?></p>
                    
                    <h5>7. <?= __('Changes to Terms') ?></h5>
                    <p><?= __('We reserve the right to modify these terms at any time.') ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('Close') ?></button>
                    <button type="button" class="btn btn-gold" data-bs-dismiss="modal" onclick="document.getElementById('terms').checked = true;">
                        <?= __('I Agree') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/theme.js"></script>
    <script>
        function togglePasswordVisibility(id) {
            const passwordInput = document.getElementById(id);
            const passwordToggle = passwordInput.nextElementSibling.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordToggle.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
        
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('passwordStrengthText');
            
            // Reset
            strengthBar.style.width = '0%';
            strengthBar.style.backgroundColor = '';
            strengthText.textContent = '';
            
            if (password.length === 0) {
                return;
            }
            
            // Calculate strength
            let strength = 0;
            const patterns = [
                { regex: /.{8,}/, score: 10 },           // Min 8 chars
                { regex: /[A-Z]/, score: 10 },           // Uppercase
                { regex: /[a-z]/, score: 10 },           // Lowercase
                { regex: /[0-9]/, score: 10 },           // Numbers
                { regex: /[^A-Za-z0-9]/, score: 10 },    // Special chars
                { regex: /.{12,}/, score: 10 },          // 12+ chars
                { regex: /[^A-Za-z0-9]{2,}/, score: 10 } // 2+ special chars
            ];
            
            patterns.forEach(pattern => {
                if (pattern.regex.test(password)) {
                    strength += pattern.score;
                }
            });
            
            // Set visual indicators
            if (strength < 30) {
                strengthBar.style.width = '25%';
                strengthBar.style.backgroundColor = '#dc3545';
                strengthText.textContent = '<?= __('Weak') ?>';
                strengthText.style.color = '#dc3545';
            } else if (strength < 50) {
                strengthBar.style.width = '50%';
                strengthBar.style.backgroundColor = '#ffc107';
                strengthText.textContent = '<?= __('Fair') ?>';
                strengthText.style.color = '#ffc107';
            } else if (strength < 70) {
                strengthBar.style.width = '75%';
                strengthBar.style.backgroundColor = '#28a745';
                strengthText.textContent = '<?= __('Good') ?>';
                strengthText.style.color = '#28a745';
            } else {
                strengthBar.style.width = '100%';
                strengthBar.style.backgroundColor = '#198754';
                strengthText.textContent = '<?= __('Strong') ?>';
                strengthText.style.color = '#198754';
            }
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchText = document.getElementById('passwordMatchText');
            
            if (confirmPassword.length === 0) {
                matchText.textContent = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchText.textContent = '<?= __('Passwords match') ?>';
                matchText.style.color = '#198754';
            } else {
                matchText.textContent = '<?= __('Passwords do not match') ?>';
                matchText.style.color = '#dc3545';
            }
        }
    </script>
</body>
</html>