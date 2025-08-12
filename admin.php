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
        case 'delete_user':
            $target_user_id = intval($_POST['user_id']);
            if ($target_user_id != 1) { // Prevent deleting main admin
                try {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$target_user_id]);
                    $message = $current_language === 'ar' ? 'تم حذف المستخدم بنجاح' : 'User deleted successfully';
                } catch (Exception $e) {
                    $error = $current_language === 'ar' ? 'خطأ في حذف المستخدم' : 'Error deleting user';
                }
            }
            break;
            
        case 'delete_idea':
            $idea_id = intval($_POST['idea_id']);
            try {
                $stmt = $pdo->prepare("DELETE FROM ideas WHERE id = ?");
                $stmt->execute([$idea_id]);
                $message = $current_language === 'ar' ? 'تم حذف الفكرة بنجاح' : 'Idea deleted successfully';
            } catch (Exception $e) {
                $error = $current_language === 'ar' ? 'خطأ في حذف الفكرة' : 'Error deleting idea';
            }
            break;
            
        case 'toggle_featured':
            $idea_id = intval($_POST['idea_id']);
            try {
                $stmt = $pdo->prepare("UPDATE ideas SET is_featured = NOT is_featured WHERE id = ?");
                $stmt->execute([$idea_id]);
                $message = $current_language === 'ar' ? 'تم تحديث حالة الفكرة' : 'Idea status updated';
            } catch (Exception $e) {
                $error = $current_language === 'ar' ? 'خطأ في تحديث الفكرة' : 'Error updating idea';
            }
            break;
            
        case 'delete_category':
            $category_id = intval($_POST['category_id']);
            try {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$category_id]);
                $message = $current_language === 'ar' ? 'تم حذف الفئة بنجاح' : 'Category deleted successfully';
            } catch (Exception $e) {
                $error = $current_language === 'ar' ? 'خطأ في حذف الفئة' : 'Error deleting category';
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

// Translation function
function t($key, $lang = 'en') {
    $translations = [
        'en' => [
            'Admin Dashboard' => 'Admin Dashboard',
            'Dashboard' => 'Dashboard',
            'Users Management' => 'Users Management',
            'Ideas Management' => 'Ideas Management',
            'Categories' => 'Categories',
            'Reported Content' => 'Reported Content',
            'Audit Log' => 'Audit Log',
            'Settings' => 'Settings',
            'Total Users' => 'Total Users',
            'Admin Users' => 'Admin Users',
            'Recent Users' => 'Recent Users',
            'Total Ideas' => 'Total Ideas',
            'Featured Ideas' => 'Featured Ideas',
            'Recent Ideas' => 'Recent Ideas',
            'Total Votes' => 'Total Votes',
            'Total Comments' => 'Total Comments',
            'Total Reactions' => 'Total Reactions',
            'Total Bookmarks' => 'Total Bookmarks',
            'Total Categories' => 'Total Categories',
            'Unread Notifications' => 'Unread Notifications',
            'Total Audit Logs' => 'Total Audit Logs',
            'Last Updated' => 'Last Updated',
            'Welcome to Admin Dashboard' => 'Welcome to Admin Dashboard',
            'Manage your IdeaVote platform from here' => 'Manage your IdeaVote platform from here',
            'ID' => 'ID',
            'Username' => 'Username',
            'Email' => 'Email',
            'Role' => 'Role',
            'Joined' => 'Joined',
            'Actions' => 'Actions',
            'Delete' => 'Delete',
            'Admin' => 'Admin',
            'User' => 'User',
            'Title' => 'Title',
            'Author' => 'Author',
            'Category' => 'Category',
            'Votes' => 'Votes',
            'Created' => 'Created',
            'Featured' => 'Featured',
            'Toggle Featured' => 'Toggle Featured',
            'Name (English)' => 'Name (English)',
            'Name (Arabic)' => 'Name (Arabic)',
            'Description' => 'Description',
            'Edit' => 'Edit',
            'Action' => 'Action',
            'Table' => 'Table',
            'Record ID' => 'Record ID',
            'IP Address' => 'IP Address',
            'Date' => 'Date',
            'Reporter' => 'Reporter',
            'Content Type' => 'Content Type',
            'Content ID' => 'Content ID',
            'Reason' => 'Reason',
            'Status' => 'Status',
            'Review' => 'Review',
            'Back to Site' => 'Back to Site',
            'Language' => 'Language',
            'Theme' => 'Theme',
            'English' => 'English',
            'Arabic' => 'Arabic',
            'Light' => 'Light',
            'Dark' => 'Dark',
            'Auto' => 'Auto',
            'Switch Language' => 'Switch Language',
            'Switch Theme' => 'Switch Theme',
            'Are you sure you want to delete this user?' => 'Are you sure you want to delete this user?',
            'Are you sure you want to delete this idea?' => 'Are you sure you want to delete this idea?',
            'Are you sure you want to delete this category?' => 'Are you sure you want to delete this category?',
            'User deleted successfully' => 'User deleted successfully',
            'Idea deleted successfully' => 'Idea deleted successfully',
            'Category deleted successfully' => 'Category deleted successfully',
            'Idea status updated' => 'Idea status updated',
            'Error deleting user' => 'Error deleting user',
            'Error deleting idea' => 'Error deleting idea',
            'Error deleting category' => 'Error deleting category',
            'Error updating idea' => 'Error updating idea',
            'Coming Soon' => 'Coming Soon',
            'This feature is under development' => 'This feature is under development'
        ],
        'ar' => [
            'Admin Dashboard' => 'لوحة تحكم المدير',
            'Dashboard' => 'الرئيسية',
            'Users Management' => 'إدارة المستخدمين',
            'Ideas Management' => 'إدارة الأفكار',
            'Categories' => 'الفئات',
            'Reported Content' => 'المحتوى المبلغ عنه',
            'Audit Log' => 'سجل التدقيق',
            'Settings' => 'الإعدادات',
            'Total Users' => 'إجمالي المستخدمين',
            'Admin Users' => 'المديرين',
            'Recent Users' => 'المستخدمين الجدد',
            'Total Ideas' => 'إجمالي الأفكار',
            'Featured Ideas' => 'الأفكار المميزة',
            'Recent Ideas' => 'الأفكار الجديدة',
            'Total Votes' => 'إجمالي التصويتات',
            'Total Comments' => 'إجمالي التعليقات',
            'Total Reactions' => 'إجمالي التفاعلات',
            'Total Bookmarks' => 'إجمالي الإشارات المرجعية',
            'Total Categories' => 'إجمالي الفئات',
            'Unread Notifications' => 'الإشعارات غير المقروءة',
            'Total Audit Logs' => 'إجمالي سجلات التدقيق',
            'Last Updated' => 'آخر تحديث',
            'Welcome to Admin Dashboard' => 'مرحباً بك في لوحة تحكم المدير',
            'Manage your IdeaVote platform from here' => 'أدر منصة IdeaVote من هنا',
            'ID' => 'الرقم',
            'Username' => 'اسم المستخدم',
            'Email' => 'البريد الإلكتروني',
            'Role' => 'الدور',
            'Joined' => 'تاريخ الانضمام',
            'Actions' => 'الإجراءات',
            'Delete' => 'حذف',
            'Admin' => 'مدير',
            'User' => 'مستخدم',
            'Title' => 'العنوان',
            'Author' => 'الكاتب',
            'Category' => 'الفئة',
            'Votes' => 'التصويتات',
            'Created' => 'تاريخ الإنشاء',
            'Featured' => 'مميز',
            'Toggle Featured' => 'تبديل التميز',
            'Name (English)' => 'الاسم (إنجليزي)',
            'Name (Arabic)' => 'الاسم (عربي)',
            'Description' => 'الوصف',
            'Edit' => 'تعديل',
            'Action' => 'الإجراء',
            'Table' => 'الجدول',
            'Record ID' => 'رقم السجل',
            'IP Address' => 'عنوان IP',
            'Date' => 'التاريخ',
            'Reporter' => 'المبلغ',
            'Content Type' => 'نوع المحتوى',
            'Content ID' => 'رقم المحتوى',
            'Reason' => 'السبب',
            'Status' => 'الحالة',
            'Review' => 'مراجعة',
            'Back to Site' => 'العودة للموقع',
            'Language' => 'اللغة',
            'Theme' => 'المظهر',
            'English' => 'إنجليزي',
            'Arabic' => 'عربي',
            'Light' => 'فاتح',
            'Dark' => 'داكن',
            'Auto' => 'تلقائي',
            'Switch Language' => 'تبديل اللغة',
            'Switch Theme' => 'تبديل المظهر',
            'Are you sure you want to delete this user?' => 'هل أنت متأكد من حذف هذا المستخدم؟',
            'Are you sure you want to delete this idea?' => 'هل أنت متأكد من حذف هذه الفكرة؟',
            'Are you sure you want to delete this category?' => 'هل أنت متأكد من حذف هذه الفئة؟',
            'User deleted successfully' => 'تم حذف المستخدم بنجاح',
            'Idea deleted successfully' => 'تم حذف الفكرة بنجاح',
            'Category deleted successfully' => 'تم حذف الفئة بنجاح',
            'Idea status updated' => 'تم تحديث حالة الفكرة',
            'Error deleting user' => 'خطأ في حذف المستخدم',
            'Error deleting idea' => 'خطأ في حذف الفكرة',
            'Error deleting category' => 'خطأ في حذف الفئة',
            'Error updating idea' => 'خطأ في تحديث الفكرة',
            'Coming Soon' => 'قريباً',
            'This feature is under development' => 'هذه الميزة قيد التطوير'
        ]
    ];
    
    return $translations[$lang][$key] ?? $key;
}
?>

<!DOCTYPE html>
<html lang="<?= $current_language ?>" dir="<?= $current_language === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('Admin Dashboard', $current_language) ?> - IdeaVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
    <style>
        /* CSS Variables matching index.php exactly */
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --gold: #FFD700;
            --gold-2: #FFEF8E;
            --success: #28a745;
            --danger: #dc3545;
            --info: #17a2b8;
            --warning: #ffc107;
            
            /* Light mode colors */
            --bg-color: #ffffff;
            --card-bg: #ffffff;
            --text-color: #333333;
            --text-muted: #6c757d;
            --border-color: #e9ecef;
            --hover-bg: #f8f9fa;
            --success-light: #d4edda;
            --danger-light: #f8d7da;
        }
        
        /* Dark mode colors */
        body.dark-mode {
            --bg-color: #1a1a1a;
            --card-bg: #2d2d2d;
            --text-color: #ffffff;
            --text-muted: #adb5bd;
            --border-color: #404040;
            --hover-bg: #3a3a3a;
            --success-light: #1e4a1e;
            --danger-light: #4a1e1e;
        }
        
        /* Force dark mode when class is applied */
        body.dark-mode .admin-content {
            background: var(--bg-color) !important;
            color: var(--text-color) !important;
        }
        
        body.dark-mode .card {
            background: var(--card-bg) !important;
            border-color: var(--border-color) !important;
            color: var(--text-color) !important;
        }
        
        body.dark-mode .card-header {
            background: var(--card-bg) !important;
            border-bottom-color: var(--border-color) !important;
            color: var(--text-color) !important;
        }
        
        body.dark-mode .card-body {
            color: var(--text-color) !important;
        }
        
        body.dark-mode .table {
            color: var(--text-color) !important;
        }
        
        body.dark-mode .table th {
            background: var(--card-bg) !important;
            border-color: var(--border-color) !important;
            color: var(--text-color) !important;
        }
        
        body.dark-mode .table td {
            border-color: var(--border-color) !important;
        }
        
        body.dark-mode .table-hover tbody tr:hover {
            background: var(--hover-bg) !important;
        }
        
        body.dark-mode .form-control {
            background: var(--card-bg) !important;
            border-color: var(--border-color) !important;
            color: var(--text-color) !important;
        }
        
        body.dark-mode .form-control:focus {
            background: var(--card-bg) !important;
            border-color: var(--primary) !important;
            color: var(--text-color) !important;
        }
        
        body.dark-mode .modal-content {
            background: var(--card-bg) !important;
            border-color: var(--border-color) !important;
        }
        
        body.dark-mode .modal-header {
            border-bottom-color: var(--border-color) !important;
            background: var(--card-bg) !important;
        }
        
        body.dark-mode .modal-footer {
            border-top-color: var(--border-color) !important;
            background: var(--card-bg) !important;
        }
        
        body.dark-mode .modal-title {
            color: var(--text-color) !important;
        }
        
        body.dark-mode .dropdown-menu {
            background: var(--card-bg) !important;
            border-color: var(--border-color) !important;
        }
        
        body.dark-mode .dropdown-item {
            color: var(--text-color) !important;
        }
        
        body.dark-mode .dropdown-item:hover {
            background: var(--hover-bg) !important;
            color: var(--text-color) !important;
        }
        
        body.dark-mode h1, 
        body.dark-mode h2, 
        body.dark-mode h3, 
        body.dark-mode h4, 
        body.dark-mode h5, 
        body.dark-mode h6 {
            color: var(--text-color) !important;
        }
        
        body.dark-mode .text-muted {
            color: var(--text-muted) !important;
        }
        
        body.dark-mode .btn-close {
            filter: invert(1);
        }
        
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        
        .admin-content {
            background: var(--bg-color);
            min-height: 100vh;
            color: var(--text-color);
        }
        
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
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
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .admin-nav-link:hover,
        .admin-nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        [dir="rtl"] .admin-nav-link i {
            margin-left: 0.5rem;
            margin-right: 0;
        }
        
        [dir="rtl"] .stat-card .d-flex {
            flex-direction: row-reverse;
        }
        
        /* Card styling */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            color: var(--text-color);
        }
        
        .card-header {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            color: var(--text-color);
        }
        
        .card-body {
            color: var(--text-color);
        }
        
        /* Button styling */
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
            border-color: #5a6fd8;
            color: white;
        }
        
        .btn-warning {
            background: var(--gold);
            border-color: var(--gold);
            color: #333;
        }
        
        .btn-warning:hover {
            background: var(--gold-2);
            border-color: var(--gold-2);
            color: #333;
        }
        
        .btn-danger {
            background: var(--danger);
            border-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
            border-color: #bd2130;
            color: white;
        }
        
        .btn-info {
            background: var(--info);
            border-color: var(--info);
            color: white;
        }
        
        .btn-info:hover {
            background: #138496;
            border-color: #117a8b;
            color: white;
        }
        
        .btn-outline-warning {
            color: var(--gold);
            border-color: var(--gold);
        }
        
        .btn-outline-warning:hover {
            background: var(--gold);
            border-color: var(--gold);
            color: #333;
        }
        
        .btn-outline-info {
            color: var(--info);
            border-color: var(--info);
        }
        
        .btn-outline-info:hover {
            background: var(--info);
            border-color: var(--info);
            color: white;
        }
        
        /* Text colors */
        .text-primary {
            color: var(--primary) !important;
        }
        
        .text-warning {
            color: var(--gold) !important;
        }
        
        .text-success {
            color: var(--success) !important;
        }
        
        .text-info {
            color: var(--info) !important;
        }
        
        .text-danger {
            color: var(--danger) !important;
        }
        
        .text-muted {
            color: var(--text-muted) !important;
        }
        
        /* Badge styling */
        .badge.bg-primary {
            background: var(--primary) !important;
        }
        
        .badge.bg-warning {
            background: var(--gold) !important;
            color: #333;
        }
        
        .badge.bg-danger {
            background: var(--danger) !important;
        }
        
        .badge.bg-secondary {
            background: var(--text-muted) !important;
        }
        
        .badge.bg-success {
            background: var(--success) !important;
        }
        
        .badge.bg-info {
            background: var(--info) !important;
        }
        
        /* Table styling */
        .table {
            color: var(--text-color);
        }
        
        .table th {
            background: var(--card-bg);
            border-color: var(--border-color);
            color: var(--text-color);
        }
        
        .table td {
            border-color: var(--border-color);
        }
        
        .table-hover tbody tr:hover {
            background: var(--hover-bg);
        }
        
        /* Alert styling */
        .alert {
            border-radius: 12px;
            border: none;
        }
        
        .alert-success {
            background: var(--success-light);
            color: var(--success);
        }
        
        .alert-danger {
            background: var(--danger-light);
            color: var(--danger);
        }
        
        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            color: var(--text-color);
        }
        
        /* Form elements */
        .form-control {
            background: var(--card-bg);
            border-color: var(--border-color);
            color: var(--text-color);
        }
        
        .form-control:focus {
            background: var(--card-bg);
            border-color: var(--primary);
            color: var(--text-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        /* Modal styling */
        .modal-content {
            background: var(--card-bg);
            border-color: var(--border-color);
            border-radius: 12px;
        }
        
        .modal-header {
            border-bottom-color: var(--border-color);
            background: var(--card-bg);
        }
        
        .modal-footer {
            border-top-color: var(--border-color);
            background: var(--card-bg);
        }
        
        .modal-title {
            color: var(--text-color);
        }
        
        /* Dropdown styling */
        .dropdown-menu {
            background: var(--card-bg);
            border-color: var(--border-color);
        }
        
        .dropdown-item {
            color: var(--text-color);
        }
        
        .dropdown-item:hover {
            background: var(--hover-bg);
            color: var(--text-color);
        }
        
        .dropdown-item.active {
            background: var(--primary);
            color: white;
        }
        
        /* Progress bars */
        .progress {
            background: var(--border-color);
        }
        
        .progress-bar {
            background: var(--primary);
        }
        
        /* Modal animations */
        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out;
        }
        
        .modal.show .modal-dialog {
            transform: none;
        }
        
        /* Navbar styling */
        .navbar-nav .nav-link {
            color: var(--text-color);
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--primary);
        }
        
        .navbar-nav .nav-link.active {
            color: var(--primary);
            font-weight: 600;
        }
        
        /* RTL support for navbar */
        [dir="rtl"] .navbar-nav .nav-link {
            text-align: right;
        }
        
        [dir="rtl"] .dropdown-menu {
            text-align: right;
        }
        
        [dir="rtl"] .dropdown-item i {
            margin-left: 0.5rem;
            margin-right: 0;
        }
    </style>
