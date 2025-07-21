<?php
ob_start();
include 'includes/navbar.php';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$categories = [];
$res = mysqli_query($conn, "SELECT id, name_en FROM categories");
while ($row = mysqli_fetch_assoc($res)) {
    $categories[] = $row;
}
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$sort = $_GET['sort'] ?? 'popular';
$query = "SELECT * FROM ideas WHERE is_public=1";
$params = [];
$types = '';
if ($category_id > 0) {
    $query .= " AND category_id=?";
    $params[] = $category_id;
    $types .= 'i';
}
$query .= " ORDER BY ";
if ($sort === 'recent') {
    $query .= "created_at DESC";
} else {
    $query .= "(SELECT COUNT(*) FROM votes WHERE votes.idea_id=ideas.id AND vote_type='like') DESC, created_at DESC";
}
$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$ideas = [];
while ($row = mysqli_fetch_assoc($res)) {
    $ideas[] = $row;
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ideas - IdeaVote</title>
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
            <form class="row g-2 align-items-center" method="get">
                <div class="col-md-4 col-12 mb-2 mb-md-0">
                    <select class="form-select" name="category_id">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name_en']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 col-6">
                    <select class="form-select" name="sort">
                        <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                        <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>>Most Recent</option>
                    </select>
                </div>
                <div class="col-md-4 col-6">
                    <button type="submit" class="btn btn-gold w-100">Filter</button>
                </div>
            </form>
        </div>
        <div class="row g-4">
            <?php if (empty($ideas)): ?>
                <div class="col-12">
                    <div class="alert alert-info glass-card text-center py-5">No ideas found. Try a different filter!</div>
                </div>
            <?php else: ?>
                <?php foreach ($ideas as $idea): ?>
                    <div class="col-md-6 col-lg-4">
                        <a href="idea.php?id=<?= $idea['id'] ?>" class="text-decoration-none">
                        <div class="glass-card h-100">
                            <?php if (!empty($idea['image_url'])): ?>
                                <img src="<?= htmlspecialchars($idea['image_url']) ?>" alt="Idea Image" class="idea-img">
                            <?php endif; ?>
                            <div class="p-3 d-flex flex-column h-100">
                                <div class="idea-title gold-gradient mb-1"><i class="bi bi-lightbulb icon-gold"></i> <?= htmlspecialchars($idea['title']) ?></div>
                                <div class="idea-meta mb-2">Category: <?= htmlspecialchars(get_category_name($idea['category_id'], 'en')) ?> &bull; <?= htmlspecialchars($idea['created_at']) ?></div>
                                <div class="idea-desc mb-2 flex-grow-1">
                                    <?= nl2br(htmlspecialchars(substr($idea['description'], 0, 120))) ?><?= strlen($idea['description']) > 120 ? '...' : '' ?>
                                </div>
                                <div class="vote-badges mt-auto mb-2">
                                    <span class="like"><i class="bi bi-hand-thumbs-up-fill"></i> <?= get_vote_counts($idea['id'])['like'] ?></span>
                                    <span class="dislike"><i class="bi bi-hand-thumbs-down-fill"></i> <?= get_vote_counts($idea['id'])['dislike'] ?></span>
                                </div>
                                <button class="btn btn-glass w-100 mt-1">View Details</button>
                            </div>
                        </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
</body>
</html> 