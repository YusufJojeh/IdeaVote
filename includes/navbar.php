<?php
if (session_status() === PHP_SESSION_NONE) session_start();
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
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">MakeIdea</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-gold px-4 ms-2" href="logout.php">Logout</a></li>
                <?php elseif ($is_logged_in): ?>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">MakeIdea</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-gold px-4 ms-2" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-gold px-4 ms-2" href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav> 