<?php
ob_start();
include 'includes/config.php';
include 'includes/db.php';
include 'includes/i18n.php';
include 'includes/functions.php';
include 'includes/auth.php';
include 'includes/csrf.php';
include 'includes/notifications.php';

// Get categories with modern features
$categories = [];
$stmt = $pdo->query("SELECT id, name_en, name_ar, description FROM categories ORDER BY name_en");
$categories = $stmt->fetchAll();

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$sort = $_GET['sort'] ?? 'trending';
$search = $_GET['search'] ?? '';
$tags = $_GET['tags'] ?? '';

// Build query with modern features
$query = "SELECT i.*, u.username, u.avatar, c.name_en as category_name,
          (SELECT COUNT(*) FROM votes WHERE votes.idea_id=i.id AND vote_type='like') as likes,
          (SELECT COUNT(*) FROM votes WHERE votes.idea_id=i.id AND vote_type='dislike') as dislikes,
          (SELECT COUNT(*) FROM comments WHERE comments.idea_id=i.id) as comments_count,
          (SELECT COUNT(*) FROM reactions WHERE reactions.idea_id=i.id) as reactions_count,
          i.bookmarks_count,
          i.views_count, i.trending_score
          FROM ideas i 
          LEFT JOIN users u ON i.user_id = u.id 
          LEFT JOIN categories c ON i.category_id = c.id 
          WHERE i.is_public=1";

$params = [];
$types = '';

if ($category_id > 0) {
    $query .= " AND i.category_id=?";
    $params[] = $category_id;
    $types .= 'i';
}