</head>
<body class="<?= $current_theme === 'dark' ? 'dark-mode' : '' ?>">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 admin-sidebar p-0">
                <div class="p-4">
                    <h4 class="text-white mb-4">
                        <i class="bi bi-shield-lock"></i> <?= t('Admin Dashboard', $current_language) ?>
                    </h4>
                    
                    <nav class="nav flex-column">
                        <a class="admin-nav-link <?= $active_tab === 'dashboard' ? 'active' : '' ?>" href="?tab=dashboard">
                            <i class="bi bi-speedometer2 me-2"></i> <?= t('Dashboard', $current_language) ?>
                        </a>
                        <a class="admin-nav-link <?= $active_tab === 'users' ? 'active' : '' ?>" href="?tab=users">
                            <i class="bi bi-people me-2"></i> <?= t('Users Management', $current_language) ?>
                        </a>
                        <a class="admin-nav-link <?= $active_tab === 'ideas' ? 'active' : '' ?>" href="?tab=ideas">
                            <i class="bi bi-lightbulb me-2"></i> <?= t('Ideas Management', $current_language) ?>
                        </a>
                        <a class="admin-nav-link <?= $active_tab === 'categories' ? 'active' : '' ?>" href="?tab=categories">
                            <i class="bi bi-tags me-2"></i> <?= t('Categories', $current_language) ?>
                        </a>
                        <a class="admin-nav-link <?= $active_tab === 'reports' ? 'active' : '' ?>" href="?tab=reports">
                            <i class="bi bi-flag me-2"></i> <?= t('Reported Content', $current_language) ?>
                        </a>
                        <a class="admin-nav-link <?= $active_tab === 'audit' ? 'active' : '' ?>" href="?tab=audit">
                            <i class="bi bi-journal-text me-2"></i> <?= t('Audit Log', $current_language) ?>
                        </a>
                        <a class="admin-nav-link <?= $active_tab === 'settings' ? 'active' : '' ?>" href="?tab=settings">
                            <i class="bi bi-gear me-2"></i> <?= t('Settings', $current_language) ?>
                        </a>
                    </nav>
                    
                    <hr class="text-white-50 my-4">
                    
                    <a href="index.php" class="admin-nav-link">
                        <i class="bi bi-house me-2"></i> <?= t('Back to Site', $current_language) ?>
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 admin-content p-0">
                <!-- Add Navbar -->
                <nav class="navbar navbar-expand-lg" style="background: var(--card-bg); border-bottom: 1px solid var(--border-color);">
                    <div class="container-fluid">
                        <a class="navbar-brand" href="admin.php">
                            <i class="bi bi-shield-lock text-primary"></i>
                            <span class="ms-2"><?= t('Admin Panel', $current_language) ?></span>
                        </a>
                        
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        
                        <div class="collapse navbar-collapse" id="adminNavbar">
                            <ul class="navbar-nav me-auto">
                                <li class="nav-item">
                                    <a class="nav-link <?= $active_tab === 'dashboard' ? 'active' : '' ?>" href="?tab=dashboard">
                                        <i class="bi bi-speedometer2"></i> <?= t('Dashboard', $current_language) ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $active_tab === 'users' ? 'active' : '' ?>" href="?tab=users">
                                        <i class="bi bi-people"></i> <?= t('Users', $current_language) ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $active_tab === 'ideas' ? 'active' : '' ?>" href="?tab=ideas">
                                        <i class="bi bi-lightbulb"></i> <?= t('Ideas', $current_language) ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $active_tab === 'categories' ? 'active' : '' ?>" href="?tab=categories">
                                        <i class="bi bi-tags"></i> <?= t('Categories', $current_language) ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $active_tab === 'reports' ? 'active' : '' ?>" href="?tab=reports">
                                        <i class="bi bi-flag"></i> <?= t('Reports', $current_language) ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $active_tab === 'audit' ? 'active' : '' ?>" href="?tab=audit">
                                        <i class="bi bi-journal-text"></i> <?= t('Audit', $current_language) ?>
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
                                        <span class="ms-1"><?= $current_theme === 'dark' ? t('Dark', $current_language) : t('Light', $current_language) ?></span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <form method="POST" style="display: inline;">
                                                <button type="submit" name="switch_theme" value="light" class="dropdown-item <?= $current_theme === 'light' ? 'active' : '' ?>">
                                                    <i class="bi bi-sun me-2"></i><?= t('Light', $current_language) ?>
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" style="display: inline;">
                                                <button type="submit" name="switch_theme" value="dark" class="dropdown-item <?= $current_theme === 'dark' ? 'active' : '' ?>">
                                                    <i class="bi bi-moon me-2"></i><?= t('Dark', $current_language) ?>
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
                                            <i class="bi bi-person me-2"></i><?= t('Profile', $current_language) ?>
                                        </a></li>
                                        <li><a class="dropdown-item" href="?tab=settings">
                                            <i class="bi bi-gear me-2"></i><?= t('Settings', $current_language) ?>
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="index.php">
                                            <i class="bi bi-house me-2"></i><?= t('Back to Site', $current_language) ?>
                                        </a></li>
                                        <li><a class="dropdown-item" href="logout.php">
                                            <i class="bi bi-box-arrow-right me-2"></i><?= t('Logout', $current_language) ?>
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
                        <h2><?= t('Admin Dashboard', $current_language) ?></h2>
                        <div class="text-muted"><?= t('Last Updated', $current_language) ?>: <?= date('Y-m-d H:i:s') ?></div>
                    </div>
                    
                    <!-- Statistics Cards -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="stat-number"><?= number_format($stats['total_users']) ?></div>
                                        <div class="text-muted"><?= t('Total Users', $current_language) ?></div>
                                    </div>
                                    <i class="bi bi-people text-primary fs-1"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="stat-number"><?= number_format($stats['total_ideas']) ?></div>
                                        <div class="text-muted"><?= t('Total Ideas', $current_language) ?></div>
                                    </div>
                                    <i class="bi bi-lightbulb text-warning fs-1"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="stat-number"><?= number_format($stats['total_votes']) ?></div>
                                        <div class="text-muted"><?= t('Total Votes', $current_language) ?></div>
                                    </div>
                                    <i class="bi bi-hand-thumbs-up text-success fs-1"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="stat-number"><?= number_format($stats['total_comments']) ?></div>
                                        <div class="text-muted"><?= t('Total Comments', $current_language) ?></div>
                                    </div>
                                    <i class="bi bi-chat-dots text-info fs-1"></i>
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
                                        <div class="text-muted"><?= t('Admin Users', $current_language) ?></div>
                                    </div>
                                    <i class="bi bi-shield-lock text-danger fs-1"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="stat-number"><?= number_format($stats['featured_ideas']) ?></div>
                                        <div class="text-muted"><?= t('Featured Ideas', $current_language) ?></div>
                                    </div>
                                    <i class="bi bi-star text-warning fs-1"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="stat-number"><?= number_format($stats['total_reactions']) ?></div>
                                        <div class="text-muted"><?= t('Total Reactions', $current_language) ?></div>
                                    </div>
                                    <i class="bi bi-emoji-smile text-success fs-1"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="stat-number"><?= number_format($stats['total_categories']) ?></div>
                                        <div class="text-muted"><?= t('Total Categories', $current_language) ?></div>
                                    </div>
                                    <i class="bi bi-tags text-info fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Welcome Message -->
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-shield-check text-success fs-1 mb-3"></i>
                            <h3><?= t('Welcome to Admin Dashboard', $current_language) ?></h3>
                            <p class="text-muted"><?= t('Manage your IdeaVote platform from here', $current_language) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Users Tab -->
                <?php if ($active_tab === 'users'): ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><?= t('Users Management', $current_language) ?></h2>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= t('ID', $current_language) ?></th>
                                            <th><?= t('Username', $current_language) ?></th>
                                            <th><?= t('Email', $current_language) ?></th>
                                            <th><?= t('Role', $current_language) ?></th>
                                            <th><?= t('Joined', $current_language) ?></th>
                                            <th><?= t('Actions', $current_language) ?></th>
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
                                                        <span class="badge bg-danger"><?= t('Admin', $current_language) ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?= t('User', $current_language) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $user['created_at'] ?></td>
                                                <td>
                                                    <?php if ($user['id'] != 1): ?>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('<?= t('Are you sure you want to delete this user?', $current_language) ?>')">
                                                            <input type="hidden" name="action" value="delete_user">
                                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger"><?= t('Delete', $current_language) ?></button>
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
                        <h2><?= t('Ideas Management', $current_language) ?></h2>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= t('ID', $current_language) ?></th>
                                            <th><?= t('Title', $current_language) ?></th>
                                            <th><?= t('Author', $current_language) ?></th>
                                            <th><?= t('Category', $current_language) ?></th>
                                            <th><?= t('Votes', $current_language) ?></th>
                                            <th><?= t('Featured', $current_language) ?></th>
                                            <th><?= t('Created', $current_language) ?></th>
                                            <th><?= t('Actions', $current_language) ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ideas as $idea): ?>
                                            <tr>
                                                <td><?= $idea['id'] ?></td>
                                                <td><?= htmlspecialchars($idea['title']) ?></td>
                                                <td><?= htmlspecialchars($idea['username']) ?></td>
                                                <td><?= htmlspecialchars($idea['category_name'] ?? t('Uncategorized', $current_language)) ?></td>
                                                <td><?= $idea['votes_count'] ?></td>
                                                <td>
                                                    <?php if ($idea['is_featured']): ?>
                                                        <span class="badge bg-warning"><?= t('Featured', $current_language) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $idea['created_at'] ?></td>
                                                <td>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="toggle_featured">
                                                        <input type="hidden" name="idea_id" value="<?= $idea['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-warning"><?= t('Toggle Featured', $current_language) ?></button>
                                                    </form>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('<?= t('Are you sure you want to delete this idea?', $current_language) ?>')">
                                                        <input type="hidden" name="action" value="delete_idea">
                                                        <input type="hidden" name="idea_id" value="<?= $idea['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger"><?= t('Delete', $current_language) ?></button>
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
                        <h2><?= t('Categories Management', $current_language) ?></h2>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="categoriesTable">
                                    <thead>
                                        <tr>
                                            <th><?= t('ID', $current_language) ?></th>
                                            <th><?= t('Name (English)', $current_language) ?></th>
                                            <th><?= t('Name (Arabic)', $current_language) ?></th>
                                            <th><?= t('Description', $current_language) ?></th>
                                            <th><?= t('Actions', $current_language) ?></th>
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
                                                    <button class="btn btn-sm btn-primary"><?= t('Edit', $current_language) ?></button>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('<?= t('Are you sure you want to delete this category?', $current_language) ?>')">
                                                        <input type="hidden" name="action" value="delete_category">
                                                        <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger"><?= t('Delete', $current_language) ?></button>
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
                        <h2><?= t('Audit Log', $current_language) ?></h2>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= t('ID', $current_language) ?></th>
                                            <th><?= t('Admin', $current_language) ?></th>
                                            <th><?= t('Action', $current_language) ?></th>
                                            <th><?= t('Table', $current_language) ?></th>
                                            <th><?= t('Record ID', $current_language) ?></th>
                                            <th><?= t('IP Address', $current_language) ?></th>
                                            <th><?= t('Date', $current_language) ?></th>
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
                        <h2><?= t('Reported Content', $current_language) ?></h2>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= t('ID', $current_language) ?></th>
                                            <th><?= t('Reporter', $current_language) ?></th>
                                            <th><?= t('Content Type', $current_language) ?></th>
                                            <th><?= t('Content ID', $current_language) ?></th>
                                            <th><?= t('Reason', $current_language) ?></th>
                                            <th><?= t('Status', $current_language) ?></th>
                                            <th><?= t('Date', $current_language) ?></th>
                                            <th><?= t('Actions', $current_language) ?></th>
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
                                                    <button class="btn btn-sm btn-primary"><?= t('Review', $current_language) ?></button>
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
                        <h2><?= t('Settings', $current_language) ?></h2>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><?= t('Platform Settings', $current_language) ?></h5>
                                </div>
                                <div class="card-body">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#platformSettingsModal">
                                        <i class="bi bi-gear me-2"></i><?= t('Configure Platform', $current_language) ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><?= t('User Management', $current_language) ?></h5>
                                </div>
                                <div class="card-body">
                                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#userManagementModal">
                                        <i class="bi bi-people me-2"></i><?= t('Manage Users', $current_language) ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= t('Add New User', $current_language) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_user">
                        <div class="mb-3">
                            <label class="form-label"><?= t('Username', $current_language) ?></label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= t('Email', $current_language) ?></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= t('Password', $current_language) ?></label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_admin" value="1">
                                <label class="form-check-label"><?= t('Admin User', $current_language) ?></label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= t('Cancel', $current_language) ?></button>
                        <button type="submit" class="btn btn-primary"><?= t('Add User', $current_language) ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= t('Edit User', $current_language) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_user">
                        <input type="hidden" name="user_id" id="editUserId">
                        <div class="mb-3">
                            <label class="form-label"><?= t('Username', $current_language) ?></label>
                            <input type="text" class="form-control" name="username" id="editUsername" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= t('Email', $current_language) ?></label>
                            <input type="email" class="form-control" name="email" id="editEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= t('New Password', $current_language) ?> (<?= t('leave blank to keep current', $current_language) ?>)</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_admin" value="1" id="editIsAdmin">
                                <label class="form-check-label"><?= t('Admin User', $current_language) ?></label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= t('Cancel', $current_language) ?></button>
                        <button type="submit" class="btn btn-primary"><?= t('Update User', $current_language) ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= t('Add New Category', $current_language) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_category">
                        <div class="mb-3">
                            <label class="form-label"><?= t('Name (English)', $current_language) ?></label>
                            <input type="text" class="form-control" name="name_en" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= t('Name (Arabic)', $current_language) ?></label>
                            <input type="text" class="form-control" name="name_ar" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= t('Description', $current_language) ?></label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= t('Cancel', $current_language) ?></button>
                        <button type="submit" class="btn btn-primary"><?= t('Add Category', $current_language) ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= t('Edit Category', $current_language) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_category">
                        <input type="hidden" name="category_id" id="editCategoryId">
                        <div class="mb-3">
                            <label class="form-label"><?= t('Name (English)', $current_language) ?></label>
                            <input type="text" class="form-control" name="name_en" id="editCategoryNameEn" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= t('Name (Arabic)', $current_language) ?></label>
                            <input type="text" class="form-control" name="name_ar" id="editCategoryNameAr" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= t('Description', $current_language) ?></label>
                            <textarea class="form-control" name="description" id="editCategoryDescription" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= t('Cancel', $current_language) ?></button>
                        <button type="submit" class="btn btn-primary"><?= t('Update Category', $current_language) ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- View Idea Details Modal -->
    <div class="modal fade" id="viewIdeaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= t('Idea Details', $current_language) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="ideaDetailsContent">
                        <!-- Content will be loaded dynamically -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= t('Close', $current_language) ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Platform Settings Modal -->
    <div class="modal fade" id="platformSettingsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= t('Platform Settings', $current_language) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_platform_settings">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><?= t('General Settings', $current_language) ?></h6>
                                <div class="mb-3">
                                    <label class="form-label"><?= t('Site Name', $current_language) ?></label>
                                    <input type="text" class="form-control" name="site_name" value="IdeaVote">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= t('Default Language', $current_language) ?></label>
                                    <select class="form-control" name="default_language">
                                        <option value="en">English</option>
                                        <option value="ar">العربية</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6><?= t('Content Settings', $current_language) ?></h6>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="auto_approve_ideas" value="1">
                                        <label class="form-check-label"><?= t('Auto-approve new ideas', $current_language) ?></label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="allow_anonymous_votes" value="1">
                                        <label class="form-check-label"><?= t('Allow anonymous voting', $current_language) ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= t('Cancel', $current_language) ?></button>
                        <button type="submit" class="btn btn-primary"><?= t('Save Settings', $current_language) ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- User Management Modal -->
    <div class="modal fade" id="userManagementModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= t('User Management', $current_language) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6><?= t('User Actions', $current_language) ?></h6>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="bi bi-plus"></i> <?= t('Add User', $current_language) ?>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th><?= t('Username', $current_language) ?></th>
                                    <th><?= t('Email', $current_language) ?></th>
                                    <th><?= t('Role', $current_language) ?></th>
                                    <th><?= t('Actions', $current_language) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($users, 0, 10) as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <?php if ($user['is_admin']): ?>
                                                <span class="badge bg-danger"><?= t('Admin', $current_language) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?= t('User', $current_language) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="editUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>', '<?= htmlspecialchars($user['email']) ?>', <?= $user['is_admin'] ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= t('Close', $current_language) ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Apply theme based on user preference
        function applyTheme(theme) {
            const body = document.body;
            
            // Remove existing theme classes
            body.classList.remove('dark-mode', 'light-mode');
            
            if (theme === 'dark') {
                body.classList.add('dark-mode');
                console.log('Dark mode applied');
            } else if (theme === 'light') {
                body.classList.add('light-mode');
                console.log('Light mode applied');
            }
        }
        
        // Apply current theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, applying theme:', '<?= $current_theme ?>');
            applyTheme('<?= $current_theme ?>');
        });
        
        // Edit user function
        function editUser(userId, username, email, isAdmin) {
            document.getElementById('editUserId').value = userId;
            document.getElementById('editUsername').value = username;
            document.getElementById('editEmail').value = email;
            document.getElementById('editIsAdmin').checked = isAdmin;
            
            // Close user management modal and open edit modal
            const userManagementModal = bootstrap.Modal.getInstance(document.getElementById('userManagementModal'));
            userManagementModal.hide();
            
            setTimeout(() => {
                const editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
                editUserModal.show();
            }, 300);
        }
        
        // Edit category function
        function editCategory(categoryId, nameEn, nameAr, description) {
            document.getElementById('editCategoryId').value = categoryId;
            document.getElementById('editCategoryNameEn').value = nameEn;
            document.getElementById('editCategoryNameAr').value = nameAr;
            document.getElementById('editCategoryDescription').value = description || '';
            
            const editCategoryModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
            editCategoryModal.show();
        }
        
        // View idea details function
        function viewIdeaDetails(ideaId) {
            const ideaDetailsContent = document.getElementById('ideaDetailsContent');
            ideaDetailsContent.innerHTML = '<div class="text-center"><i class="bi bi-hourglass-split"></i> Loading...</div>';
            
            const viewIdeaModal = new bootstrap.Modal(document.getElementById('viewIdeaModal'));
            viewIdeaModal.show();
            
            setTimeout(() => {
                ideaDetailsContent.innerHTML = `
                    <div class="row">
                        <div class="col-md-8">
                            <h5>Idea Title</h5>
                            <p class="text-muted">Detailed description of the idea...</p>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6>Statistics</h6>
                                    <p>Votes: 15</p>
                                    <p>Comments: 8</p>
                                    <p>Views: 120</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }, 1000);
        }
        
        // Add click handlers for edit buttons in tables
        document.addEventListener('DOMContentLoaded', function() {
            // Add edit buttons to category table
            const categoryRows = document.querySelectorAll('#categoriesTable tbody tr');
            categoryRows.forEach(row => {
                const cells = row.cells;
                const categoryId = row.querySelector('td:first-child').textContent;
                const nameEn = cells[1].textContent;
                const nameAr = cells[2].textContent;
                const description = cells[3].textContent;
                
                const editBtn = row.querySelector('.btn-primary');
                if (editBtn) {
                    editBtn.onclick = () => editCategory(categoryId, nameEn, nameAr, description);
                }
            });
        });
    </script>
</body>
</html>