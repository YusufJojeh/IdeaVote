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
require_login();

$errors = [];
$success = false;

// Handle idea submission with optional image
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'CSRF verification failed.';
    } else {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    $tags = trim($_POST['tags'] ?? '');
    $image_url = null;
    
    if (isset($_FILES['idea_image']) && $_FILES['idea_image']['error'] === UPLOAD_ERR_OK) {
        $up = upload_image($_FILES['idea_image'], 'ideas');
        if ($up['ok']) {
            $image_url = $up['path'];
        } else {
            $errors[] = $up['error'] ?? 'upload_failed';
        }
    }
    
    if (strlen($title) < 3) {
        $errors[] = __('Title must be at least 3 characters.');
    }
    if (strlen($description) < 10) {
        $errors[] = __('Description must be at least 10 characters.');
    }
    if ($category_id <= 0) {
        $errors[] = __('Please select a category.');
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO ideas (user_id, category_id, title, description, is_public, image_url, tags, slug) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $uid = current_user_id();
        $slug = create_slug($title);
        
        if ($stmt->execute([$uid, $category_id, $title, $description, $is_public, $image_url, $tags, $slug])) {
            $success = true;
            
            // Create webhook event
            $idea_id = $pdo->lastInsertId();
            trigger_webhook('idea.created', [
                'idea_id' => $idea_id,
                'user_id' => $uid,
                'title' => $title,
                'category_id' => $category_id
            ]);
        } else {
            $errors[] = __('Failed to submit idea. Please try again.');
        }
    }
    }
}
$categories = [];
$stmt = $pdo->query("SELECT id, name_en, name_ar FROM categories ORDER BY name_en");
$categories = $stmt->fetchAll();

