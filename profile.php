<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic includes
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = [];
$edit_errors = [];
$edit_success = false;

// Get user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: login.php');
        exit();
    }
} catch (Exception $e) {
    $edit_errors[] = 'Database error: ' . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $language = $_POST['language'] ?? 'en';
    $theme = $_POST['theme'] ?? 'auto';
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    
    // Validation
    if (empty($username)) {
        $edit_errors[] = 'Username is required.';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $edit_errors[] = 'Valid email is required.';
    }
    
    // Check if username/email already exists
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $user_id]);
        if ($stmt->rowCount() > 0) {
            $edit_errors[] = 'Username or email already exists.';
        }
    } catch (Exception $e) {
        $edit_errors[] = 'Database error: ' . $e->getMessage();
    }
    
    // Update user if no errors
    if (empty($edit_errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET username = ?, email = ?, bio = ?, language = ?, theme = ?, email_notifications = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([$username, $email, $bio, $language, $theme, $email_notifications, $user_id])) {
                $edit_success = true;
                $user = array_merge($user, [
                    'username' => $username,
                    'email' => $email,
                    'bio' => $bio,
                    'language' => $language,
                    'theme' => $theme,
                    'email_notifications' => $email_notifications
                ]);
            } else {
                $edit_errors[] = 'Failed to update profile.';
            }
        } catch (Exception $e) {
            $edit_errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - IdeaVote</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/app.css" rel="stylesheet">
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    <style>
        body {
            background: var(--bg);
            color: var(--text);
        }
        
        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid var(--gold);
            object-fit: cover;
            margin-bottom: 1rem;
        }
        
        .form-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid var(--border);
            padding: 12px 16px;
            background: var(--card);
            color: var(--text);
        }
        
        .form-control:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--text);
        }
        
        .btn-gold {
            background: linear-gradient(90deg, var(--gold), var(--gold-2));
            color: #111;
            border: 0;
            font-weight: 700;
            border-radius: 8px;
            padding: 12px 24px;
        }
        
        .btn-gold:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
        }
        
        .stats-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-top: 1rem;
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gold);
        }
        
        .stat-label {
            color: var(--muted);
            font-size: 0.9rem;
        }
        
        /* Theme toggle button styling */
        .theme-toggle-btn {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1000;
            background: var(--card);
            border: 2px solid var(--border);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .theme-toggle-btn:hover {
            transform: scale(1.1);
            border-color: var(--gold);
        }
        
        .theme-toggle-btn i {
            font-size: 1.2rem;
            color: var(--text);
        }
    </style>
</head>
<body>
    <!-- Include Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Theme Toggle Button -->
    <button class="theme-toggle-btn" id="themeToggleBtn" title="Toggle Theme">
        <i class="bi bi-moon" id="themeIcon"></i>
    </button>

    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <img src="<?= $user['avatar'] ?: 'assets/images/default-avatar.png' ?>" 
                 alt="<?= htmlspecialchars($user['username']) ?>" 
                 class="profile-avatar">
            <h1 class="h3 mb-2"><?= htmlspecialchars($user['username']) ?>'s Profile</h1>
            <p class="text-muted">Manage your account settings and preferences</p>
        </div>

        <!-- Profile Form -->
        <div class="form-card">
            <form method="POST" id="profileForm">
                <div class="row g-3">
                    <!-- Username -->
                    <div class="col-md-6">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               value="<?= htmlspecialchars($user['username']) ?>" 
                               required>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               value="<?= htmlspecialchars($user['email']) ?>" 
                               required>
                    </div>

                    <!-- Bio -->
                    <div class="col-12">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" 
                                  id="bio" 
                                  name="bio" 
                                  rows="4" 
                                  placeholder="Tell us about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </div>

                    <!-- Language -->
                    <div class="col-md-6">
                        <label for="language" class="form-label">Language</label>
                        <select class="form-control" id="language" name="language">
                            <option value="en" <?= $user['language'] === 'en' ? 'selected' : '' ?>>English</option>
                            <option value="ar" <?= $user['language'] === 'ar' ? 'selected' : '' ?>>العربية</option>
                        </select>
                    </div>

                    <!-- Theme -->
                    <div class="col-md-6">
                        <label for="theme" class="form-label">Theme</label>
                        <select class="form-control" id="theme" name="theme">
                            <option value="auto" <?= $user['theme'] === 'auto' ? 'selected' : '' ?>>Auto</option>
                            <option value="light" <?= $user['theme'] === 'light' ? 'selected' : '' ?>>Light</option>
                            <option value="dark" <?= $user['theme'] === 'dark' ? 'selected' : '' ?>>Dark</option>
                        </select>
                    </div>

                    <!-- Email Notifications -->
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="email_notifications" 
                                   name="email_notifications" 
                                   <?= $user['email_notifications'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="email_notifications">
                                Receive email notifications
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                    </a>
                    <button type="submit" class="btn btn-gold">
                        <i class="bi bi-check-lg me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- User Stats -->
        <div class="stats-card">
            <h5 class="mb-3">
                <i class="bi bi-graph-up me-2 text-primary"></i>Account Statistics
            </h5>
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number"><?= date('M j', strtotime($user['created_at'])) ?></div>
                        <div class="stat-label">Member since</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number"><?= $user['id'] ?></div>
                        <div class="stat-label">User ID</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number"><?= $user['is_admin'] ? 'Admin' : 'User' ?></div>
                        <div class="stat-label">Account Type</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    
    <script>
        // Theme management
        let currentTheme = localStorage.getItem('theme') || 'light';
        
        function setTheme(theme) {
            currentTheme = theme;
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            
            // Update theme icon
            const themeIcon = document.getElementById('themeIcon');
            if (themeIcon) {
                themeIcon.className = theme === 'light' ? 'bi bi-moon' : 'bi bi-sun';
            }
            
            // Update theme select in form
            const themeSelect = document.getElementById('theme');
            if (themeSelect) {
                themeSelect.value = theme;
            }
        }
        
        // Initialize theme
        function initTheme() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            setTheme(savedTheme);
        }
        
        // Theme toggle button
        document.getElementById('themeToggleBtn').addEventListener('click', function() {
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            setTheme(newTheme);
            
            // Show toast notification
            Toastify({
                text: `Switched to ${newTheme} mode`,
                duration: 2000,
                gravity: "top",
                position: "right",
                backgroundColor: "#28a745",
                stopOnFocus: true
            }).showToast();
        });
        
        // Theme select change
        document.getElementById('theme').addEventListener('change', function() {
            const theme = this.value;
            if (theme !== 'auto') {
                setTheme(theme);
                
                // Show toast notification
                Toastify({
                    text: `Theme set to ${theme}`,
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28a745",
                    stopOnFocus: true
                }).showToast();
            }
        });
        
        // Initialize theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            initTheme();
        });
        
        // Show toasts based on PHP variables
        <?php if ($edit_success): ?>
            Toastify({
                text: "Profile updated successfully!",
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#28a745",
                stopOnFocus: true
            }).showToast();
        <?php endif; ?>

        <?php if (!empty($edit_errors)): ?>
            Toastify({
                text: "<?= implode(', ', $edit_errors) ?>",
                duration: 5000,
                gravity: "top",
                position: "right",
                backgroundColor: "#dc3545",
                stopOnFocus: true
            }).showToast();
        <?php endif; ?>

        // Form submission with loading state
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Saving...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html> 