if ($search) {
    $query .= " AND (i.title LIKE ? OR i.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

if ($tags) {
    $query .= " AND i.tags LIKE ?";
    $params[] = "%$tags%";
    $types .= 's';
}

// Enhanced sorting
$query .= " ORDER BY ";
switch ($sort) {
    case 'recent':
        $query .= "i.created_at DESC";
        break;
    case 'trending':
        $query .= "i.trending_score DESC, i.views_count DESC";
        break;
    case 'popular':
        $query .= "(SELECT COUNT(*) FROM votes WHERE votes.idea_id=i.id AND vote_type='like') DESC";
        break;
    case 'most_viewed':
        $query .= "i.views_count DESC";
        break;
    case 'most_commented':
        $query .= "(SELECT COUNT(*) FROM comments WHERE comments.idea_id=i.id) DESC";
        break;
    default:
        $query .= "i.trending_score DESC";
}

$stmt = $pdo->prepare($query);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
$ideas = $stmt->fetchAll();

// Get user's bookmarks for highlighting
$user_bookmarks = [];
if (is_logged_in()) {
    $stmt = $pdo->prepare("SELECT idea_id FROM bookmarks WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_bookmarks = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

include 'includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="<?php echo current_language(); ?>" dir="<?php echo lang_dir(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('Ideas'); ?> - IdeaVote</title>
    <meta name="description" content="<?php echo __('Discover and vote on amazing ideas from our community of innovators.'); ?>">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo __('Ideas'); ?> - IdeaVote">
    <meta property="og:description" content="<?php echo __('Discover and vote on amazing ideas from our community of innovators.'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $_SERVER['REQUEST_URI']; ?>">
    
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

        /* Filter Section */
        .filter-section{background:var(--card);border:1px solid var(--border);border-radius:1.5rem;padding:2rem;margin-bottom:3rem;box-shadow:0 4px 20px rgba(0,0,0,.08)}
        .filter-label{font-weight:600;color:var(--text);margin-bottom:.5rem;font-size:.9rem}
        .form-control,.form-select{border:2px solid var(--border);border-radius:.75rem;padding:.75rem 1rem;font-weight:500;transition:all .2s;background:var(--card)}
        .form-control:focus,.form-select:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(255,215,0,.1);background:var(--card)}

        /* Idea Cards */
        .idea-card{background:var(--card);border:1px solid var(--border);border-radius:1.25rem;overflow:hidden;transition:all .3s;position:relative}
        .idea-card:hover{transform:translateY(-4px);box-shadow:0 20px 40px rgba(0,0,0,.15)}
        .idea-thumb{width:100%;height:200px;object-fit:cover;background:linear-gradient(135deg, var(--gold), var(--gold-2))}
        .idea-content{padding:1.5rem}
        .idea-title{font-weight:700;font-size:1.25rem;margin-bottom:.5rem;line-height:1.4}
        .idea-meta{color:var(--muted);font-size:.9rem;margin-bottom:1rem}
        .idea-desc{color:var(--muted);font-size:.95rem;line-height:1.6;margin-bottom:1rem;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
        .idea-stats{display:flex;justify-content:space-between;align-items:center;padding:.75rem;background:rgba(255,215,0,.05);border-radius:.75rem;margin-bottom:1rem}
        .stat-item{display:flex;align-items:center;color:var(--muted);font-size:.85rem;font-weight:500}
        .stat-item i{margin-right:.25rem;color:var(--gold)}
        .like-count{color:#10b981}
        .dislike-count{color:#ef4444}
        .comment-count{color:#3b82f6}
        .bookmark-count{color:var(--gold)}
        .view-count{color:#8b5cf6}
        
        /* Author Links */
        .author-link{color:var(--text);transition:color .2s}
        .author-link:hover{color:var(--gold);text-decoration:none!important}

        /* Bookmark Button */
        .bookmark-btn{position:absolute;top:1rem;right:1rem;background:rgba(255,255,255,.9);border:none;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;color:var(--gold);transition:all .2s;backdrop-filter:blur(10px);z-index:10;cursor:pointer}
        .bookmark-btn:hover{background:var(--gold);color:white;transform:scale(1.1);box-shadow:0 4px 12px rgba(255,215,0,0.3)}
        .bookmark-btn.bookmarked{background:var(--gold);color:white;box-shadow:0 4px 12px rgba(255,215,0,0.3)}
        .bookmark-btn:active{transform:scale(0.95)}

        /* Tags */
        .idea-tags{margin-bottom:1rem}
        .tag{display:inline-block;background:rgba(255,215,0,.1);color:var(--gold);padding:.2rem .6rem;border-radius:1rem;font-size:.75rem;font-weight:500;margin-right:.5rem;margin-bottom:.5rem;border:1px solid rgba(255,215,0,.2)}

        /* Empty State */
        .empty-state{text-align:center;padding:4rem 2rem;background:var(--card);border:1px solid var(--border);border-radius:1.5rem}
        .empty-state-icon{font-size:4rem;color:var(--gold);margin-bottom:1rem}
        .empty-state-title{font-size:1.5rem;font-weight:700;color:var(--text);margin-bottom:.5rem}
        .empty-state-text{color:var(--muted);font-size:1rem}

        /* Floating Action Button */
        .fab{position:fixed;bottom:2rem;right:2rem;width:60px;height:60px;background:linear-gradient(90deg,var(--gold),var(--gold-2));border:none;border-radius:50%;color:#111;font-size:1.5rem;box-shadow:0 8px 25px rgba(255,215,0,.3);transition:all .3s;z-index:1000;font-weight:700}
        .fab:hover{transform:scale(1.1);box-shadow:0 12px 35px rgba(255,215,0,.4);color:#111}

        /* Responsive */
        @media (max-width:768px){
            .hero{min-height:40vh}
            .filter-section{padding:1.5rem}
            .idea-thumb{height:150px}
            .idea-content{padding:1rem}
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <header class="hero">
        <div class="container content">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <span class="badge badge-gold rounded-pill px-3 py-2 mb-3">💡 <?php echo __('Discover Amazing Ideas'); ?></span>
                    <h1 class="display-4 mb-3"><?php echo __('Explore & Vote on Brilliant Ideas'); ?> 🚀</h1>
                    <p class="lead subtle mb-4"><?php echo __('Browse, filter, and support the most creative ideas from our community. Every vote counts towards building something amazing!'); ?></p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <?php if (is_logged_in()): ?>
                            <a href="dashboard.php" class="btn btn-gold"><i class="bi bi-plus-circle me-2"></i><?php echo __('Submit Idea'); ?></a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-gold"><i class="bi bi-person-plus me-2"></i><?php echo __('Join Community'); ?></a>
                        <?php endif; ?>
                        <a href="#ideas" class="btn btn-outline-gold"><i class="bi bi-stars me-2"></i><?php echo __('Browse Ideas'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Filter Section -->
    <section class="section" id="ideas">
        <div class="container">
            <div class="filter-section">
                <form method="get" class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="filter-label"><?php echo __('Category'); ?></label>
                    <select class="form-select" name="category_id">
                        <option value="0"><?php echo __('All Categories'); ?></option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars(current_language() === 'ar' ? $cat['name_ar'] : $cat['name_en']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                    
                    <div class="col-lg-2 col-md-6">
                        <label class="filter-label"><?php echo __('Sort By'); ?></label>
                    <select class="form-select" name="sort">
                        <option value="trending" <?= $sort === 'trending' ? 'selected' : '' ?>><?php echo __('Trending'); ?></option>
                        <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>><?php echo __('Most Popular'); ?></option>
                        <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>><?php echo __('Most Recent'); ?></option>
                        <option value="most_viewed" <?= $sort === 'most_viewed' ? 'selected' : '' ?>><?php echo __('Most Viewed'); ?></option>
                        <option value="most_commented" <?= $sort === 'most_commented' ? 'selected' : '' ?>><?php echo __('Most Commented'); ?></option>
                    </select>
                </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="filter-label"><?php echo __('Search Ideas'); ?></label>
                        <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" 
                               placeholder="<?php echo __('Search by title or description...'); ?>">
                </div>
                    
                    <div class="col-lg-2 col-md-6">
                        <label class="filter-label"><?php echo __('Tags'); ?></label>
                        <input type="text" class="form-control" name="tags" value="<?= htmlspecialchars($tags) ?>" 
                               placeholder="<?php echo __('Filter by tags...'); ?>">
                </div>
                    
                    <div class="col-lg-2 col-md-12">
                        <label class="filter-label">&nbsp;</label>
                        <button type="submit" class="btn btn-gold w-100">
                            <i class="bi bi-funnel me-2"></i><?php echo __('Filter'); ?>
                        </button>
                </div>
            </form>
        </div>

            <!-- Ideas Grid -->
        <div class="row g-4">
            <?php if (empty($ideas)): ?>
                <div class="col-12">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bi bi-search"></i>
                            </div>
                            <h3 class="empty-state-title"><?php echo __('No Ideas Found'); ?></h3>
                            <p class="empty-state-text"><?php echo __('Try adjusting your filters or search terms to find more ideas.'); ?></p>
                            <?php if (is_logged_in()): ?>
                                <a href="dashboard.php" class="btn btn-gold mt-3">
                                    <i class="bi bi-plus-circle me-2"></i><?php echo __('Be the First to Share'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                </div>
            <?php else: ?>
                <?php foreach ($ideas as $idea): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="idea-card h-100 shadow-soft">
                                <!-- Bookmark Button -->
                            <?php if (is_logged_in()): ?>
                                    <button class="bookmark-btn <?= in_array($idea['id'], $user_bookmarks) ? 'bookmarked' : '' ?>" 
                                        onclick="toggleBookmark(<?= $idea['id'] ?>)">
                                    <i class="bi bi-bookmark<?= in_array($idea['id'], $user_bookmarks) ? '-fill' : '' ?>"></i>
                                </button>
                            <?php endif; ?>
                            
                                <!-- Idea Image -->
                                <?php if (!empty($idea['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($idea['image_url']) ?>" alt="Idea Image" class="idea-thumb">
                                <?php else: ?>
                                    <div class="idea-thumb d-flex align-items-center justify-content-center">
                                        <i class="bi bi-lightbulb text-white" style="font-size: 3rem;"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="idea-content">
                                    <!-- Category Badge -->
                                    <span class="badge badge-gold rounded-pill px-2 py-1 mb-2"><?= htmlspecialchars($idea['category_name']) ?></span>
                                    
                                    <!-- Title -->
                                    <h3 class="idea-title">
                                        <a href="idea.php?id=<?= $idea['id'] ?>" class="text-decoration-none text-dark">
                                            <?= htmlspecialchars($idea['title']) ?>
                                        </a>
                                    </h3>

                                    <!-- Meta Info -->
                                    <div class="idea-meta">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="<?= $idea['avatar'] ?: 'assets/images/default-avatar.png'; ?>" 
                                         alt="<?= htmlspecialchars($idea['username']); ?>" 
                                                 class="rounded-circle me-2" style="width: 24px; height: 24px; object-fit: cover;">
                                            <span><a href="profile_others.php?user_id=<?= $idea['user_id'] ?>" class="author-link"><?= htmlspecialchars($idea['username']); ?></a></span>
                                            <span class="ms-auto"><?= format_date_time($idea['created_at']) ?></span>
                                </div>
                                </div>
                                
                                    <!-- Description -->
                                    <p class="idea-desc">
                                    <?= nl2br(htmlspecialchars(substr($idea['description'], 0, 120))) ?><?= strlen($idea['description']) > 120 ? '...' : '' ?>
                                    </p>
                                
                                <!-- Tags -->
                                <?php if (!empty($idea['tags'])): ?>
                                        <div class="idea-tags">
                                            <?php 
                                            // Handle both JSON array format and comma-separated format
                                            $tags = [];
                                            if (strpos($idea['tags'], '[') === 0) {
                                                // JSON array format
                                                $tags = json_decode($idea['tags'], true);
                                                if (!is_array($tags)) {
                                                    $tags = [];
                                                }
                                            } else {
                                                // Comma-separated format
                                                $tags = array_map('trim', explode(',', $idea['tags']));
                                            }
                                            
                                            foreach ($tags as $tag): 
                                                if (!empty(trim($tag))):
                                            ?>
                                                <span class="tag"><?= htmlspecialchars(trim($tag)) ?></span>
                                            <?php 
                                                endif;
                                            endforeach; 
                                            ?>
                                    </div>
                                <?php endif; ?>
                                
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
                                        <div class="stat-item bookmark-count">
                                            <i class="bi bi-bookmark-fill"></i>
                                            <span><?= $idea['bookmarks_count'] ?? 0 ?></span>
                                        </div>
                                        <div class="stat-item view-count">
                                            <i class="bi bi-eye"></i>
                                            <span><?= $idea['views_count'] ?? 0 ?></span>
                                        </div>
                                    </div>
                                
                                    <!-- Actions -->
                                <div class="d-flex gap-2">
                                        <a href="idea.php?id=<?= $idea['id'] ?>" class="btn btn-gold flex-grow-1">
                                            <i class="bi bi-arrow-right me-2"></i><?php echo __('View Details'); ?>
                                        </a>
                                    <?php if (is_logged_in()): ?>
                                            <button class="btn btn-outline-gold" onclick="showReactions(<?= $idea['id'] ?>)">
                                            <i class="bi bi-emoji-smile"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    </section>

    <!-- CTA Section -->
    <section class="section text-center">
        <div class="container">
            <div class="p-5 rounded-3xl shadow-soft" style="background:linear-gradient(90deg, rgba(255,215,0,.15), rgba(255,215,0,.05));border:1px solid var(--border)">
                <h2 class="mb-2"><?php echo __('Ready to share your idea?'); ?> 💡</h2>
                <p class="subtle mb-4"><?php echo __('Join thousands of creators sharing and voting on amazing ideas.'); ?></p>
                <?php if (is_logged_in()): ?>
                    <a class="btn btn-gold" href="dashboard.php"><i class="bi bi-plus-circle me-2"></i><?php echo __('Submit Your Idea'); ?></a>
                <?php else: ?>
                    <a class="btn btn-gold" href="register.php"><i class="bi bi-person-plus me-2"></i><?php echo __('Join for Free'); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Floating Action Button -->
    <?php if (is_logged_in()): ?>
        <a href="dashboard.php" class="fab" title="<?php echo __('Add New Idea'); ?>">
            <i class="bi bi-plus-lg"></i>
        </a>
    <?php endif; ?>

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
            const button = event.target.closest('.bookmark-btn');
            const icon = button.querySelector('i');
            const isBookmarked = icon.classList.contains('bi-bookmark-fill');
            
            // Prevent multiple clicks
            if (button.disabled) return;
            button.disabled = true;
            
            // Show loading state
            const originalIcon = icon.className;
            icon.className = 'bi bi-arrow-clockwise spin';
            
            fetch('actions/bookmarks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=' + (isBookmarked ? 'remove' : 'add') + '&idea_id=' + ideaId
            })
            .then(response => response.text())
            .then(result => {
                if (result === 'bookmarked' || result === 'removed' || result === 'already_bookmarked') {
                    // Toggle the bookmark state
                    icon.classList.toggle('bi-bookmark');
                    icon.classList.toggle('bi-bookmark-fill');
                    button.classList.toggle('bookmarked');
                    
                    // Update bookmark count
                    const bookmarkCountElement = button.closest('.idea-card').querySelector('.bookmark-count span');
                    if (bookmarkCountElement) {
                        let currentCount = parseInt(bookmarkCountElement.textContent) || 0;
                        if (result === 'bookmarked') {
                            currentCount++;
                        } else if (result === 'removed') {
                            currentCount = Math.max(0, currentCount - 1);
                        }
                        bookmarkCountElement.textContent = currentCount;
                    }
                    
                    // Add animation
                    button.style.transform = 'scale(1.2)';
                    setTimeout(() => {
                        button.style.transform = 'scale(1)';
                    }, 200);
                    
                    // Show feedback
                    showToast(result === 'bookmarked' ? 'Bookmark added!' : 'Bookmark removed!', 'success');
                } else {
                    showToast('Error: ' + result, 'error');
                    // Restore original icon on error
                    icon.className = originalIcon;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error occurred while bookmarking', 'error');
                // Restore original icon on error
                icon.className = originalIcon;
            })
            .finally(() => {
                // Re-enable button
                button.disabled = false;
            });
        }
        
        // Toast notification function
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 p-3';
            toast.style.zIndex = '9999';
            
            const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
            const textClass = type === 'success' ? 'text-white' : type === 'error' ? 'text-white' : 'text-white';
            
            toast.innerHTML = `
                <div class="toast show ${bgClass} ${textClass}" role="alert">
                    <div class="toast-header ${bgClass} ${textClass}">
                        <strong class="me-auto">${type === 'success' ? 'Success!' : type === 'error' ? 'Error!' : 'Info'}</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
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
                            <div class="toast show" role="alert">
                                <div class="toast-header">
                                    <strong class="me-auto">Success!</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                                </div>
                                <div class="toast-body">
                                    Reaction added successfully! ${reaction}
                                </div>
                            </div>
                        `;
                        document.body.appendChild(toast);
                        
                        setTimeout(() => {
                            toast.remove();
                        }, 3000);
                    }
                });
            });
        });

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

        // Add loading animation for form submission
        document.querySelector('form').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Loading...';
            submitBtn.disabled = true;
        });

        // Add CSS for spinning animation and improved toast styling
        const style = document.createElement('style');
        style.textContent = `
            .spin {
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            
            .toast {
                border-radius: 12px;
                border: none;
                box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            }
            
            .bookmark-btn:disabled {
                opacity: 0.7;
                cursor: not-allowed;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html> 