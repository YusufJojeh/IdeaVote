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
require_once 'includes/i18n.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || !$user['is_admin']) {
    header('Location: index.php');
    exit();
}

// Handle language and theme switching
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['switch_language'])) {
        $new_language = $_POST['switch_language'];
        $_SESSION['language'] = $new_language;
        
        // Update database
        $stmt = $pdo->prepare("UPDATE users SET language = ? WHERE id = ?");
        $stmt->execute([$new_language, $user_id]);
        
        // Redirect to refresh the page
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }
    
    if (isset($_POST['switch_theme'])) {
        $new_theme = $_POST['switch_theme'];
        $_SESSION['theme'] = $new_theme;
        
        // Update database
        $stmt = $pdo->prepare("UPDATE users SET theme = ? WHERE id = ?");
        $stmt->execute([$new_theme, $user_id]);
        
        // Redirect to refresh the page
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// Get current language and theme
$current_language = $_SESSION['language'] ?? 'en';
$current_theme = $_SESSION['theme'] ?? 'light';

// Initialize variables
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
$message = '';
$error = '';

// Get comprehensive dashboard statistics
$stats = [];
try {
    // User statistics
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
    $stats['admin_users'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['recent_users'] = $stmt->fetch()['count'];
    
    // Idea statistics
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM ideas");
    $stats['total_ideas'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM ideas WHERE is_featured = 1");
    $stats['featured_ideas'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM ideas WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['recent_ideas'] = $stmt->fetch()['count'];
    
    // Engagement statistics
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM votes");
    $stats['total_votes'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM comments");
    $stats['total_comments'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM reactions");
    $stats['total_reactions'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookmarks");
    $stats['total_bookmarks'] = $stmt->fetch()['count'];
    
    // Category statistics
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $stats['total_categories'] = $stmt->fetch()['count'];
    
    // Notification statistics
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM notifications WHERE is_read = 0");
    $stats['unread_notifications'] = $stmt->fetch()['count'];
    
    // Audit log statistics
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM audit_logs");
    $stats['total_audit_logs'] = $stmt->fetch()['count'];
    
} catch (Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Handle form submissions
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'add_user':
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $is_admin = intval($_POST['is_admin']);
            $bio = trim($_POST['bio'] ?? '');
            
            // Validation
            if (empty($username) || empty($email) || empty($password)) {
                $error = __('All required fields must be filled');
                break;
            }
            
            if ($password !== $confirm_password) {
                $error = __('Passwords do not match');
                break;
            }
            
            if (strlen($password) < 6) {
                $error = __('Password must be at least 6 characters long');
                break;
            }
            
            // Check if username or email already exists
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                if ($stmt->rowCount() > 0) {
                    $error = __('Username or email already exists');
                    break;
                }
                
                // Hash password and insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin, bio, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$username, $email, $hashed_password, $is_admin, $bio]);
                
                $message = __('User added successfully');
                
                // Log the action
                $new_user_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action, table_name, record_id, new_data, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $user_id,
                    'create',
                    'users',
                    $new_user_id,
                    json_encode(['username' => $username, 'email' => $email, 'is_admin' => $is_admin]),
                    $_SERVER['REMOTE_ADDR'] ?? '',
                    $_SERVER['HTTP_USER_AGENT'] ?? ''
                ]);
                
            } catch (Exception $e) {
                $error = __('Error adding user: ') . $e->getMessage();
            }
            break;
            
        case 'delete_user':
            $target_user_id = intval($_POST['user_id']);
            if ($target_user_id != 1) { // Prevent deleting main admin
                try {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$target_user_id]);
                    $message = __('User deleted successfully');
                } catch (Exception $e) {
                    $error = __('Error deleting user');
                }
            }
            break;
            
        case 'delete_idea':
            $idea_id = intval($_POST['idea_id']);
            try {
                $stmt = $pdo->prepare("DELETE FROM ideas WHERE id = ?");
                $stmt->execute([$idea_id]);
                $message = __('Idea deleted successfully');
            } catch (Exception $e) {
                $error = __('Error deleting idea');
            }
            break;
            
        case 'toggle_featured':
            $idea_id = intval($_POST['idea_id']);
            try {
                $stmt = $pdo->prepare("UPDATE ideas SET is_featured = NOT is_featured WHERE id = ?");
                $stmt->execute([$idea_id]);
                $message = __('Idea status updated');
            } catch (Exception $e) {
                $error = __('Error updating idea');
            }
            break;
            
        case 'delete_category':
            $category_id = intval($_POST['category_id']);
            try {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$category_id]);
                $message = __('Category deleted successfully');
            } catch (Exception $e) {
                $error = __('Error deleting category');
            }
            break;
    }
}

