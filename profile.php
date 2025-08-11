<?php
ob_start();
include 'includes/config.php';
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/auth.php';
include 'includes/csrf.php';
include 'includes/upload.php';
include 'includes/i18n.php';
include 'includes/notifications.php';
require_login();

$user_id = current_user_id();
$user = [];
$edit_errors = [];
$edit_success = false;

// Get user data
$stmt = $pdo->prepare("SELECT email, password, created_at, bio, image_url FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle account deletion
if (isset($_POST['delete_account'])) {
    if (csrf_verify()) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
        $stmt->execute([$user_id]);
        session_destroy();
        header('Location: index.php');
        exit();
    }
}

// Handle profile update
if (isset($_POST['update_profile'])) {
    if (csrf_verify()) {
        $new_email = trim($_POST['email'] ?? '');
        $new_password = $_POST['password'] ?? '';
        $new_bio = trim($_POST['bio'] ?? '');
        $new_image_url = $user['image_url'];

        // Handle image upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $up = upload_image($_FILES['avatar'], 'users');
            if ($up['ok']) {
                $new_image_url = $up['path'];
            } else {
                $edit_errors[] = $up['error'] ?? 'Upload failed';
            }
        }

        // Validate email uniqueness
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email=? AND id!=?");
        $stmt->execute([$new_email, $user_id]);
        if ($stmt->rowCount() > 0) {
            $edit_errors[] = 'Email already exists.';
        }

        if (empty($edit_errors)) {
            if (!empty($new_password)) {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET email=?, password=?, bio=?, image_url=? WHERE id=?");
                $stmt->execute([$new_email, $hash, $new_bio, $new_image_url, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET email=?, bio=?, image_url=? WHERE id=?");
                $stmt->execute([$new_email, $new_bio, $new_image_url, $user_id]);
            }
            
            if ($stmt->rowCount() > 0) {
                $edit_success = true;
                $user['email'] = $new_email;
                $user['bio'] = $new_bio;
                $user['image_url'] = $new_image_url;
            }
        }
    }
}

// Get user's ideas
$my_ideas = [];
$stmt = $pdo->prepare("SELECT * FROM ideas WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$my_ideas = $stmt->fetchAll();

// Get user's votes
$my_votes = [];
$stmt = $pdo->prepare("SELECT v.vote_type, i.title, i.id as idea_id, i.category_id, i.created_at FROM votes v JOIN ideas i ON v.idea_id=i.id WHERE v.user_id=? ORDER BY v.created_at DESC");
$stmt->execute([$user_id]);
$my_votes = $stmt->fetchAll();

include 'includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - IdeaVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Inter', 'Segoe UI', Arial, sans-serif; }
        .navbar-lux { background: #fff !important; box-shadow: 0 8px 32px 0 rgba(24,24,24,0.06); border-bottom: 1px solid #eee; }
        .gold-gradient { background: linear-gradient(90deg, #FFD700 0%, #FFEF8E 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; color: #FFD700; }
        .profile-glass { background: rgba(255,255,255,0.97); box-shadow: 0 8px 32px 0 rgba(24,24,24,0.08); border-radius: 32px; border: 1.5px solid #eee; padding: 2.5rem 2rem; }
        .icon-gold { color: #FFD700; }
        .idea-card { background: #fff; border-radius: 18px; box-shadow: 0 2px 12px rgba(24,24,24,0.06); border: 1.5px solid #eee; margin-bottom: 1.2rem; }
        .idea-card .card-title { color: #181818; }
        .idea-card .badge { background: #FFD700; color: #181818; }
        .idea-card .text-muted { color: #888 !important; }
        .vote-badge { font-size: 1rem; padding: 0.5em 1em; border-radius: 12px; font-weight: 500; }
        .vote-badge.like { background: #FFD700; color: #181818; }
        .vote-badge.dislike { background: #eee; color: #888; }
    </style>
</head>
<body>
    <?php
    // The navbar is now included at the very top of the file
    ?>
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="profile-glass shadow mb-4">
                    <h3 class="mb-3 gold-gradient"><i class="bi bi-person-circle icon-gold"></i> Profile</h3>
                    <?php if ($edit_success): ?>
                        <div class="alert alert-success">Profile updated successfully.</div>
                    <?php endif; ?>
                    <?php if (!empty($edit_errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($edit_errors as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" novalidate>
                        <?= csrf_field(); ?>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                                                         <input type="text" class="form-control" value="<?= htmlspecialchars(current_username()) ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="bio" class="form-label">Job / Education / Bio</label>
                            <input type="text" class="form-control" id="bio" name="bio" value="<?= htmlspecialchars($user['bio'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                            <?php if (!empty($user['image_url'])): ?>
                                <img src="<?= htmlspecialchars($user['image_url']) ?>" alt="avatar" class="rounded-circle mt-2" width="96" height="96">
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password <span class="text-muted" style="font-weight:400;">(leave blank to keep current)</span></label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-gold w-100">Save Changes</button>
                    </form>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This cannot be undone.');">
                        <?= csrf_field(); ?>
                        <button type="submit" name="delete_account" class="btn btn-danger w-100 mt-3">Delete My Account</button>
                    </form>
                                         <img src="<?= htmlspecialchars($user['image_url'] ?? 'https://api.dicebear.com/6.x/initials/svg?seed=' . urlencode(current_username())) ?>" alt="avatar" class="rounded-circle mb-3" width="96" height="96">
                    <ul class="list-unstyled mt-4 mb-0">
                        <li><strong>Job / Education:</strong> <?= htmlspecialchars($user['bio'] ?? '') ?></li>
                        <li><strong>Member since:</strong> <?= htmlspecialchars($user['created_at']) ?></li>
                    </ul>
                </div>
                <div class="profile-glass shadow mt-4">
                    <h4 class="mb-3 gold-gradient"><i class="bi bi-lightbulb icon-gold"></i> My Ideas</h4>
                    <?php if (empty($my_ideas)): ?>
                        <div class="alert alert-info">You haven't submitted any ideas yet.</div>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($my_ideas as $idea): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center idea-card">
                                    <a href="idea.php?id=<?= $idea['id'] ?>" class="fw-bold gold-gradient"> <?= htmlspecialchars($idea['title']) ?> </a>
                                    <span class="badge"><i class="bi bi-hand-thumbs-up-fill icon-gold"></i> <?= get_vote_counts($idea['id'])['like'] ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="profile-glass shadow">
                    <h4 class="mb-3 gold-gradient"><i class="bi bi-clock-history icon-gold"></i> Voting History</h4>
                    <?php if (empty($my_votes)): ?>
                        <div class="alert alert-info">You haven't voted on any ideas yet.</div>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($my_votes as $vote): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center idea-card">
                                    <a href="idea.php?id=<?= $vote['idea_id'] ?>" class="fw-bold gold-gradient"> <?= htmlspecialchars($vote['title']) ?> </a>
                                    <span class="vote-badge <?= $vote['vote_type'] === 'like' ? 'like' : 'dislike' ?>">
                                        <i class="bi bi-<?= $vote['vote_type'] === 'like' ? 'hand-thumbs-up-fill' : 'hand-thumbs-down-fill' ?> icon-gold"></i> <?= ucfirst($vote['vote_type']) ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 