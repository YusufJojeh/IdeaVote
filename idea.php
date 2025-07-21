<?php
ob_start();
include 'includes/navbar.php';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$idea_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($idea_id <= 0) {
    header('Location: ideas.php');
    exit();
}
$stmt = mysqli_prepare($conn, "SELECT * FROM ideas WHERE id=?");
mysqli_stmt_bind_param($stmt, 'i', $idea_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$idea = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

// Fetch author info
$stmt = mysqli_prepare($conn, "SELECT username, bio FROM users WHERE id=?");
mysqli_stmt_bind_param($stmt, 'i', $idea['user_id']);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$author = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$idea || (!$idea['is_public'] && (!is_logged_in() || current_user_id() != $idea['user_id'] && !is_admin()))) {
    header('Location: ideas.php');
    exit();
}

// Handle idea edit and delete
if (is_logged_in() && current_user_id() == $idea['user_id']) {
    if (isset($_POST['delete_idea'])) {
        $stmt = mysqli_prepare($conn, "DELETE FROM ideas WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'i', $idea_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header('Location: dashboard.php?msg=idea_deleted');
        exit();
    }
    if (isset($_POST['edit_idea'])) {
        $new_title = trim($_POST['edit_title']);
        $new_desc = trim($_POST['edit_description']);
        $new_image_url = trim($_POST['edit_image_url']);
        $new_is_public = isset($_POST['edit_is_public']) ? 1 : 0;
        // Handle file upload
        if (isset($_FILES['edit_image_file']) && $_FILES['edit_image_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['edit_image_file']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp','svg'];
            if (in_array($ext, $allowed)) {
                $newname = 'idea_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                $dest = 'assets/images/ideas/' . $newname;
                if (!is_dir('assets/images/ideas')) mkdir('assets/images/ideas', 0777, true);
                if (move_uploaded_file($_FILES['edit_image_file']['tmp_name'], $dest)) {
                    $new_image_url = $dest;
                }
            }
        }
        $stmt = mysqli_prepare($conn, "UPDATE ideas SET title=?, description=?, image_url=?, is_public=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'sssii', $new_title, $new_desc, $new_image_url, $new_is_public, $idea_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header('Location: idea.php?id=' . $idea_id . '&msg=updated');
        exit();
    }
}
if (isset($_GET['msg']) && $_GET['msg'] === 'updated') {
    echo '<div class="alert alert-success">Idea updated successfully!</div>';
}

// Voting logic
$vote_msg = '';
if (is_logged_in() && isset($_POST['vote_type'])) {
    $vote_type = $_POST['vote_type'] === 'like' ? 'like' : 'dislike';
    $stmt = mysqli_prepare($conn, "SELECT id FROM votes WHERE user_id=? AND idea_id=?");
    $uid = current_user_id();
    mysqli_stmt_bind_param($stmt, 'ii', $uid, $idea_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) == 0) {
        mysqli_stmt_close($stmt);
        $stmt = mysqli_prepare($conn, "INSERT INTO votes (user_id, idea_id, vote_type) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iis', $uid, $idea_id, $vote_type);
        if (mysqli_stmt_execute($stmt)) {
            $vote_msg = 'Your vote has been recorded!';
        } else {
            $vote_msg = 'Failed to record vote.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $vote_msg = 'You have already voted on this idea.';
        mysqli_stmt_close($stmt);
    }
}

// Comment logic
$comment_msg = '';
if (is_logged_in() && isset($_POST['comment']) && trim($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    if (strlen($comment) < 2) {
        $comment_msg = 'Comment is too short.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO comments (user_id, idea_id, comment) VALUES (?, ?, ?)");
        $uid = current_user_id();
        mysqli_stmt_bind_param($stmt, 'iis', $uid, $idea_id, $comment);
        if (mysqli_stmt_execute($stmt)) {
            $comment_msg = 'Comment added!';
        } else {
            $comment_msg = 'Failed to add comment.';
        }
        mysqli_stmt_close($stmt);
    }
}

// Get votes and comments
$votes = get_vote_counts($idea_id);
$comments = [];
$stmt = mysqli_prepare($conn, "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id=u.id WHERE c.idea_id=? ORDER BY c.created_at ASC");
mysqli_stmt_bind_param($stmt, 'i', $idea_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) {
    $comments[] = $row;
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($idea['title']) ?> - IdeaVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #FFD700;
            --gold-light: #FFEF8E;
            --black: #181818;
            --gray: #444;
            --offwhite: #f8fafc;
        }
        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            background: var(--offwhite);
            color: var(--black);
            min-height: 100vh;
        }
        .idea-main-card {
            background: rgba(255,255,255,0.92);
            border-radius: 36px;
            box-shadow: 0 8px 32px 0 rgba(24,24,24,0.10);
            border: 1.5px solid #eee;
            padding: 2.5rem 2rem;
            margin-bottom: 2rem;
            max-width: 700px;
            margin-left:auto; margin-right:auto;
        }
        .idea-img-lg {
            width: 100%;
            max-width: 420px;
            max-height: 320px;
            object-fit: cover;
            border-radius: 24px;
            box-shadow: 0 4px 24px 0 rgba(255,215,0,0.10);
            margin-bottom: 1.5rem;
            background: #f8fafc;
        }
        .gold-gradient {
            background: linear-gradient(90deg, var(--gold) 0%, var(--gold-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: var(--gold);
        }
        .idea-title {
            font-size: 2.1rem;
            font-weight: 800;
            color: var(--black);
            letter-spacing: 1px;
        }
        .idea-meta {
            color: #888;
            font-size: 1.05em;
            margin-bottom: 0.7em;
        }
        .idea-desc {
            color: var(--gray);
            font-size: 1.15em;
            margin-bottom: 1.5em;
        }
        .vote-badges {
            font-size: 1.2em;
            margin-bottom: 1.2em;
        }
        .vote-badges .like {
            color: var(--gold);
            margin-right: 1.5em;
        }
        .vote-badges .dislike {
            color: #888;
        }
        .btn-glass {
            background: linear-gradient(90deg, var(--gold) 0%, var(--gold-light) 100%);
            color: var(--black);
            font-weight: 600;
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 12px rgba(255,215,0,0.10);
            transition: background 0.2s, color 0.2s;
        }
        .btn-glass:hover {
            background: var(--gold-light);
            color: var(--black);
        }
        .edit-controls { margin-bottom: 1.5em; }
        .comment-section {
            background: rgba(255,255,255,0.92);
            border-radius: 24px;
            box-shadow: 0 4px 24px 0 rgba(24,24,24,0.08);
            border: 1.5px solid #eee;
            padding: 2rem 1.5rem;
            max-width: 700px;
            margin-left:auto; margin-right:auto;
        }
        .comment-glass {
            background: rgba(255,255,255,0.98);
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(24,24,24,0.06);
            border: 1.5px solid #eee;
            margin-bottom: 1.2rem;
            padding: 1.2em;
        }
        .comment-username { color: var(--gold); font-weight: 600; }
        .comment-date { color: #888; font-size: 0.95em; }
        .idea-details-side {
            background: rgba(255,255,255,0.85);
            border-radius: 24px;
            box-shadow: 0 4px 24px 0 rgba(24,24,24,0.08);
            border: 1.5px solid #eee;
            padding: 1.5rem 1.2rem;
        }
        @media (max-width: 767px) {
            .idea-main-card, .comment-section { padding: 1.2rem 0.5rem; }
            .idea-img-lg { max-width: 100%; height: auto; }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 mb-4">
                <div class="idea-main-card">
                    <?php if (!empty($idea['image_url'])): ?>
                        <div class="text-center">
                            <img src="<?= htmlspecialchars($idea['image_url']) ?>" alt="Idea Image for <?= htmlspecialchars($idea['title']) ?>" class="idea-img-lg">
                        </div>
                    <?php endif; ?>
                    <div class="d-flex align-items-center mb-2">
                        <div class="idea-title gold-gradient flex-grow-1"><i class="bi bi-lightbulb icon-gold"></i> <?= htmlspecialchars($idea['title']) ?></div>
                    </div>
                    <div class="idea-meta">Category: <?= htmlspecialchars(get_category_name($idea['category_id'], 'en')) ?> &bull; <?= htmlspecialchars($idea['created_at']) ?> &bull; <span>By <a href="profile_others.php?user_id=<?= $idea['user_id'] ?>" class="gold-gradient" style="text-decoration:underline;"> <?= htmlspecialchars($author['username']) ?></a></span></div>
                    <div class="idea-desc"> <?= nl2br(htmlspecialchars($idea['description'])) ?> </div>
                    <div class="vote-badges">
                        <span class="like"><i class="bi bi-hand-thumbs-up-fill"></i> <?= $votes['like'] ?></span>
                        <span class="dislike"><i class="bi bi-hand-thumbs-down-fill"></i> <?= $votes['dislike'] ?></span>
                    </div>
                    <?php if (is_logged_in()): ?>
                        <form method="POST" class="d-flex gap-2 mb-3">
                            <button type="submit" name="vote_type" value="like" class="btn btn-glass"><i class="bi bi-hand-thumbs-up"></i> Like</button>
                            <button type="submit" name="vote_type" value="dislike" class="btn btn-outline-secondary"><i class="bi bi-hand-thumbs-down"></i> Dislike</button>
                        </form>
                        <?php if ($vote_msg): ?>
                            <div class="alert alert-info py-2"> <?= htmlspecialchars($vote_msg) ?> </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (is_logged_in() && current_user_id() == $idea['user_id']): ?>
                        <div class="edit-controls d-flex gap-2">
                            <button class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#editIdeaForm"><i class="bi bi-pencil"></i> Edit</button>
                            <form method="POST" onsubmit="return confirm('Delete this idea?');" style="display:inline;">
                                <button type="submit" name="delete_idea" class="btn btn-danger"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                        <div class="collapse mb-4" id="editIdeaForm">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-2">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="edit_title" value="<?= htmlspecialchars($idea['title']) ?>" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="edit_description" rows="3" required><?= htmlspecialchars($idea['description']) ?></textarea>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Upload New Image</label>
                                    <input type="file" class="form-control" name="edit_image_file" accept="image/*" onchange="previewEditIdeaImage(event)">
                                    <div id="editIdeaImagePreview" class="mt-2" style="display:none;">
                                        <img src="#" alt="Preview" class="rounded shadow" style="max-width:180px;max-height:120px;object-fit:cover;">
                                    </div>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="edit_is_public" id="edit_is_public" <?= $idea['is_public'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="edit_is_public">Public (visible to everyone)</label>
                                </div>
                                <button type="submit" name="edit_idea" class="btn btn-gold">Save Changes</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="comment-section mt-4">
                    <h4 class="mb-3 gold-gradient"><i class="bi bi-chat-dots icon-gold"></i> Comments</h4>
                    <?php if (is_logged_in()): ?>
                        <form method="POST" class="mb-3">
                            <div class="input-group">
                                <input type="text" name="comment" class="form-control" placeholder="Add a comment..." required>
                                <button class="btn btn-gold" type="submit">Post</button>
                            </div>
                        </form>
                        <?php if ($comment_msg): ?>
                            <div class="alert alert-info py-2"> <?= htmlspecialchars($comment_msg) ?> </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (empty($comments)): ?>
                        <div class="alert alert-info">No comments yet.</div>
                    <?php else: ?>
                        <?php foreach ($comments as $c): ?>
                            <div class="comment-glass">
                                <span class="comment-username"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($c['username']) ?>:</span>
                                <?= nl2br(htmlspecialchars($c['comment'])) ?>
                                <div class="comment-date mt-1"> <?= htmlspecialchars($c['created_at']) ?> </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="idea-details-side">
                    <h5 class="gold-gradient">Idea Details</h5>
                    <ul class="list-unstyled mb-0">
                        <li><strong>Author:</strong> <a href="profile_others.php?user_id=<?= $idea['user_id'] ?>" class="gold-gradient" style="text-decoration:underline;">
                            <?= htmlspecialchars($author['username']) ?></a>
                        <?php if (!empty($author['bio'])): ?> – <?= htmlspecialchars($author['bio']) ?><?php endif; ?>
                        </li>
                        <li><strong>Category:</strong> <?= htmlspecialchars(get_category_name($idea['category_id'], 'en')) ?></li>
                        <li><strong>Visibility:</strong> <?= $idea['is_public'] ? 'Public' : 'Private' ?></li>
                        <li><strong>Created:</strong> <?= htmlspecialchars($idea['created_at']) ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <footer class="py-4 footer-lux text-center border-0 mt-5">
        <div class="container">
            <div class="mb-2">
                <a href="contact.php" class="footer-link">Contact</a>
                <a href="ideas.php" class="footer-link">Browse Ideas</a>
                <a href="register.php" class="footer-link">Register</a>
            </div>
            <small style="color: var(--gray);">All rights reserved &copy; Idea Voting Platform 2024</small>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
function previewEditIdeaImage(event) {
    const input = event.target;
    const previewDiv = document.getElementById('editIdeaImagePreview');
    const img = previewDiv.querySelector('img');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            previewDiv.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        previewDiv.style.display = 'none';
    }
}
</script>
</body>
</html> 