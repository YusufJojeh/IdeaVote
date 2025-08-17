<?php
ob_start();
include 'includes/config.php';
include 'includes/db.php';
include 'includes/i18n.php';
include 'includes/functions.php';
include 'includes/auth.php';
include 'includes/csrf.php';
include 'includes/upload.php';
include 'includes/notifications.php';

$idea_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($idea_id <= 0) {
    header('Location: ideas.php');
    exit();
}

// Increment view count
$stmt = $pdo->prepare("UPDATE ideas SET views_count = views_count + 1 WHERE id = ?");
$stmt->execute([$idea_id]);

// Fetch idea with enhanced data
$stmt = $pdo->prepare("
    SELECT i.*, u.username, u.avatar, u.bio, c.name_en as category_name, c.name_ar as category_name_ar,
           (SELECT COUNT(*) FROM votes WHERE votes.idea_id=i.id AND vote_type='like') as likes,
           (SELECT COUNT(*) FROM votes WHERE votes.idea_id=i.id AND vote_type='dislike') as dislikes,
           (SELECT COUNT(*) FROM comments WHERE comments.idea_id=i.id) as comments_count,
           (SELECT COUNT(*) FROM reactions WHERE reactions.idea_id=i.id) as reactions_count,
           (SELECT COUNT(*) FROM bookmarks WHERE bookmarks.idea_id=i.id) as bookmarks_count
    FROM ideas i 
    LEFT JOIN users u ON i.user_id = u.id 
    LEFT JOIN categories c ON i.category_id = c.id 
    WHERE i.id = ?
");
$stmt->execute([$idea_id]);
$idea = $stmt->fetch();

// Fetch author info
$author = [
    'username' => $idea['username'],
    'bio' => $idea['bio'],
    'avatar' => $idea['avatar']
];

if (!$idea || (!$idea['is_public'] && (!is_logged_in() || current_user_id() != $idea['user_id'] && !is_admin()))) {
    header('Location: ideas.php');
    exit();
}

// Handle idea edit and delete
if (is_logged_in() && current_user_id() == $idea['user_id']) {
    if (isset($_POST['delete_idea'])) {
        if (!csrf_verify()) {
            echo '<div class="alert alert-danger">CSRF verification failed.</div>';
        } else {
            $stmt = $pdo->prepare("DELETE FROM ideas WHERE id=?");
            $stmt->execute([$idea_id]);
            header('Location: dashboard.php?msg=idea_deleted');
            exit();
        }
    }
    if (isset($_POST['edit_idea'])) {
        if (!csrf_verify()) {
            echo '<div class="alert alert-danger">CSRF verification failed.</div>';
        } else {
            $new_title = trim($_POST['edit_title']);
            $new_desc = trim($_POST['edit_description']);
            $new_image_url = $idea['image_url'];
            $new_is_public = isset($_POST['edit_is_public']) ? 1 : 0;
            // Handle file upload
            if (isset($_FILES['edit_image_file']) && $_FILES['edit_image_file']['error'] === UPLOAD_ERR_OK) {
                $up = upload_image($_FILES['edit_image_file'], 'ideas');
                if ($up['ok']) {
                    $new_image_url = $up['path'];
                } else {
                    echo '<div class="alert alert-danger">Upload failed.</div>';
                }
            }
            $stmt = $pdo->prepare("UPDATE ideas SET title=?, description=?, image_url=?, is_public=? WHERE id=?");
            $stmt->execute([$new_title, $new_desc, $new_image_url, $new_is_public, $idea_id]);
            header('Location: idea.php?id=' . $idea_id . '&msg=updated');
            exit();
        }
    }
}
if (isset($_GET['msg']) && $_GET['msg'] === 'updated') {
    echo '<div class="alert alert-success">Idea updated successfully!</div>';
}

// Voting moved to actions/vote.php
$vote_msg = '';

// Comment logic
$comment_msg = '';
if (is_logged_in() && isset($_POST['comment']) && trim($_POST['comment'])) {
    if (!csrf_verify()) {
        $comment_msg = __('CSRF verification failed.');
    } else {
        $comment = trim($_POST['comment']);
        if (strlen($comment) < 2) {
            $comment_msg = __('Comment is too short.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO comments (user_id, idea_id, comment) VALUES (?, ?, ?)");
            $uid = current_user_id();
            if ($stmt->execute([$uid, $idea_id, $comment])) {
                $comment_msg = __('Comment added!');
                
                // Create notification for idea owner
                if ($idea['user_id'] != $uid) {
                    notify_comment($idea['user_id'], $uid, $idea_id, $comment);
                }
            } else {
                $comment_msg = __('Failed to add comment.');
            }
        }
    }
}

// Get votes and comments
$votes = ['like' => $idea['likes'], 'dislike' => $idea['dislikes']];
$comments = [];
$stmt = $pdo->prepare("SELECT c.*, u.username, u.avatar FROM comments c JOIN users u ON c.user_id=u.id WHERE c.idea_id=? ORDER BY c.created_at ASC");
$stmt->execute([$idea_id]);
$comments = $stmt->fetchAll();

// Get reactions for this idea
$stmt = $pdo->prepare("SELECT reaction_type, COUNT(*) as count FROM reactions WHERE idea_id = ? GROUP BY reaction_type");
$stmt->execute([$idea_id]);
$reactions = $stmt->fetchAll();

// Get current user's reaction
$user_reaction = null;
if (is_logged_in()) {
    $stmt = $pdo->prepare("SELECT reaction_type FROM reactions WHERE user_id = ? AND idea_id = ?");
    $stmt->execute([current_user_id(), $idea_id]);
    $user_reaction = $stmt->fetch();
}

// Check if user has bookmarked this idea
$user_bookmarked = false;
if (is_logged_in()) {
    $stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND idea_id = ?");
    $stmt->execute([current_user_id(), $idea_id]);
    $user_bookmarked = $stmt->fetch() ? true : false;
}

include 'includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="<?php echo current_language(); ?>" dir="<?php echo lang_dir(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($idea['title']) ?> - IdeaVote</title>
    <meta name="description" content="<?= htmlspecialchars(substr($idea['description'], 0, 160)) ?>">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?= htmlspecialchars($idea['title']) ?> - IdeaVote">
    <meta property="og:description" content="<?= htmlspecialchars(substr($idea['description'], 0, 160)) ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <?php if (!empty($idea['image_url'])): ?>
    <meta property="og:image" content="<?= htmlspecialchars($idea['image_url']) ?>">
    <?php endif; ?>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css">
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
        .btn-outline-gold{border:2px solid var(--gold);color:var(--text);background:transparent}
        .btn-outline-gold:hover{background:rgba(255,215,0,.15)}
        .section{padding:4.5rem 0}
        .subtle{color:var(--muted)}
        .rounded-3xl{border-radius:1.25rem}
        .shadow-soft{box-shadow:0 10px 30px rgba(0,0,0,.25)}
        .badge-gold{background:rgba(255,215,0,.15);border:1px solid rgba(255,215,0,.35);color:#ffe98f}

        /* Hero Section */
        .hero{position:relative;min-height:60vh;display:flex;align-items:center;background:linear-gradient(135deg, rgba(255,215,0,.1) 0%, rgba(255,215,0,.05) 100%)}
        .hero::before{content:"";position:absolute;inset:0;background:url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1900&q=80') center/cover no-repeat;filter:brightness(.15);opacity:.3}
        .hero::after{content:"";position:absolute;inset:0;background:radial-gradient(80% 60% at 50% 0%,rgba(255,215,0,.12),transparent 60%)}
        .hero .content{position:relative;z-index:2}
        .hero h1{font-weight:800;letter-spacing:-.02em}

        /* Idea Content */
        .idea-content{background:var(--card);border:1px solid var(--border);border-radius:1.5rem;padding:3rem;margin-bottom:2rem;box-shadow:0 10px 40px rgba(0,0,0,.1)}
        .idea-image{width:100%;max-height:400px;object-fit:cover;border-radius:1rem;margin-bottom:2rem;box-shadow:0 8px 25px rgba(0,0,0,.15)}
        .idea-title{font-size:2.5rem;font-weight:800;color:var(--text);margin-bottom:1rem;line-height:1.2}
        .idea-meta{color:var(--muted);font-size:1rem;margin-bottom:1.5rem}
        .idea-description{color:var(--text);font-size:1.1rem;line-height:1.7;margin-bottom:2rem}
        .idea-stats{display:flex;gap:2rem;margin-bottom:2rem;padding:1.5rem;background:rgba(255,215,0,.05);border-radius:1rem;border:1px solid rgba(255,215,0,.1)}
        .stat-item{display:flex;align-items:center;gap:.5rem;color:var(--muted);font-weight:600}
        .stat-item i{color:var(--gold);font-size:1.2rem}
        .like-count{color:#10b981}
        .dislike-count{color:#ef4444}
        .comment-count{color:#3b82f6}
        .view-count{color:#8b5cf6}
        
        /* Reactions Display */
        .reactions-display .badge{font-size:0.9rem;padding:0.5rem 0.75rem;border-radius:1rem}
        .current-reaction .badge{font-size:1rem;padding:0.5rem 1rem;border-radius:1rem}
        .bg-gold{background:var(--gold)!important;color:#111!important}
        
        /* Author Links */
        .author-link{color:var(--text);transition:color .2s}
        .author-link:hover{color:var(--gold);text-decoration:none!important}
        .comment-author a{color:var(--text);transition:color .2s}
        .comment-author a:hover{color:var(--gold);text-decoration:none!important}

        /* Action Buttons */
        .action-buttons{display:flex;gap:1rem;margin-bottom:2rem;flex-wrap:wrap}
        .btn-vote{background:var(--card);border:2px solid var(--border);border-radius:.75rem;padding:.75rem 1.5rem;font-weight:600;transition:all .2s;color:var(--text)}
        .btn-vote:hover{background:var(--gold);color:#111;border-color:var(--gold);transform:translateY(-2px)}
        .btn-vote.active{background:var(--gold);color:#111;border-color:var(--gold)}
        .btn-bookmark{position:absolute;top:2rem;right:2rem;background:rgba(255,255,255,.9);border:none;border-radius:50%;width:50px;height:50px;display:flex;align-items:center;justify-content:center;color:var(--gold);transition:all .2s;backdrop-filter:blur(10px);z-index:10}
        .btn-bookmark:hover{background:var(--gold);color:white;transform:scale(1.1)}
        .btn-bookmark.bookmarked{background:var(--gold);color:white}

        /* Author Section */
        .author-section{background:var(--card);border:1px solid var(--border);border-radius:1rem;padding:1.5rem;margin-bottom:2rem}
        .author-avatar{width:60px;height:60px;border-radius:50%;object-fit:cover;border:3px solid var(--gold);margin-right:1rem}
        .author-info h5{margin:0;font-weight:600;color:var(--text)}
        .author-bio{color:var(--muted);font-size:.9rem;margin:0}

        /* Comments Section */
        .comments-section{background:var(--card);border:1px solid var(--border);border-radius:1.5rem;padding:2rem;margin-bottom:2rem}
        .comment-form{background:rgba(255,215,0,.05);border:1px solid rgba(255,215,0,.1);border-radius:1rem;padding:1.5rem;margin-bottom:2rem}
        .comment-input{border:2px solid var(--border);border-radius:.75rem;padding:1rem;font-weight:500;transition:all .2s;background:var(--card)}
        .comment-input:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(255,215,0,.1);background:var(--card)}
        .comment-item{background:var(--card);border:1px solid var(--border);border-radius:1rem;padding:1.5rem;margin-bottom:1rem}
        .comment-header{display:flex;align-items:center;margin-bottom:.75rem}
        .comment-avatar{width:40px;height:40px;border-radius:50%;object-fit:cover;margin-right:.75rem}
        .comment-author{font-weight:600;color:var(--text);margin:0}
        .comment-date{color:var(--muted);font-size:.85rem;margin:0}
        .comment-text{color:var(--text);line-height:1.6;margin:0}

        /* Sidebar */
        .idea-sidebar{background:var(--card);border:1px solid var(--border);border-radius:1.5rem;padding:2rem;position:sticky;top:2rem}
        .sidebar-title{font-weight:700;color:var(--text);margin-bottom:1.5rem;font-size:1.2rem}
        .sidebar-item{display:flex;justify-content:space-between;align-items:center;padding:.75rem 0;border-bottom:1px solid var(--border)}
        .sidebar-item:last-child{border-bottom:none}
        .sidebar-label{color:var(--muted);font-weight:500}
        .sidebar-value{color:var(--text);font-weight:600}

        /* Edit Form */
        .edit-form{background:rgba(255,215,0,.05);border:1px solid rgba(255,215,0,.1);border-radius:1rem;padding:2rem;margin-bottom:2rem}
        .form-control,.form-select{border:2px solid var(--border);border-radius:.75rem;padding:.75rem 1rem;font-weight:500;transition:all .2s;background:var(--card)}
        .form-control:focus,.form-select:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(255,215,0,.1);background:var(--card)}

        /* Responsive */
        @media (max-width:768px){
            .hero{min-height:40vh}
            .idea-content{padding:2rem 1.5rem}
            .idea-title{font-size:2rem}
            .action-buttons{flex-direction:column}
            .btn-bookmark{top:1rem;right:1rem;width:40px;height:40px}
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <header class="hero">
        <div class="container content">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <span class="badge badge-gold rounded-pill px-3 py-2 mb-3">💡 <?php echo __('Amazing Idea'); ?></span>
                    <h1 class="display-4 mb-3"><?= htmlspecialchars($idea['title']) ?></h1>
                    <p class="lead subtle mb-4"><?= htmlspecialchars(substr($idea['description'], 0, 200)) ?>...</p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="ideas.php" class="btn btn-outline-gold"><i class="bi bi-arrow-left me-2"></i><?php echo __('Back to Ideas'); ?></a>
                        <?php if (is_logged_in()): ?>
                            <a href="dashboard.php" class="btn btn-gold"><i class="bi bi-plus-circle me-2"></i><?php echo __('Share Your Idea'); ?></a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-gold"><i class="bi bi-person-plus me-2"></i><?php echo __('Join Community'); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container py-4">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="idea-content position-relative">
                    <!-- Bookmark Button -->
                    <?php if (is_logged_in()): ?>
                        <button class="btn-bookmark <?= $user_bookmarked ? 'bookmarked' : '' ?>" onclick="toggleBookmark(<?= $idea_id ?>)">
                            <i class="bi bi-bookmark<?= $user_bookmarked ? '-fill' : '' ?>"></i>
                        </button>
                    <?php endif; ?>

                    <!-- Idea Image -->
                    <?php if (!empty($idea['image_url'])): ?>
                        <img src="<?= htmlspecialchars($idea['image_url']) ?>" alt="Idea Image" class="idea-image">
                    <?php endif; ?>

                    <!-- Idea Title -->
                    <h1 class="idea-title"><?= htmlspecialchars($idea['title']) ?></h1>

                    <!-- Meta Information -->
                    <div class="idea-meta">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <span class="badge badge-gold rounded-pill px-3 py-2"><?= htmlspecialchars($idea['category_name']) ?></span>
                            <span><i class="bi bi-calendar3 me-1"></i><?= format_date_time($idea['created_at']) ?></span>
                            <span><i class="bi bi-person me-1"></i><a href="profile_others.php?user_id=<?= $idea['user_id'] ?>" class="author-link"><?= htmlspecialchars($author['username']) ?></a></span>
                            <span><i class="bi bi-eye me-1"></i><?= $idea['views_count'] ?? 0 ?> views</span>
                        </div>
                    </div>

                    <!-- Idea Description -->
                    <div class="idea-description">
                        <?= nl2br(htmlspecialchars($idea['description'])) ?>
                    </div>

                    <!-- Stats -->
                    <div class="idea-stats">
                        <div class="stat-item like-count">
                            <i class="bi bi-hand-thumbs-up-fill"></i>
                            <span><?= $votes['like'] ?> likes</span>
                        </div>
                        <div class="stat-item dislike-count">
                            <i class="bi bi-hand-thumbs-down-fill"></i>
                            <span><?= $votes['dislike'] ?> dislikes</span>
                        </div>
                        <div class="stat-item comment-count">
                            <i class="bi bi-chat-dots"></i>
                            <span><?= $idea['comments_count'] ?> comments</span>
                        </div>
                        <div class="stat-item">
                            <i class="bi bi-bookmark"></i>
                            <span><?= $idea['bookmarks_count'] ?> bookmarks</span>
                    </div>
                    </div>

                    <!-- Action Buttons -->
                    <?php if (is_logged_in()): ?>
                        <div class="action-buttons">
                            <form method="POST" action="actions/vote.php" class="d-flex gap-2">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="idea_id" value="<?= $idea_id ?>">
                                <button type="submit" name="vote_type" value="like" class="btn btn-vote">
                                    <i class="bi bi-hand-thumbs-up me-2"></i><?php echo __('Like'); ?>
                                </button>
                                <button type="submit" name="vote_type" value="dislike" class="btn btn-vote">
                                    <i class="bi bi-hand-thumbs-down me-2"></i><?php echo __('Dislike'); ?>
                                </button>
                        </form>
                            <button class="btn btn-outline-gold" onclick="showReactions(<?= $idea_id ?>)">
                                <i class="bi bi-emoji-smile me-2"></i><?php echo __('React'); ?>
                            </button>
                        </div>
                        <?php if ($vote_msg): ?>
                            <div class="alert alert-info"><?= htmlspecialchars($vote_msg) ?></div>
                        <?php endif; ?>
                        
                        <!-- Reactions Display -->
                        <?php if (!empty($reactions)): ?>
                            <div class="reactions-display mb-3">
                                <h6 class="mb-2"><?php echo __('Reactions'); ?></h6>
                                <div class="d-flex gap-2 flex-wrap">
                                    <?php foreach ($reactions as $reaction): ?>
                                        <span class="badge bg-light text-dark border">
                                            <?= htmlspecialchars($reaction['reaction_type']) ?> <?= $reaction['count'] ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Current User's Reaction -->
                        <?php if ($user_reaction): ?>
                            <div class="current-reaction mb-3">
                                <small class="text-muted"><?php echo __('Your reaction:'); ?></small>
                                <span class="badge bg-gold text-dark"><?= htmlspecialchars($user_reaction['reaction_type']) ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Edit Controls (for idea owner) -->
                    <?php if (is_logged_in() && current_user_id() == $idea['user_id']): ?>
                        <div class="action-buttons">
                            <button class="btn btn-outline-gold" data-bs-toggle="collapse" data-bs-target="#editIdeaForm">
                                <i class="bi bi-pencil me-2"></i><?php echo __('Edit Idea'); ?>
                            </button>
                            <form method="POST" onsubmit="return confirm('<?php echo __('Are you sure you want to delete this idea?'); ?>');" style="display:inline;">
                                <?= csrf_field(); ?>
                                <button type="submit" name="delete_idea" class="btn btn-outline-danger">
                                    <i class="bi bi-trash me-2"></i><?php echo __('Delete'); ?>
                                </button>
                            </form>
                        </div>

                        <!-- Edit Form -->
                        <div class="collapse" id="editIdeaForm">
                            <div class="edit-form">
                                <h5 class="mb-3"><?php echo __('Edit Your Idea'); ?></h5>
                            <form method="POST" enctype="multipart/form-data">
                                <?= csrf_field(); ?>
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo __('Title'); ?></label>
                                    <input type="text" class="form-control" name="edit_title" value="<?= htmlspecialchars($idea['title']) ?>" required>
                                </div>
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo __('Description'); ?></label>
                                        <textarea class="form-control" name="edit_description" rows="4" required><?= htmlspecialchars($idea['description']) ?></textarea>
                                </div>
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo __('Upload New Image'); ?></label>
                                    <input type="file" class="form-control" name="edit_image_file" accept="image/*" onchange="previewEditIdeaImage(event)">
                                    <div id="editIdeaImagePreview" class="mt-2" style="display:none;">
                                            <img src="#" alt="Preview" class="rounded shadow" style="max-width:200px;max-height:150px;object-fit:cover;">
                                    </div>
                                </div>
                                    <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="edit_is_public" id="edit_is_public" <?= $idea['is_public'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="edit_is_public"><?php echo __('Public (visible to everyone)'); ?></label>
                                </div>
                                    <button type="submit" name="edit_idea" class="btn btn-gold"><?php echo __('Save Changes'); ?></button>
                            </form>
                        </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Author Section -->
                <div class="author-section">
                    <div class="d-flex align-items-center">
                        <img src="<?= $author['avatar'] ?: 'assets/images/default-avatar.png'; ?>" alt="<?= htmlspecialchars($author['username']); ?>" class="author-avatar">
                        <div class="author-info">
                            <h5><a href="profile_others.php?user_id=<?= $idea['user_id'] ?>" class="author-link"><?= htmlspecialchars($author['username']) ?></a></h5>
                            <?php if (!empty($author['bio'])): ?>
                                <p class="author-bio"><?= htmlspecialchars($author['bio']) ?></p>
                    <?php endif; ?>
                </div>
                        <a href="profile_others.php?user_id=<?= $idea['user_id'] ?>" class="btn btn-outline-gold ms-auto">
                            <i class="bi bi-person me-2"></i><?php echo __('View Profile'); ?>
                        </a>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="comments-section">
                    <h4 class="mb-4"><i class="bi bi-chat-dots me-2"></i><?php echo __('Comments'); ?> (<?= count($comments) ?>)</h4>
                    
                    <?php if (is_logged_in()): ?>
                        <div class="comment-form">
                            <form method="POST">
                            <?= csrf_field(); ?>
                                <div class="mb-3">
                                    <textarea name="comment" class="form-control comment-input" rows="3" placeholder="<?php echo __('Share your thoughts on this idea...'); ?>" required></textarea>
                            </div>
                                <button type="submit" class="btn btn-gold">
                                    <i class="bi bi-send me-2"></i><?php echo __('Post Comment'); ?>
                                </button>
                        </form>
                        </div>
                        <?php if ($comment_msg): ?>
                            <div class="alert alert-info"><?= htmlspecialchars($comment_msg) ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <?php echo __('Please'); ?> <a href="login.php"><?php echo __('login'); ?></a> <?php echo __('to leave a comment.'); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Comments List -->
                    <?php if (empty($comments)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-chat-dots text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2"><?php echo __('No comments yet. Be the first to share your thoughts!'); ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-item">
                                <div class="comment-header">
                                    <img src="<?= $comment['avatar'] ?: 'assets/images/default-avatar.png'; ?>" alt="<?= htmlspecialchars($comment['username']); ?>" class="comment-avatar">
                                    <div>
                                        <h6 class="comment-author"><a href="profile_others.php?user_id=<?= $comment['user_id'] ?>" class="author-link"><?= htmlspecialchars($comment['username']) ?></a></h6>
                                        <p class="comment-date"><?= format_date_time($comment['created_at']) ?></p>
                                    </div>
                                </div>
                                <p class="comment-text"><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="idea-sidebar">
                    <h5 class="sidebar-title"><i class="bi bi-info-circle me-2"></i><?php echo __('Idea Details'); ?></h5>
                    
                    <div class="sidebar-item">
                        <span class="sidebar-label"><?php echo __('Author'); ?></span>
                        <span class="sidebar-value"><a href="profile_others.php?user_id=<?= $idea['user_id'] ?>" class="author-link"><?= htmlspecialchars($author['username']) ?></a></span>
                    </div>
                    
                    <div class="sidebar-item">
                        <span class="sidebar-label"><?php echo __('Category'); ?></span>
                        <span class="sidebar-value"><?= htmlspecialchars($idea['category_name']) ?></span>
                    </div>
                    
                    <div class="sidebar-item">
                        <span class="sidebar-label"><?php echo __('Visibility'); ?></span>
                        <span class="sidebar-value">
                            <?php if ($idea['is_public']): ?>
                                <span class="badge bg-success"><?php echo __('Public'); ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?php echo __('Private'); ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="sidebar-item">
                        <span class="sidebar-label"><?php echo __('Created'); ?></span>
                        <span class="sidebar-value"><?= format_date_time($idea['created_at'], true) ?></span>
                    </div>
                    
                    <div class="sidebar-item">
                        <span class="sidebar-label"><?php echo __('Views'); ?></span>
                        <span class="sidebar-value"><?= number_format($idea['views_count'] ?? 0) ?></span>
                    </div>
                    
                    <div class="sidebar-item">
                        <span class="sidebar-label"><?php echo __('Votes'); ?></span>
                        <span class="sidebar-value"><?= $votes['like'] + $votes['dislike'] ?></span>
                    </div>

                    <!-- Tags -->
                    <?php if (!empty($idea['tags'])): ?>
                        <div class="mt-4">
                            <h6 class="sidebar-title"><?php echo __('Tags'); ?></h6>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach (parse_tags($idea['tags']) as $tag): ?>
                                    <span class="badge bg-light text-dark"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Reaction Modal -->
    <div class="modal fade" id="reactionModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><?php echo __('Add Reaction'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button class="btn btn-outline-primary reaction-emoji" data-reaction="👍">👍</button>
                        <button class="btn btn-outline-danger reaction-emoji" data-reaction="❤️">❤️</button>
                        <button class="btn btn-outline-warning reaction-emoji" data-reaction="🎉">🎉</button>
                        <button class="btn btn-outline-info reaction-emoji" data-reaction="🔥">🔥</button>
                        <button class="btn btn-outline-success reaction-emoji" data-reaction="👏">👏</button>
                        <button class="btn btn-outline-secondary reaction-emoji" data-reaction="🤔">🤔</button>
                    </div>
                </div>
            </div>
            </div>
        </div>

    <footer class="footer py-4 text-center">
        <div class="container small">© <?= date('Y') ?> IdeaVote. <?php echo __('Made with love by makers.'); ?> <a class="ms-2" href="contact.php"><?php echo __('Contact'); ?></a></div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/theme.js"></script>
    <script>
        // Bookmark functionality
        function toggleBookmark(ideaId) {
            const button = event.target.closest('.btn-bookmark');
            const icon = button.querySelector('i');
            
            fetch('actions/bookmarks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=' + (icon.classList.contains('bi-bookmark-fill') ? 'remove' : 'add') + '&idea_id=' + ideaId
            })
            .then(response => response.text())
            .then(result => {
                if (result === 'success') {
                    icon.classList.toggle('bi-bookmark');
                    icon.classList.toggle('bi-bookmark-fill');
                    button.classList.toggle('bookmarked');
                    
                    // Add animation
                    button.style.transform = 'scale(1.2)';
                    setTimeout(() => {
                        button.style.transform = 'scale(1)';
                    }, 200);
                }
            });
        }

        // Reaction functionality
        let currentIdeaId = null;
        
        function showReactions(ideaId) {
            currentIdeaId = ideaId;
            new bootstrap.Modal(document.getElementById('reactionModal')).show();
        }

        document.querySelectorAll('.reaction-emoji').forEach(btn => {
            btn.addEventListener('click', function() {
                const reaction = this.dataset.reaction;
                
                fetch('actions/reactions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=add&idea_id=' + currentIdeaId + '&reaction=' + reaction
                })
                .then(response => response.text())
                .then(result => {
                    if (result === 'success') {
                        bootstrap.Modal.getInstance(document.getElementById('reactionModal')).hide();
                        
                        // Show success feedback
                        const toast = document.createElement('div');
                        toast.className = 'position-fixed top-0 end-0 p-3';
                        toast.style.zIndex = '9999';
                        toast.innerHTML = `
                            <div class="toast show bg-success text-white" role="alert">
                                <div class="toast-header bg-success text-white">
                                    <strong class="me-auto">Success!</strong>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                                </div>
                                <div class="toast-body">
                                    Reaction added successfully! ${reaction}
                                </div>
                            </div>
                        `;
                        document.body.appendChild(toast);
                        
                        setTimeout(() => {
                            toast.remove();
                            // Refresh the page to show updated reactions
                            location.reload();
                        }, 1500);
                    } else {
                        // Show error feedback
                        const toast = document.createElement('div');
                        toast.className = 'position-fixed top-0 end-0 p-3';
                        toast.style.zIndex = '9999';
                        toast.innerHTML = `
                            <div class="toast show bg-danger text-white" role="alert">
                                <div class="toast-header bg-danger text-white">
                                    <strong class="me-auto">Error!</strong>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                                </div>
                                <div class="toast-body">
                                    ${result}
                                </div>
                            </div>
                        `;
                        document.body.appendChild(toast);
                        
                        setTimeout(() => {
                            toast.remove();
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const toast = document.createElement('div');
                    toast.className = 'position-fixed top-0 end-0 p-3';
                    toast.style.zIndex = '9999';
                    toast.innerHTML = `
                        <div class="toast show bg-danger text-white" role="alert">
                            <div class="toast-header bg-danger text-white">
                                <strong class="me-auto">Error!</strong>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                            </div>
                            <div class="toast-body">
                                An error occurred while adding the reaction.
                            </div>
                        </div>
                    `;
                    document.body.appendChild(toast);
                    
                    setTimeout(() => {
                        toast.remove();
                    }, 3000);
                });
            });
        });

        // Image preview for edit form
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

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
</script>
</body>
</html> 