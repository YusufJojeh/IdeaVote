<?php
/**
 * Main navigation bar
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/notifications.php';
require_once __DIR__ . '/i18n.php';
// Get unread notification count if user is logged in
$unread_notification_count = 0;
if (is_logged_in()) {
    $unread_notification_count = get_unread_notifications_count(current_user_id());
}
?>
<link rel="stylesheet" href="assets/css/app.css">
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <span style="font-weight: 700; background: linear-gradient(90deg, var(--gold) 0%, var(--gold-light) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                IdeaVote
            </span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><?= __('Home') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="ideas.php"><?= __('Ideas') ?></a>
                </li>
                <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><?= __('My Ideas') ?></a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php"><?= __('Contact Us') ?></a>
                </li>
                <?php if (is_admin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php">
                            <i class="bi bi-shield-lock"></i> <?= __('Admin') ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav ms-auto">
                <?php if (is_logged_in()): ?>
                    <!-- Notifications Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge" style="display: none;">
                                0
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" style="width: 320px; max-height: 400px; overflow-y: auto;">
                            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                                <h6 class="mb-0"><?= __('Notifications') ?></h6>
                                <a href="#" class="text-decoration-none small" onclick="markAllAsRead()"><?= __('Mark all as read') ?></a>
                            </div>
                            <div id="notificationsContainer">
                                <div class="dropdown-item text-center py-3">
                                    <span class="text-muted"><?= __('Loading...') ?></span>
                                </div>
                            </div>
                            
                            <div class="dropdown-divider mb-0"></div>
                            <a class="dropdown-item text-center small" href="notifications.php">
                                <?= __('View all notifications') ?>
                            </a>
                        </div>
                    </li>
                    
                    <!-- Language Switcher for Logged-in Users -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="languageDropdownLoggedIn" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="<?= __('Language') ?>">
                            <i class="bi bi-globe"></i>
                            <span class="ms-1 d-none d-sm-inline"><?= current_language() === 'en' ? 'EN' : 'عربي' ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdownLoggedIn">
                            <li>
                                <a class="dropdown-item <?= current_language() === 'en' ? 'active' : '' ?>" href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>?lang=en">
                                    <i class="bi bi-check2 <?= current_language() === 'en' ? '' : 'invisible' ?> me-2"></i>
                                    English
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?= current_language() === 'ar' ? 'active' : '' ?>" href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>?lang=ar">
                                    <i class="bi bi-check2 <?= current_language() === 'ar' ? '' : 'invisible' ?> me-2"></i>
                                    العربية
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- User Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= h(current_username()) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="profile.php">
                                    <i class="bi bi-person me-2"></i> <?= __('Profile') ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="settings.php">
                                    <i class="bi bi-gear me-2"></i> <?= __('Settings') ?>
                                </a>
                            </li>
                            <li>
                                <button class="dropdown-item theme-toggle">
                                    <i class="bi bi-moon me-2"></i> <?= __('Toggle Theme') ?>
                                </button>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i> <?= __('Logout') ?>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Language Switcher for Anonymous Users -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Language">
                            <i class="bi bi-globe"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                            <li>
                                <a class="dropdown-item <?= current_language() === 'en' ? 'active' : '' ?>" href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>?lang=en">
                                    English
                                </a>
                    </li>
                            <li>
                                <a class="dropdown-item <?= current_language() === 'ar' ? 'active' : '' ?>" href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>?lang=ar">
                                    العربية
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- Theme Toggle for Anonymous Users -->
                    <li class="nav-item">
                        <button class="nav-link theme-toggle border-0 bg-transparent" id="themeToggleBtn" title="<?= __('Toggle Theme') ?>">
                            <i class="bi bi-moon" id="themeIcon"></i>
                        </button>
                    </li>
                    
                    <!-- Login/Register Links -->
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><?= __('Login') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-gold text-dark px-3 py-1 ms-2" href="register.php"><?= __('Register') ?></a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if (is_logged_in()): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load notifications
    loadNotifications();
    
    // Refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
});

function loadNotifications() {
    fetch('actions/notifications.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            const container = document.getElementById('notificationsContainer');
            const badge = document.getElementById('notificationBadge');
            
            if (data.success && data.notifications && data.notifications.length > 0) {
                container.innerHTML = '';
                
                // Update badge count
                if (badge && data.unread_count > 0) {
                    badge.textContent = data.unread_count;
                    badge.style.display = 'block';
                } else if (badge) {
                    badge.style.display = 'none';
                }
                
                data.notifications.forEach(notification => {
                    const notificationItem = document.createElement('div');
                    notificationItem.className = `dropdown-item d-flex align-items-start py-2 ${notification.is_read ? '' : 'bg-light'}`;
                    
                    let iconClass = 'bi-bell';
                    if (notification.type === 'vote') iconClass = 'bi-hand-thumbs-up';
                    if (notification.type === 'comment') iconClass = 'bi-chat-text';
                    if (notification.type === 'follow') iconClass = 'bi-person-plus';
                    if (notification.type === 'system') iconClass = 'bi-info-circle';
                    if (notification.type === 'bookmark') iconClass = 'bi-bookmark';
                    if (notification.type === 'reaction') iconClass = 'bi-emoji-smile';
                    
                    notificationItem.innerHTML = `
                        <div class="me-3">
                            <i class="bi ${iconClass} text-gold"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <strong>${notification.title}</strong>
                                <small class="text-muted">${notification.time_ago}</small>
                            </div>
                            <div>${notification.message}</div>
                        </div>
                    `;
                    
                    container.appendChild(notificationItem);
                });
            } else {
                container.innerHTML = `
                    <div class="dropdown-item text-center py-3">
                        <span class="text-muted"><?= __('No notifications') ?></span>
                    </div>
                `;
                
                // Hide badge if no unread notifications
                if (badge) {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            document.getElementById('notificationsContainer').innerHTML = `
                <div class="dropdown-item text-center py-3">
                    <span class="text-danger"><?= __('Failed to load notifications') ?></span>
                    <br><small class="text-muted">Please refresh the page</small>
                </div>
            `;
        });
}
</script> 
<?php endif; ?>