// Get data for different tabs
$users = [];
$ideas = [];
$categories = [];
$audit_logs = [];
$reported_content = [];

if ($active_tab === 'users') {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 100");
    $users = $stmt->fetchAll();
} elseif ($active_tab === 'ideas') {
    $stmt = $pdo->query("SELECT i.*, u.username, c.name_en as category_name FROM ideas i LEFT JOIN users u ON i.user_id = u.id LEFT JOIN categories c ON i.category_id = c.id ORDER BY i.created_at DESC LIMIT 100");
    $ideas = $stmt->fetchAll();
} elseif ($active_tab === 'categories') {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name_en");
    $categories = $stmt->fetchAll();
} elseif ($active_tab === 'audit') {
    $stmt = $pdo->query("SELECT al.*, u.username as admin_username FROM audit_logs al LEFT JOIN users u ON al.admin_id = u.id ORDER BY al.created_at DESC LIMIT 100");
    $audit_logs = $stmt->fetchAll();
} elseif ($active_tab === 'reports') {
    $stmt = $pdo->query("SELECT rc.*, u.username as reporter_username FROM reported_content rc LEFT JOIN users u ON rc.reporter_id = u.id ORDER BY rc.created_at DESC LIMIT 100");
    $reported_content = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="<?= $current_language ?>" dir="<?= $current_language === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('Admin Dashboard') ?> - IdeaVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
    <style>
        :root{
            /* Brand */
            --gold:#FFD700; --gold-2:#FFEF8E;
            /* Light (day) palette */
            --bg:#ffffff; --text:#181818; --muted:#555d68; --card:#ffffff; --border:#e5e7eb; --nav-bg:rgba(255,255,255,.86);
        }
        /* Dark (night) overrides */
        [data-theme="dark"]{
            --bg:#0b0e13; --text:#e5e7eb; --muted:#9aa3af; --card:#0f141c; --border:#1f2937; --nav-bg:rgba(15,20,28,.8);
        }
        *{box-sizing:border-box}
        body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:var(--bg);color:var(--text);}        
        a{color:inherit}
        .navbar{background:var(--nav-bg)!important;backdrop-filter:saturate(180%) blur(12px);border-bottom:1px solid var(--border)}
        .gold{color:var(--gold)}
        .btn-gold{background:linear-gradient(90deg,var(--gold),var(--gold-2));color:#111;border:0;font-weight:700}
        .btn-gold:hover{filter:brightness(1.05);transform:translateY(-1px)}
        .btn-outline-gold{border:2px solid var(--gold);color:#fff}
        .section{padding:4.5rem 0}
        .subtle{color:var(--muted)}
        .rounded-3xl{border-radius:1.25rem}
        .shadow-soft{box-shadow:0 10px 30px rgba(0,0,0,.25)}
        .badge-gold{background:rgba(255,215,0,.15);border:1px solid rgba(255,215,0,.35);color:#ffe98f}

        /* Admin specific styles */
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-2) 100%);
            color: #111;
        }
        
        .admin-content {
            background: var(--bg);
            min-height: 100vh;
            color: var(--text);
        }
        
        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-2) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .admin-nav-link {
            color: #111;
            text-decoration: none;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: all 0.2s;
            font-weight: 500;
        }
        
        .admin-nav-link:hover,
        .admin-nav-link.active {
            color: #111;
            background: rgba(255,255,255,0.3);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            color: var(--text);
        }
        
        .table {
            color: var(--text);
        }
        
        .table th {
            background: var(--card);
            border-color: var(--border);
            color: var(--text);
        }
        
        .table td {
            border-color: var(--border);
        }
        
        .table-hover tbody tr:hover {
            background: rgba(255,215,0,0.05);
        }
        
        .form-control {
            background: var(--card);
            border-color: var(--border);
            color: var(--text);
        }
        
        .form-control:focus {
            background: var(--card);
            border-color: var(--gold);
            color: var(--text);
            box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
        }
        
        /* Modal Styling */
        .modal-content {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            background: var(--card);
        }
        
        .modal-header {
            border-bottom: 1px solid var(--border);
            border-radius: 1rem 1rem 0 0;
            background: var(--card);
        }
        
        .modal-footer {
            border-top: 1px solid var(--border);
            border-radius: 0 0 1rem 1rem;
            background: var(--card);
        }
        
        .modal-title {
            font-weight: 600;
            color: var(--text);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        }
        
        .btn-primary {
            background: var(--gold);
            border-color: var(--gold);
            color: #111;
        }
        
        .btn-primary:hover {
            background: var(--gold-2);
            border-color: var(--gold-2);
            color: #111;
        }
        
        .btn-danger {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
            border-color: #bd2130;
            color: white;
        }
        
        .btn-warning {
            background: var(--gold);
            border-color: var(--gold);
            color: #111;
        }
        
        .btn-warning:hover {
            background: var(--gold-2);
            border-color: var(--gold-2);
            color: #111;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        /* RTL support */
        [dir="rtl"] .admin-nav-link i {
            margin-left: 0.5rem;
            margin-right: 0;
        }
        
        [dir="rtl"] .stat-card .d-flex {
            flex-direction: row-reverse;
        }
    </style>
</head>
<body data-theme="<?= $current_theme ?>">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 admin-sidebar p-0">
                <div class="p-4">
                    <h4 class="mb-4">
                        <i class="bi bi-shield-lock"></i> <?= __('Admin Dashboard') ?>
                    </h4>
                    
                    <nav class="nav flex-column">
                        <a class="admin-nav-link <?= $active_tab === 'dashboard' ? 'active' : '' ?>" href="?tab=dashboard">
                            <i class="bi bi-speedometer2 me-2"></i> <?= __('Dashboard') ?>
                        </a>
                        <a class="admin-nav-link <?= $active_tab === 'users' ? 'active' : '' ?>" href="?tab=users">
                            <i class="bi bi-people me-2"></i> <?= __('Users Management') ?>
                        </a>
                        <a class="admin-nav-link <?= $active_tab === 'ideas' ? 'active' : '' ?>" href="?tab=ideas">
                            <i class="bi bi-lightbulb me-2"></i> <?= __('Ideas Management') ?>
                        </a>
                        <a class="admin-nav-link <?= $active_tab === 'categories' ? 'active' : '' ?>" href="?tab=categories">
                            <i class="bi bi-tags me-2"></i> <?= __('Categories') ?>
                        </a>
                        <a class="admin-nav-link <?= $active_tab === 'reports' ? 'active' : '' ?>" href="?tab=reports">
                            <i class="bi bi-flag me-2"></i> <?= __('Reported Content') ?>
                        </a>
                        <a class="admin-nav-link <?= $active_tab === 'audit' ? 'active' : '' ?>" href="?tab=audit">
                            <i class="bi bi-journal-text me-2"></i> <?= __('Audit Log') ?>
                        </a>
                        <a class="admin-nav-link <?= $active_tab === 'settings' ? 'active' : '' ?>" href="?tab=settings">
                            <i class="bi bi-gear me-2"></i> <?= __('Settings') ?>
                        </a>
                    </nav>
                    
                    <hr class="my-4">
                    
                    <a href="index.php" class="admin-nav-link">
                        <i class="bi bi-house me-2"></i> <?= __('Back to Site') ?>
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 admin-content p-0">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg">
                    <div class="container-fluid">
                        <a class="navbar-brand" href="admin.php">
                            <i class="bi bi-shield-lock gold"></i>
                            <span class="ms-2"><?= __('Admin Panel') ?></span>
                        </a>
                        
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        
                        <div class="collapse navbar-collapse" id="adminNavbar">
                            <ul class="navbar-nav me-auto">
                                <li class="nav-item">
                                    <a class="nav-link <?= $active_tab === 'dashboard' ? 'active' : '' ?>" href="?tab=dashboard">
                                        <i class="bi bi-speedometer2"></i> <?= __('Dashboard') ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $active_tab === 'users' ? 'active' : '' ?>" href="?tab=users">
                                        <i class="bi bi-people"></i> <?= __('Users') ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $active_tab === 'ideas' ? 'active' : '' ?>" href="?tab=ideas">
                                        <i class="bi bi-lightbulb"></i> <?= __('Ideas') ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $active_tab === 'categories' ? 'active' : '' ?>" href="?tab=categories">
                                        <i class="bi bi-tags"></i> <?= __('Categories') ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $active_tab === 'reports' ? 'active' : '' ?>" href="?tab=reports">
                                        <i class="bi bi-flag"></i> <?= __('Reports') ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $active_tab === 'audit' ? 'active' : '' ?>" href="?tab=audit">
                                        <i class="bi bi-journal-text"></i> <?= __('Audit') ?>
                                    </a>
                                </li>
                            </ul>
                            
                            <ul class="navbar-nav">
                                <!-- Language Switcher -->
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-translate"></i>
                                        <span class="ms-1"><?= $current_language === 'ar' ? 'العربية' : 'English' ?></span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <form method="POST" style="display: inline;">
                                                <button type="submit" name="switch_language" value="en" class="dropdown-item <?= $current_language === 'en' ? 'active' : '' ?>">
                                                    <i class="bi bi-flag me-2"></i>English
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" style="display: inline;">
                                                <button type="submit" name="switch_language" value="ar" class="dropdown-item <?= $current_language === 'ar' ? 'active' : '' ?>">
                                                    <i class="bi bi-flag me-2"></i>العربية
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </li>
                                
                                <!-- Theme Switcher -->
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="themeDropdown" role="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-palette"></i>
                                        <span class="ms-1"><?= $current_theme === 'dark' ? __('Dark') : __('Light') ?></span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <form method="POST" style="display: inline;">
                                                <button type="submit" name="switch_theme" value="light" class="dropdown-item <?= $current_theme === 'light' ? 'active' : '' ?>">
                                                    <i class="bi bi-sun me-2"></i><?= __('Light') ?>
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" style="display: inline;">
                                                <button type="submit" name="switch_theme" value="dark" class="dropdown-item <?= $current_theme === 'dark' ? 'active' : '' ?>">
                                                    <i class="bi bi-moon me-2"></i><?= __('Dark') ?>
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </li>
                                
                                <!-- User Dropdown -->
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-person-circle"></i>
                                        <span class="ms-1"><?= $_SESSION['username'] ?? 'Admin' ?></span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="profile.php">
                                            <i class="bi bi-person me-2"></i><?= __('Profile') ?>
                                        </a></li>
                                        <li><a class="dropdown-item" href="?tab=settings">
                                            <i class="bi bi-gear me-2"></i><?= __('Settings') ?>
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="index.php">
                                            <i class="bi bi-house me-2"></i><?= __('Back to Site') ?>
                                        </a></li>
                                        <li><a class="dropdown-item" href="logout.php">
                                            <i class="bi bi-box-arrow-right me-2"></i><?= __('Logout') ?>
                                        </a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
                
                <!-- Content Area -->
                <div class="p-4">
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Dashboard Tab -->
                    <?php if ($active_tab === 'dashboard'): ?>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2><?= __('Admin Dashboard') ?></h2>
                            <div class="subtle"><?= __('Last Updated') ?>: <?= date('Y-m-d H:i:s') ?></div>
                        </div>
                        
                        <!-- Statistics Cards -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="stat-number"><?= number_format($stats['total_users']) ?></div>
                                            <div class="subtle"><?= __('Total Users') ?></div>
                                        </div>
                                        <i class="bi bi-people gold fs-1"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="stat-number"><?= number_format($stats['total_ideas']) ?></div>
                                            <div class="subtle"><?= __('Total Ideas') ?></div>
                                        </div>
                                        <i class="bi bi-lightbulb gold fs-1"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="stat-number"><?= number_format($stats['total_votes']) ?></div>
                                            <div class="subtle"><?= __('Total Votes') ?></div>
                                        </div>
                                        <i class="bi bi-hand-thumbs-up gold fs-1"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="stat-number"><?= number_format($stats['total_comments']) ?></div>
                                            <div class="subtle"><?= __('Total Comments') ?></div>
                                        </div>
                                        <i class="bi bi-chat-dots gold fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Statistics -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="stat-number"><?= number_format($stats['admin_users']) ?></div>
                                            <div class="subtle"><?= __('Admin Users') ?></div>
                                        </div>
                                        <i class="bi bi-shield-lock gold fs-1"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="stat-number"><?= number_format($stats['featured_ideas']) ?></div>
                                            <div class="subtle"><?= __('Featured Ideas') ?></div>
                                        </div>
                                        <i class="bi bi-star gold fs-1"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="stat-number"><?= number_format($stats['total_reactions']) ?></div>
                                            <div class="subtle"><?= __('Total Reactions') ?></div>
                                        </div>
                                        <i class="bi bi-emoji-smile gold fs-1"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="stat-number"><?= number_format($stats['total_categories']) ?></div>
                                            <div class="subtle"><?= __('Total Categories') ?></div>
                                        </div>
                                        <i class="bi bi-tags gold fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Welcome Message -->
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="bi bi-shield-check gold fs-1 mb-3"></i>
                                <h3><?= __('Welcome to Admin Dashboard') ?></h3>
                                <p class="subtle"><?= __('Manage your IdeaVote platform from here') ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Users Tab -->
                    <?php if ($active_tab === 'users'): ?>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2><?= __('Users Management') ?></h2>
                            <button type="button" class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="bi bi-person-plus me-2"></i><?= __('Add User') ?>
                            </button>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th><?= __('ID') ?></th>
                                                <th><?= __('Username') ?></th>
                                                <th><?= __('Email') ?></th>
                                                <th><?= __('Role') ?></th>
                                                <th><?= __('Joined') ?></th>
                                                <th><?= __('Actions') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td><?= $user['id'] ?></td>
                                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                                    <td>
                                                        <?php if ($user['is_admin']): ?>
                                                            <span class="badge bg-danger"><?= __('Admin') ?></span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary"><?= __('User') ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= $user['created_at'] ?></td>
                                                    <td>
                                                        <?php if ($user['id'] != 1): ?>
                                                            <form method="POST" style="display: inline;" onsubmit="return confirm('<?= __('Are you sure you want to delete this user?') ?>')">
                                                                <input type="hidden" name="action" value="delete_user">
                                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger"><?= __('Delete') ?></button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Ideas Tab -->
                    <?php if ($active_tab === 'ideas'): ?>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2><?= __('Ideas Management') ?></h2>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th><?= __('ID') ?></th>
                                                <th><?= __('Title') ?></th>
                                                <th><?= __('Author') ?></th>
                                                <th><?= __('Category') ?></th>
                                                <th><?= __('Votes') ?></th>
                                                <th><?= __('Featured') ?></th>
                                                <th><?= __('Created') ?></th>
                                                <th><?= __('Actions') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ideas as $idea): ?>
                                                <tr>
                                                    <td><?= $idea['id'] ?></td>
                                                    <td><?= htmlspecialchars($idea['title']) ?></td>
                                                    <td><?= htmlspecialchars($idea['username']) ?></td>
                                                    <td><?= htmlspecialchars($idea['category_name'] ?? __('Uncategorized')) ?></td>
                                                    <td><?= $idea['votes_count'] ?></td>
                                                    <td>
                                                        <?php if ($idea['is_featured']): ?>
                                                            <span class="badge bg-warning"><?= __('Featured') ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= $idea['created_at'] ?></td>
                                                    <td>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="toggle_featured">
                                                            <input type="hidden" name="idea_id" value="<?= $idea['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-warning"><?= __('Toggle Featured') ?></button>
                                                        </form>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('<?= __('Are you sure you want to delete this idea?') ?>')">
                                                            <input type="hidden" name="action" value="delete_idea">
                                                            <input type="hidden" name="idea_id" value="<?= $idea['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger"><?= __('Delete') ?></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Categories Tab -->
                    <?php if ($active_tab === 'categories'): ?>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2><?= __('Categories Management') ?></h2>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="categoriesTable">
                                        <thead>
                                            <tr>
                                                <th><?= __('ID') ?></th>
                                                <th><?= __('Name (English)') ?></th>
                                                <th><?= __('Name (Arabic)') ?></th>
                                                <th><?= __('Description') ?></th>
                                                <th><?= __('Actions') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categories as $category): ?>
                                                <tr>
                                                    <td><?= $category['id'] ?></td>
                                                    <td><?= htmlspecialchars($category['name_en']) ?></td>
                                                    <td><?= htmlspecialchars($category['name_ar']) ?></td>
                                                    <td><?= htmlspecialchars($category['description'] ?? '') ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary"><?= __('Edit') ?></button>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('<?= __('Are you sure you want to delete this category?') ?>')">
                                                            <input type="hidden" name="action" value="delete_category">
                                                            <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger"><?= __('Delete') ?></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Audit Log Tab -->
                    <?php if ($active_tab === 'audit'): ?>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2><?= __('Audit Log') ?></h2>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th><?= __('ID') ?></th>
                                                <th><?= __('Admin') ?></th>
                                                <th><?= __('Action') ?></th>
                                                <th><?= __('Table') ?></th>
                                                <th><?= __('Record ID') ?></th>
                                                <th><?= __('IP Address') ?></th>
                                                <th><?= __('Date') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($audit_logs as $log): ?>
                                                <tr>
                                                    <td><?= $log['id'] ?></td>
                                                    <td><?= htmlspecialchars($log['admin_username']) ?></td>
                                                    <td><?= htmlspecialchars($log['action']) ?></td>
                                                    <td><?= htmlspecialchars($log['table_name']) ?></td>
                                                    <td><?= $log['record_id'] ?></td>
                                                    <td><?= htmlspecialchars($log['ip_address']) ?></td>
                                                    <td><?= $log['created_at'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Reported Content Tab -->
                    <?php if ($active_tab === 'reports'): ?>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2><?= __('Reported Content') ?></h2>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th><?= __('ID') ?></th>
                                                <th><?= __('Reporter') ?></th>
                                                <th><?= __('Content Type') ?></th>
                                                <th><?= __('Content ID') ?></th>
                                                <th><?= __('Reason') ?></th>
                                                <th><?= __('Status') ?></th>
                                                <th><?= __('Date') ?></th>
                                                <th><?= __('Actions') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reported_content as $report): ?>
                                                <tr>
                                                    <td><?= $report['id'] ?></td>
                                                    <td><?= htmlspecialchars($report['reporter_username']) ?></td>
                                                    <td><?= htmlspecialchars($report['content_type']) ?></td>
                                                    <td><?= $report['content_id'] ?></td>
                                                    <td><?= htmlspecialchars($report['reason']) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $report['status'] === 'pending' ? 'warning' : ($report['status'] === 'resolved' ? 'success' : 'secondary') ?>">
                                                            <?= htmlspecialchars($report['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= $report['created_at'] ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary"><?= __('Review') ?></button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Settings Tab -->
                    <?php if ($active_tab === 'settings'): ?>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2><?= __('Settings') ?></h2>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><?= __('Platform Settings') ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#platformSettingsModal">
                                            <i class="bi bi-gear me-2"></i><?= __('Configure Platform') ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><?= __('User Management') ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userManagementModal">
                                            <i class="bi bi-people me-2"></i><?= __('Manage Users') ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">
                        <i class="bi bi-person-plus me-2"></i><?= __('Add New User') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="addUserForm">
                    <input type="hidden" name="action" value="add_user">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label"><?= __('Username') ?> *</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label"><?= __('Email') ?> *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label"><?= __('Password') ?> *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label"><?= __('Confirm Password') ?> *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="is_admin" class="form-label"><?= __('Role') ?></label>
                            <select class="form-select" id="is_admin" name="is_admin">
                                <option value="0"><?= __('User') ?></option>
                                <option value="1"><?= __('Admin') ?></option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="bio" class="form-label"><?= __('Bio') ?></label>
                            <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="<?= __('Optional user bio...') ?>"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('Cancel') ?></button>
                        <button type="submit" class="btn btn-gold">
                            <i class="bi bi-person-plus me-2"></i><?= __('Add User') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/theme.js"></script>
    <script>
        // Apply theme based on user preference
        function applyTheme(theme) {
            const body = document.body;
            
            // Remove existing theme classes
            body.classList.remove('dark-mode', 'light-mode');
            
            if (theme === 'dark') {
                body.setAttribute('data-theme', 'dark');
                console.log('Dark mode applied');
            } else if (theme === 'light') {
                body.setAttribute('data-theme', 'light');
                console.log('Light mode applied');
            }
        }
        
        // Apply current theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, applying theme:', '<?= $current_theme ?>');
            applyTheme('<?= $current_theme ?>');
            
            // Add User Form Validation
            const addUserForm = document.getElementById('addUserForm');
            if (addUserForm) {
                addUserForm.addEventListener('submit', function(e) {
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert('<?= __('Passwords do not match') ?>');
                        return false;
                    }
                    
                    if (password.length < 6) {
                        e.preventDefault();
                        alert('<?= __('Password must be at least 6 characters long') ?>');
                        return false;
                    }
                });
            }
            
            // Clear form when modal is closed
            const addUserModal = document.getElementById('addUserModal');
            if (addUserModal) {
                addUserModal.addEventListener('hidden.bs.modal', function() {
                    addUserForm.reset();
                });
            }
        });
    </script>
</body>
</html>
