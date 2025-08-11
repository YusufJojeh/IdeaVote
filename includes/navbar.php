<?php
require_once __DIR__ . '/auth.php';
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
?>
<nav class="navbar navbar-expand-lg navbar-light navbar-lux sticky-top py-3 mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold gold-gradient fs-3 d-flex align-items-center gap-2" href="index.php">
            <i class="bi bi-lightbulb-fill icon-gold"></i> IdeaVote
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="ideas.php">Browse Ideas</a></li>
                <?php if ($is_logged_in && $is_admin): ?>
                    <li class="nav-item"><a class="nav-link" href="admin.php">Admin Panel</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger notification-badge" id="notificationCount" style="display: none;">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown" id="notificationsList">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="#" id="markAllRead">Mark all as read</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-plus me-2"></i>Submit Idea</a></li>
                            <li><a class="dropdown-item" href="#" id="darkModeToggle"><i class="fas fa-moon me-2"></i>Dark Mode</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php elseif ($is_logged_in): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger notification-badge" id="notificationCount" style="display: none;">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown" id="notificationsList">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="#" id="markAllRead">Mark all as read</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-plus me-2"></i>Submit Idea</a></li>
                            <li><a class="dropdown-item" href="#" id="darkModeToggle"><i class="fas fa-moon me-2"></i>Dark Mode</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-gold px-4 ms-2" href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
// Notifications system
document.addEventListener('DOMContentLoaded', function() {
    const notificationCount = document.getElementById('notificationCount');
    const notificationsList = document.getElementById('notificationsList');
    const markAllRead = document.getElementById('markAllRead');
    
    // Load notifications on page load
    loadNotifications();
    
    // Refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
    
    // Mark all as read
    markAllRead?.addEventListener('click', function(e) {
        e.preventDefault();
        markAllNotificationsRead();
    });
    
    function loadNotifications() {
        fetch('/actions/notifications.php?limit=5')
            .then(response => response.json())
            .then(data => {
                updateNotificationBadge(data.unread_count);
                updateNotificationsList(data.notifications);
            })
            .catch(error => console.error('Error loading notifications:', error));
    }
    
    function updateNotificationBadge(count) {
        if (count > 0) {
            notificationCount.textContent = count;
            notificationCount.style.display = 'inline';
        } else {
            notificationCount.style.display = 'none';
        }
    }
    
    function updateNotificationsList(notifications) {
        // Clear existing notifications (except header and mark all read)
        const items = notificationsList.querySelectorAll('.notification-item');
        items.forEach(item => item.remove());
        
        if (notifications.length === 0) {
            const noNotifications = document.createElement('li');
            noNotifications.className = 'dropdown-item text-muted text-center';
            noNotifications.textContent = 'No notifications';
            notificationsList.insertBefore(noNotifications, markAllRead.parentElement);
        } else {
            notifications.forEach(notification => {
                const item = document.createElement('li');
                item.className = 'notification-item';
                item.innerHTML = `
                    <a class="dropdown-item ${notification.is_read ? '' : 'fw-bold'}" href="#" data-notification-id="${notification.id}">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">${notification.time_ago}</small>
                            ${notification.is_read ? '' : '<span class="badge bg-primary">New</span>'}
                        </div>
                        <div class="notification-title">${notification.title}</div>
                        <small class="text-muted">${notification.message}</small>
                    </a>
                `;
                notificationsList.insertBefore(item, markAllRead.parentElement);
                
                // Mark as read when clicked
                item.querySelector('a').addEventListener('click', function(e) {
                    e.preventDefault();
                    markNotificationRead(notification.id);
                });
            });
        }
    }
    
    function markNotificationRead(notificationId) {
        const formData = new FormData();
        formData.append('action', 'mark_read');
        formData.append('notification_id', notificationId);
        formData.append('csrf_token', '<?= csrf_token() ?>');
        
        fetch('/actions/notifications.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            if (result === 'success') {
                loadNotifications();
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    }
    
    function markAllNotificationsRead() {
        const formData = new FormData();
        formData.append('action', 'mark_all_read');
        formData.append('csrf_token', '<?= csrf_token() ?>');
        
        fetch('/actions/notifications.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            if (result === 'success') {
                loadNotifications();
            }
        })
        .catch(error => console.error('Error marking all notifications as read:', error));
    }
});

// Dark mode toggle
document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const html = document.documentElement;
    
    // Check for saved theme preference or default to 'light'
    const currentTheme = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-bs-theme', currentTheme);
    
    darkModeToggle?.addEventListener('click', function(e) {
        e.preventDefault();
        const currentTheme = html.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        html.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Update button text
        const icon = this.querySelector('i');
        if (newTheme === 'dark') {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
            this.innerHTML = '<i class="fas fa-sun me-2"></i>Light Mode';
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
            this.innerHTML = '<i class="fas fa-moon me-2"></i>Dark Mode';
        }
    });
    
    // Set initial button state
    if (currentTheme === 'dark') {
        const icon = darkModeToggle?.querySelector('i');
        if (icon) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
            darkModeToggle.innerHTML = '<i class="fas fa-sun me-2"></i>Light Mode';
        }
    }
});
</script> 