$my_ideas = [];
$stmt = $pdo->prepare("
    SELECT i.*, c.name_en as category_name, c.name_ar as category_name_ar,
           (SELECT COUNT(*) FROM votes WHERE votes.idea_id=i.id AND vote_type='like') as likes,
           (SELECT COUNT(*) FROM votes WHERE votes.idea_id=i.id AND vote_type='dislike') as dislikes,
           (SELECT COUNT(*) FROM comments WHERE comments.idea_id=i.id) as comments_count,
           (SELECT COUNT(*) FROM reactions WHERE reactions.idea_id=i.id) as reactions_count,
           (SELECT COUNT(*) FROM bookmarks WHERE bookmarks.idea_id=i.id) as bookmarks_count,
           i.views_count
    FROM ideas i 
    LEFT JOIN categories c ON i.category_id = c.id 
    WHERE i.user_id = ? 
    ORDER BY i.created_at DESC
");
$uid = current_user_id();
$stmt->execute([$uid]);
$my_ideas = $stmt->fetchAll();

$idea_count = count($my_ideas);
$vote_count = 0;
$total_views = 0;
foreach ($my_ideas as $idea) {
    $vote_count += $idea['likes'] + $idea['dislikes'];
    $total_views += $idea['views_count'] ?? 0;
}

include 'includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="<?php echo current_language(); ?>" dir="<?php echo lang_dir(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('Dashboard'); ?> - IdeaVote</title>
    <meta name="description" content="<?php echo __('Manage your ideas and track your progress on IdeaVote.'); ?>">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo __('Dashboard'); ?> - IdeaVote">
    <meta property="og:description" content="<?php echo __('Manage your ideas and track your progress on IdeaVote.'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $_SERVER['REQUEST_URI']; ?>">
    
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
            --dark-bg: #1a1a1a;
            --dark-card: #2d2d2d;
            --dark-text: #e0e0e0;
        }
        
        [data-theme="dark"] {
            --black: var(--dark-text);
            --gray: #b0b0b0;
            --offwhite: var(--dark-bg);
        }
        
        body { 
            background: var(--offwhite); 
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif; 
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .navbar-lux { 
            background: var(--offwhite) !important; 
            box-shadow: 0 8px 32px 0 rgba(24,24,24,0.06); 
            border-bottom: 1px solid #eee; 
            transition: background-color 0.3s ease;
        }
        
        [data-theme="dark"] .navbar-lux {
            background: var(--dark-card) !important;
            border-bottom: 1px solid #444;
        }
        
        .gold-gradient { 
            background: linear-gradient(90deg, var(--gold) 0%, var(--gold-light) 100%); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            background-clip: text; 
            color: var(--gold); 
        }
        
        .dashboard-glass { 
            background: rgba(255,255,255,0.95); 
            box-shadow: 0 8px 32px 0 rgba(24,24,24,0.08); 
            border-radius: 32px; 
            border: 1.5px solid #eee; 
            padding: 2.5rem 2rem; 
            transition: background 0.3s ease, border-color 0.3s ease;
        }
        
        [data-theme="dark"] .dashboard-glass {
            background: rgba(45,45,45,0.95);
            border: 1.5px solid #444;
        }
        .btn-gold { background: linear-gradient(90deg, #FFD700 0%, #FFEF8E 100%); color: #fff; font-weight: bold; border: none; box-shadow: 0 2px 12px rgba(255,215,0,0.10); }
        .btn-gold:hover { background: linear-gradient(90deg, #FFEF8E 0%, #FFD700 100%); color: #181818; }
        .form-label { color: #181818; font-weight: 500; }
        .form-control, .form-select { background: #fff; border-radius: 12px; border: 1.5px solid #eee; }
        .icon-gold { color: #FFD700; }
        .idea-card { background: #fff; border-radius: 18px; box-shadow: 0 2px 12px rgba(24,24,24,0.06); border: 1.5px solid #eee; margin-bottom: 1.5rem; }
        .idea-card .card-title { color: #181818; }
        .idea-card .badge { background: #FFD700; color: #181818; }
        .idea-card .text-muted { color: #888 !important; }
        .vote-icon { font-size: 1.2rem; }
        .vote-icon.like { color: #FFD700; }
        .vote-icon.dislike { color: #888; }
    </style>
</head>
<body>
    <?php
    // The navbar is now included at the very top of the file
    ?>
    <div class="container py-4">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="dashboard-glass shadow">
                    <h3 class="mb-3 gold-gradient"><i class="bi bi-plus-circle icon-gold"></i> Submit a New Idea</h3>
                    <?php if ($success): ?>
                        <div class="alert alert-success">Idea submitted successfully!</div>
                    <?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" novalidate>
                        <?= csrf_field(); ?>
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name_en']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="is_public" name="is_public" <?= isset($_POST['is_public']) ? 'checked' : '' ?> checked>
                            <label class="form-check-label" for="is_public">Public (visible to everyone)</label>
                        </div>
                        <div class="mb-3">
                            <label for="idea_image" class="form-label">Idea Image (optional)</label>
                            <input type="file" class="form-control" id="idea_image" name="idea_image" accept="image/*" onchange="previewIdeaImage(event)">
                            <div id="ideaImagePreview" class="mt-2" style="display:none;">
                                <img src="#" alt="Preview" class="rounded shadow" style="max-width:180px;max-height:120px;object-fit:cover;">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-gold w-100 cta-btn">Submit Idea</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="dashboard-glass shadow">
                    <h3 class="mb-3 gold-gradient"><i class="bi bi-lightbulb icon-gold"></i> My Ideas</h3>
                    <div class="mb-2">Total Ideas: <strong><?= $idea_count ?></strong> | Total Votes: <strong><?= $vote_count ?></strong></div>
                    <?php if (empty($my_ideas)): ?>
                        <div class="alert alert-info">You haven't submitted any ideas yet.</div>
                    <?php else: ?>
                        <?php foreach ($my_ideas as $idea): ?>
                            <div class="card idea-card mb-3 d-flex flex-row align-items-center" style="min-height:90px;">
                                <?php if (!empty($idea['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($idea['image_url']) ?>" alt="Idea Image" class="rounded-start" style="width:72px;height:72px;object-fit:cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title mb-1">
                                        <?= htmlspecialchars($idea['title']) ?>
                                        <?php if (!$idea['is_public']): ?>
                                            <span class="badge">Private</span>
                                        <?php endif; ?>
                                    </h5>
                                    <div class="mb-2 text-muted" style="font-size:0.95em;">
                                        Category: <?= htmlspecialchars(get_category_name($idea['category_id'], 'en')) ?>
                                    </div>
                                    <p class="card-text mb-2" style="font-size:1em;"> <?= nl2br(htmlspecialchars($idea['description'])) ?> </p>
                                    <div class="d-flex align-items-center">
                                        <span class="me-3 vote-icon like"><i class="bi bi-hand-thumbs-up-fill"></i> <?= get_vote_counts($idea['id'])['like'] ?></span>
                                        <span class="vote-icon dislike"><i class="bi bi-hand-thumbs-down-fill"></i> <?= get_vote_counts($idea['id'])['dislike'] ?></span>
                                    </div>
                                    <div class="text-muted mt-2" style="font-size:0.85em;">Submitted: <?= htmlspecialchars($idea['created_at']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        function previewIdeaImage(event) {
            const input = event.target;
            const previewDiv = document.getElementById('ideaImagePreview');
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