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

// Handle idea submission with optional image and new category creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'CSRF verification failed.';
    } else {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
        $new_category_name = trim($_POST['new_category_name'] ?? '');
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    $tags = trim($_POST['tags'] ?? '');
    $image_url = null;
        
        // Handle new category creation
        if ($category_id === -1 && !empty($new_category_name)) {
            // Check if category already exists
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name_en = ? OR name_ar = ?");
            $stmt->execute([$new_category_name, $new_category_name]);
            $existing_category = $stmt->fetch();
            
            if ($existing_category) {
                $category_id = $existing_category['id'];
            } else {
                // Create new category
                $stmt = $pdo->prepare("INSERT INTO categories (name_en, name_ar) VALUES (?, ?)");
                if ($stmt->execute([$new_category_name, $new_category_name])) {
                    $category_id = $pdo->lastInsertId();
                } else {
                    $errors[] = __('Failed to create new category. Please try again.');
                }
            }
        }
    
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
            $errors[] = __('Please select a category or enter a new one.');
        }
        if ($category_id === -1 && empty($new_category_name)) {
            $errors[] = __('Please enter a name for the new category.');
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
<html lang="<?= current_language() ?>" dir="<?= lang_dir() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('My Ideas') ?> - IdeaVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        :root{
            --gold:#FFD700; --gold-2:#FFEF8E;
            --bg:#ffffff; --text:#181818; --muted:#555d68; --card:#ffffff; --border:#e5e7eb;
        }
        [data-theme="dark"]{
            --bg:#0b0e13; --text:#e5e7eb; --muted:#9aa3af; --card:#0f141c; --border:#1f2937;
        }
        body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:var(--bg);color:var(--text);}
        .btn-gold{background:linear-gradient(90deg,var(--gold),var(--gold-2));color:#111;border:0;font-weight:700}
        .btn-gold:hover{filter:brightness(1.05);transform:translateY(-1px)}
        .card{background:var(--card);border:1px solid var(--border);border-radius:1.5rem;box-shadow:0 10px 30px rgba(0,0,0,.1)}
        .gold-gradient{background:linear-gradient(90deg,var(--gold),var(--gold-2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;color:var(--gold)}

        /* Hero Section */
        .hero{position:relative;min-height:40vh;display:flex;align-items:center;background:linear-gradient(135deg, rgba(255,215,0,.1) 0%, rgba(255,215,0,.05) 100%)}
        .hero::before{content:"";position:absolute;inset:0;background:url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1900&q=80') center/cover no-repeat;filter:brightness(.15);opacity:.3}
        .hero::after{content:"";position:absolute;inset:0;background:radial-gradient(80% 60% at 50% 0%,rgba(255,215,0,.12),transparent 60%)}
        .hero .content{position:relative;z-index:2}

        /* Stats Cards */
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem;margin-bottom:3rem}
        .stat-card{background:var(--card);border:1px solid var(--border);border-radius:1rem;padding:1.5rem;text-align:center;transition:all .3s}
        .stat-card:hover{transform:translateY(-4px);box-shadow:0 15px 40px rgba(0,0,0,.15)}
        .stat-number{font-size:2.5rem;font-weight:800;color:var(--gold);margin-bottom:.5rem}
        .stat-label{color:var(--muted);font-weight:500}

        /* Form Styling */
        .idea-form{background:var(--card);border:1px solid var(--border);border-radius:1.5rem;padding:2rem;margin-bottom:3rem}
        .form-control,.form-select{border:2px solid var(--border);border-radius:.75rem;padding:.75rem 1rem;font-weight:500;transition:all .2s;background:var(--card);color:var(--text)}
        .form-control:focus,.form-select:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(255,215,0,.1);background:var(--card)}
        .form-label{font-weight:600;color:var(--text);margin-bottom:.5rem}

        /* Idea Cards */
        .idea-card{background:var(--card);border:1px solid var(--border);border-radius:1rem;overflow:hidden;transition:all .3s;position:relative}
        .idea-card:hover{transform:translateY(-4px);box-shadow:0 15px 40px rgba(0,0,0,.15)}
        .idea-image{width:100%;height:200px;object-fit:cover;background:linear-gradient(135deg, var(--gold), var(--gold-2))}
        .idea-content{padding:1.5rem}
        .idea-title{font-size:1.25rem;font-weight:700;color:var(--text);margin-bottom:.5rem;line-height:1.4}
        .idea-meta{color:var(--muted);font-size:.9rem;margin-bottom:1rem}
        .idea-desc{color:var(--muted);font-size:.95rem;line-height:1.6;margin-bottom:1rem;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
        .idea-stats{display:flex;justify-content:space-between;align-items:center;padding:.75rem;background:rgba(255,215,0,.05);border-radius:.75rem;margin-bottom:1rem}
        .stat-item{display:flex;align-items:center;color:var(--muted);font-size:.85rem;font-weight:500}
        .stat-item i{margin-right:.25rem;color:var(--gold)}
        .like-count{color:#10b981}
        .dislike-count{color:#ef4444}
        .comment-count{color:#3b82f6}
        .view-count{color:#8b5cf6}

        /* Action Buttons */
        .action-buttons{display:flex;gap:.75rem;flex-wrap:wrap}
        .btn-primary-custom{background:var(--gold);border:none;border-radius:.75rem;padding:.75rem 1.5rem;font-weight:600;color:#111;transition:all .2s}
        .btn-primary-custom:hover{background:var(--gold-2);color:#111;transform:translateY(-2px)}
        .btn-secondary-custom{background:rgba(255,215,0,.1);border:2px solid var(--gold);border-radius:.75rem;padding:.75rem 1.5rem;color:var(--gold);transition:all .2s}
        .btn-secondary-custom:hover{background:var(--gold);color:#111;transform:translateY(-2px)}

        /* Empty State */
        .empty-state{text-align:center;padding:4rem 2rem;background:var(--card);border:1px solid var(--border);border-radius:1.5rem}
        .empty-state-icon{font-size:4rem;color:var(--gold);margin-bottom:1rem}
        .empty-state-title{font-size:1.5rem;font-weight:700;color:var(--text);margin-bottom:.5rem}
        .empty-state-text{color:var(--muted);font-size:1rem}

        /* Responsive */
        @media (max-width:768px){
            .hero{min-height:30vh}
            .stats-grid{grid-template-columns:repeat(2,1fr)}
            .idea-form{padding:1.5rem}
            .action-buttons{flex-direction:column}
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <header class="hero">
        <div class="container content">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <span class="badge badge-gold rounded-pill px-3 py-2 mb-3">💡 <?= __('My Ideas Hub') ?></span>
                    <h1 class="display-4 mb-3"><?= __('Manage Your Ideas') ?></h1>
                    <p class="lead text-white mb-4"><?= __('Create, edit, and track the performance of your innovative ideas') ?></p>
                </div>
            </div>
        </div>
    </header>

    <div class="container py-4">
        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $idea_count ?></div>
                <div class="stat-label"><?= __('Total Ideas') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $vote_count ?></div>
                <div class="stat-label"><?= __('Total Votes') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $total_views ?></div>
                <div class="stat-label"><?= __('Total Views') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count(array_filter($my_ideas, function($idea) { return $idea['is_public']; })) ?></div>
                <div class="stat-label"><?= __('Public Ideas') ?></div>
            </div>
        </div>

        <!-- Success Message -->
                    <?php if ($success): ?>
            <div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= __('Idea submitted successfully!') ?>
            </div>
                    <?php endif; ?>

        <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

        <!-- Submit New Idea Form -->
        <div class="idea-form">
            <h3 class="mb-4"><i class="bi bi-plus-circle me-2"></i><?= __('Submit New Idea') ?></h3>
            <form method="POST" enctype="multipart/form-data">
                        <?= csrf_field(); ?>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label"><?= __('Idea Title') ?></label>
                            <input type="text" class="form-control" id="title" name="title" required 
                                   placeholder="<?= __('Enter a catchy title for your idea') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label"><?= __('Description') ?></label>
                            <textarea class="form-control" id="description" name="description" rows="4" required 
                                      placeholder="<?= __('Describe your idea in detail...') ?>"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                        <div class="mb-3">
                                    <label for="category_id" class="form-label"><?= __('Category') ?></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                        <option value=""><?= __('Select a category') ?></option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>">
                                                <?= htmlspecialchars(current_language() === 'ar' ? $category['name_ar'] : $category['name_en']) ?>
                                            </option>
                                <?php endforeach; ?>
                                        <option value="-1"><?= __('Other') ?></option>
                            </select>
                        </div>
                                
                                <!-- New Category Input (hidden by default) -->
                                <div class="mb-3" id="new_category_container" style="display: none;">
                                    <label for="new_category_name" class="form-label"><?= __('New Category Name') ?></label>
                                    <input type="text" class="form-control" id="new_category_name" name="new_category_name" 
                                           placeholder="<?= __('Enter new category name') ?>">
                                    <div class="form-text"><?= __('Enter a descriptive name for your new category') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tags" class="form-label"><?= __('Tags') ?></label>
                                    <input type="text" class="form-control" id="tags" name="tags" 
                                           placeholder="<?= __('Enter tags separated by commas') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="idea_image" class="form-label"><?= __('Upload Image') ?></label>
                            <input type="file" class="form-control" id="idea_image" name="idea_image" accept="image/*">
                            <div class="form-text"><?= __('Optional: Add an image to make your idea stand out') ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_public" name="is_public" checked>
                                <label class="form-check-label" for="is_public">
                                    <?= __('Make this idea public') ?>
                                </label>
                            </div>
                            <div class="form-text"><?= __('Public ideas are visible to everyone') ?></div>
                        </div>
                        
                        <button type="submit" class="btn btn-gold w-100 py-3">
                            <i class="bi bi-rocket-takeoff me-2"></i><?= __('Submit Idea') ?>
                        </button>
                            </div>
                        </div>
                    </form>
                </div>

        <!-- My Ideas Section -->
        <div class="mb-4">
            <h3 class="mb-4"><i class="bi bi-lightbulb me-2"></i><?= __('My Ideas') ?></h3>
            
            <?php if (empty($my_ideas)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-lightbulb"></i>
                    </div>
                    <h3 class="empty-state-title"><?= __('No Ideas Yet') ?></h3>
                    <p class="empty-state-text"><?= __('Start sharing your innovative ideas with the community!') ?></p>
                    <button class="btn btn-gold mt-3" onclick="document.getElementById('title').focus()">
                        <i class="bi bi-plus-circle me-2"></i><?= __('Create Your First Idea') ?>
                    </button>
            </div>
                    <?php else: ?>
                <div class="row g-4">
                        <?php foreach ($my_ideas as $idea): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="idea-card h-100">
                                <!-- Idea Image -->
                                <?php if (!empty($idea['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($idea['image_url']) ?>" alt="Idea Image" class="idea-image">
                                <?php else: ?>
                                    <div class="idea-image d-flex align-items-center justify-content-center">
                                        <i class="bi bi-lightbulb text-white" style="font-size: 3rem;"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="idea-content">
                                    <!-- Category Badge -->
                                    <span class="badge badge-gold rounded-pill px-2 py-1 mb-2">
                                        <?= htmlspecialchars($idea['category_name']) ?>
                                    </span>
                                    
                                    <!-- Title -->
                                    <h4 class="idea-title">
                                        <a href="idea.php?id=<?= $idea['id'] ?>" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($idea['title']) ?>
                                        </a>
                                    </h4>

                                    <!-- Meta Info -->
                                    <div class="idea-meta">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span><?= format_date_time($idea['created_at']) ?></span>
                                            <span class="badge <?= $idea['is_public'] ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= $idea['is_public'] ? __('Public') : __('Private') ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <p class="idea-desc">
                                        <?= nl2br(htmlspecialchars(substr($idea['description'], 0, 120))) ?><?= strlen($idea['description']) > 120 ? '...' : '' ?>
                                    </p>

                                    <!-- Stats -->
                                    <div class="idea-stats">
                                        <div class="stat-item like-count">
                                            <i class="bi bi-hand-thumbs-up-fill"></i>
                                            <span><?= $idea['likes'] ?></span>
                                        </div>
                                        <div class="stat-item dislike-count">
                                            <i class="bi bi-hand-thumbs-down-fill"></i>
                                            <span><?= $idea['dislikes'] ?></span>
                                    </div>
                                        <div class="stat-item comment-count">
                                            <i class="bi bi-chat-dots"></i>
                                            <span><?= $idea['comments_count'] ?></span>
                                    </div>
                                        <div class="stat-item view-count">
                                            <i class="bi bi-eye"></i>
                                            <span><?= $idea['views_count'] ?? 0 ?></span>
                                </div>
                            </div>

                                    <!-- Action Buttons -->
                                    <div class="action-buttons">
                                        <a href="idea.php?id=<?= $idea['id'] ?>" class="btn btn-primary-custom flex-grow-1">
                                            <i class="bi bi-eye me-2"></i><?= __('View') ?>
                                        </a>
                                        <a href="idea.php?id=<?= $idea['id'] ?>" class="btn btn-secondary-custom">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                </div>
            </div>
        </div>
    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/theme.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth scrolling
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

            // Form validation enhancement
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Submitting...';
                    submitBtn.disabled = true;
                });
            }

            const categorySelect = document.getElementById('category_id');
            const newCategoryContainer = document.getElementById('new_category_container');
            const newCategoryInput = document.getElementById('new_category_name');
            
            // Handle category selection change
            categorySelect.addEventListener('change', function() {
                if (this.value === '-1') {
                    // Show new category input
                    newCategoryContainer.style.display = 'block';
                    newCategoryInput.required = true;
                    newCategoryInput.focus();
            } else {
                    // Hide new category input
                    newCategoryContainer.style.display = 'none';
                    newCategoryInput.required = false;
                    newCategoryInput.value = '';
                }
            });
            
            // Form validation
            form.addEventListener('submit', function(e) {
                const categoryId = categorySelect.value;
                const newCategoryName = newCategoryInput.value.trim();
                
                if (categoryId === '-1' && newCategoryName === '') {
                    e.preventDefault();
                    alert('<?= __("Please enter a name for the new category.") ?>');
                    newCategoryInput.focus();
                    return false;
                }
                
                if (categoryId === '-1' && newCategoryName.length < 2) {
                    e.preventDefault();
                    alert('<?= __("Category name must be at least 2 characters long.") ?>');
                    newCategoryInput.focus();
                    return false;
                }
            });
            
            // Auto-save new category name to localStorage for better UX
            newCategoryInput.addEventListener('input', function() {
                localStorage.setItem('newCategoryName', this.value);
            });
            
            // Restore new category name from localStorage
            const savedCategoryName = localStorage.getItem('newCategoryName');
            if (savedCategoryName && categorySelect.value === '-1') {
                newCategoryInput.value = savedCategoryName;
            }
            
            // Clear localStorage on successful submission
            <?php if ($success): ?>
            localStorage.removeItem('newCategoryName');
            <?php endif; ?>
        });

        // Add CSS for spinning animation
        const style = document.createElement('style');
        style.textContent = `