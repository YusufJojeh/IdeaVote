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
$stmt = $pdo->query("SELECT id, name_en, name_ar, description, icon FROM categories ORDER BY name_en");
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
          (SELECT COUNT(*) FROM bookmarks WHERE bookmarks.idea_id=i.id) as bookmarks_count,
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
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            background: var(--offwhite);
            color: var(--black);
            min-height: 100vh;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .ideas-hero {
            background: rgba(255,255,255,0.15);
            border-radius: 36px;
            box-shadow: 0 8px 32px 0 rgba(24,24,24,0.10);
            padding: 2.5rem 2rem;
            margin-bottom: 2rem;
        }
        .glass-card {
            background: rgba(255,255,255,0.85);
            border-radius: 24px;
            box-shadow: 0 4px 24px 0 rgba(24,24,24,0.10);
            border: 1.5px solid #eee;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }
        .glass-card:hover {
            transform: translateY(-6px) scale(1.03);
            box-shadow: 0 8px 32px 0 rgba(255,215,0,0.18);
        }
        .idea-img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 18px 18px 0 0;
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
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--black);
        }
        .idea-meta {
            color: #888;
            font-size: 0.98em;
        }
        .idea-desc {
            color: var(--gray);
            font-size: 1.05em;
            min-height: 60px;
        }
        .vote-badges {
            font-size: 1.1em;
        }
        .vote-badges .like {
            color: var(--gold);
            margin-right: 1.2em;
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
        .filter-bar {
            background: rgba(255,255,255,0.7);
            border-radius: 18px;
            box-shadow: 0 2px 12px rgba(24,24,24,0.04);
            padding: 1.2rem 1rem;
            margin-bottom: 2rem;
        }
        .footer-lux {
            background: #fff;
            color: var(--gray);
            box-shadow: 0 -4px 32px rgba(24,24,24,0.06);
            border-top-left-radius: 36px;
            border-top-right-radius: 36px;
            border-top: 1.5px solid #eee;
        }
        .footer-link {
            color: var(--gold);
            opacity: 0.9;
            margin-right: 1.5rem;
            transition: opacity 0.2s;
        }
        .footer-link:hover {
            opacity: 1;
            text-decoration: underline;
        }
        @media (max-width: 767px) {
            .idea-img { height: 140px; }
            .glass-card { margin-bottom: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="ideas-hero text-center mb-4">
            <h1 class="gold-gradient mb-2" style="font-size:2.5rem;letter-spacing:1px;">Discover & Vote on Brilliant Ideas</h1>
            <p class="lead" style="color:var(--gray);max-width:600px;margin:auto;">Browse, filter, and support the most creative ideas. Click any card to see more details, vote, and join the conversation!</p>
        </div>
        <div class="filter-bar mb-4">
            <form class="row g-3 align-items-end" method="get">
                <div class="col-md-3 col-12 mb-2 mb-md-0">
                    <label class="form-label small fw-bold"><?php echo __('Category'); ?></label>
                    <select class="form-select" name="category_id">
                        <option value="0"><?php echo __('All Categories'); ?></option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars(current_language() === 'ar' ? $cat['name_ar'] : $cat['name_en']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label small fw-bold"><?php echo __('Sort By'); ?></label>
                    <select class="form-select" name="sort">
                        <option value="trending" <?= $sort === 'trending' ? 'selected' : '' ?>><?php echo __('Trending'); ?></option>
                        <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>><?php echo __('Most Popular'); ?></option>
                        <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>><?php echo __('Most Recent'); ?></option>
                        <option value="most_viewed" <?= $sort === 'most_viewed' ? 'selected' : '' ?>><?php echo __('Most Viewed'); ?></option>
                        <option value="most_commented" <?= $sort === 'most_commented' ? 'selected' : '' ?>><?php echo __('Most Commented'); ?></option>
                    </select>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small fw-bold"><?php echo __('Search'); ?></label>
                    <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="<?php echo __('Search ideas...'); ?>">
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label small fw-bold"><?php echo __('Tags'); ?></label>
                    <input type="text" class="form-control" name="tags" value="<?= htmlspecialchars($tags) ?>" placeholder="<?php echo __('Filter by tags...'); ?>">
                </div>
                <div class="col-md-2 col-6">
                    <button type="submit" class="btn btn-gold w-100"><?php echo __('Filter'); ?></button>
                </div>
            </form>
        </div>
        <div class="row g-4">
            <?php if (empty($ideas)): ?>
                <div class="col-12">
                    <div class="alert alert-info glass-card text-center py-5"><?php echo __('No ideas found. Try a different filter!'); ?></div>
                </div>
            <?php else: ?>
                <?php foreach ($ideas as $idea): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="glass-card h-100 position-relative">
                            <?php if (!empty($idea['image_url'])): ?>
                                <img src="<?= htmlspecialchars($idea['image_url']) ?>" alt="Idea Image" class="idea-img">
                            <?php endif; ?>
                            
                            <!-- Bookmark button -->
                            <?php if (is_logged_in()): ?>
                                <button class="btn btn-sm position-absolute top-0 end-0 m-2 bookmark-btn" 
                                        data-idea-id="<?= $idea['id'] ?>"
                                        onclick="toggleBookmark(<?= $idea['id'] ?>)">
                                    <i class="bi bi-bookmark<?= in_array($idea['id'], $user_bookmarks) ? '-fill' : '' ?>"></i>
                                </button>
                            <?php endif; ?>
                            
                            <div class="p-3 d-flex flex-column h-100">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="<?= $idea['avatar'] ?: 'assets/images/default-avatar.png'; ?>" 
                                         alt="<?= htmlspecialchars($idea['username']); ?>" 
                                         class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                    <small class="text-muted"><?= htmlspecialchars($idea['username']); ?></small>
                                </div>
                                
                                <div class="idea-title gold-gradient mb-1">
                                    <i class="bi bi-lightbulb icon-gold"></i> 
                                    <a href="idea.php?id=<?= $idea['id'] ?>" class="text-decoration-none gold-gradient">
                                        <?= htmlspecialchars($idea['title']) ?>
                                    </a>
                                </div>
                                
                                <div class="idea-meta mb-2">
                                    <span class="badge bg-light text-dark me-1"><?= htmlspecialchars($idea['category_name']) ?></span>
                                    <small class="text-muted"><?= format_date($idea['created_at']) ?></small>
                                </div>
                                
                                <div class="idea-desc mb-2 flex-grow-1">
                                    <?= nl2br(htmlspecialchars(substr($idea['description'], 0, 120))) ?><?= strlen($idea['description']) > 120 ? '...' : '' ?>
                                </div>
                                
                                <!-- Tags -->
                                <?php if (!empty($idea['tags'])): ?>
                                    <div class="mb-2">
                                        <?php foreach (explode(',', $idea['tags']) as $tag): ?>
                                            <span class="badge bg-secondary me-1"><?= htmlspecialchars(trim($tag)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="vote-badges mt-auto mb-2 d-flex justify-content-between">
                                    <div>
                                        <span class="like me-2"><i class="bi bi-hand-thumbs-up-fill"></i> <?= $idea['likes'] ?></span>
                                        <span class="dislike me-2"><i class="bi bi-hand-thumbs-down-fill"></i> <?= $idea['dislikes'] ?></span>
                                        <span class="text-muted"><i class="bi bi-chat"></i> <?= $idea['comments_count'] ?></span>
                                    </div>
                                    <div class="text-muted">
                                        <i class="bi bi-eye"></i> <?= $idea['views_count'] ?? 0 ?>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <a href="idea.php?id=<?= $idea['id'] ?>" class="btn btn-glass flex-grow-1"><?php echo __('View Details'); ?></a>
                                    <?php if (is_logged_in()): ?>
                                        <button class="btn btn-outline-secondary btn-sm reaction-btn" 
                                                onclick="showReactions(<?= $idea['id'] ?>)">
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
    <footer class="py-4 footer-lux text-center border-0 mt-5">
        <div class="container">
            <div class="mb-2">
                <a href="contact.php" class="footer-link"><?php echo __('Contact'); ?></a>
                <a href="ideas.php" class="footer-link"><?php echo __('Browse Ideas'); ?></a>
                <a href="register.php" class="footer-link"><?php echo __('Register'); ?></a>
            </div>
            <small style="color: var(--gray);"><?php echo __('All rights reserved &copy; Idea Voting Platform 2024'); ?></small>
        </div>
    </footer>

    <!-- Reaction Modal -->
    <div class="modal fade" id="reactionModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo __('Add Reaction'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
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

    <script>
        // Bookmark functionality
        function toggleBookmark(ideaId) {
            fetch('actions/bookmarks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=' + (document.querySelector(`[data-idea-id="${ideaId}"] i`).classList.contains('bi-bookmark-fill') ? 'remove' : 'add') + '&idea_id=' + ideaId
            })
            .then(response => response.text())
            .then(result => {
                if (result === 'success') {
                    const icon = document.querySelector(`[data-idea-id="${ideaId}"] i`);
                    icon.classList.toggle('bi-bookmark');
                    icon.classList.toggle('bi-bookmark-fill');
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
                        // Optionally refresh the page or update UI
                        location.reload();
                    }
                });
            });
        });

        // Dark mode support
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.body.setAttribute('data-theme', savedTheme);
        });
    </script>
</body>
</html> 