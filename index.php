<?php
ob_start();
include 'includes/config.php';
include 'includes/db.php';
include 'includes/i18n.php';
include 'includes/functions.php';

// Get real statistics from database
$stats = [];
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_ideas FROM ideas");
    $stats['ideas'] = $stmt->fetch()['total_ideas'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $stats['users'] = $stmt->fetch()['total_users'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total_votes FROM votes");
    $stats['votes'] = $stmt->fetch()['total_votes'];
    
    // Get trending ideas
    $stmt = $pdo->query("
        SELECT i.*, u.username, u.avatar, 
               COUNT(v.id) as vote_count,
               COUNT(DISTINCT c.id) as comment_count
        FROM ideas i 
        LEFT JOIN users u ON i.user_id = u.id
        LEFT JOIN votes v ON i.id = v.idea_id
        LEFT JOIN comments c ON i.id = c.idea_id
        WHERE i.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY i.id
        ORDER BY vote_count DESC, comment_count DESC
        LIMIT 3
    ");
    $trending_ideas = $stmt->fetchAll();
} catch (Exception $e) {
    $stats = ['ideas' => 0, 'users' => 0, 'votes' => 0];
    $trending_ideas = [];
}

include 'includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="<?php echo current_language(); ?>" dir="<?php echo lang_dir(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('IdeaVote – The Ultimate Idea Voting Platform'); ?></title>
    <meta name="description" content="<?php echo __('Share, vote, and discover world-changing ideas on IdeaVote - the most inspiring platform for innovators.'); ?>">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo __('IdeaVote – The Ultimate Idea Voting Platform'); ?>">
    <meta property="og:description" content="<?php echo __('Share, vote, and discover world-changing ideas on IdeaVote - the most inspiring platform for innovators.'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $_SERVER['REQUEST_URI']; ?>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/countup.js@2.0.7/dist/countUp.umd.js"></script>
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
        
        .btn-gold {
            background: linear-gradient(90deg, var(--gold) 0%, var(--gold-light) 100%);
            color: #fff;
            font-weight: bold;
            border: none;
            box-shadow: 0 2px 12px rgba(255,215,0,0.10);
        }
        
        .btn-gold:hover {
            background: linear-gradient(90deg, var(--gold-light) 0%, var(--gold) 100%);
            color: var(--black);
        }
        
        .hero-section {
            min-height: 90vh;
            display: flex;
            align-items: center;
            background: linear-gradient(120deg, var(--offwhite) 0%, #f8fafc 100%);
            color: var(--black);
            position: relative;
            overflow: hidden;
            transition: background 0.3s ease;
        }
        
        [data-theme="dark"] .hero-section {
            background: linear-gradient(120deg, var(--dark-bg) 0%, #1f1f1f 100%);
        }
        
        .hero-glass {
            background: rgba(255,255,255,0.85);
            box-shadow: 0 8px 32px 0 rgba(24,24,24,0.08);
            backdrop-filter: blur(14px);
            border-radius: 36px;
            border: 1.5px solid #eee;
            padding: 3.5rem 2.5rem;
            transition: background 0.3s ease, border-color 0.3s ease;
        }
        
        [data-theme="dark"] .hero-glass {
            background: rgba(45,45,45,0.85);
            border: 1.5px solid #444;
        }
        
        .hero-img {
            max-width: 500px;
            border-radius: 36px;
            box-shadow: 0 8px 32px rgba(24,24,24,0.10);
            border: 4px solid #fff;
            animation: float 4s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-18px); }
        }
        
        .feature-card {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(24,24,24,0.06);
            padding: 2.2rem 1.5rem;
            margin-bottom: 2rem;
            border: 1.5px solid #eee;
            color: var(--black);
            transition: all 0.3s ease;
        }
        
        [data-theme="dark"] .feature-card {
            background: rgba(45,45,45,0.95);
            border: 1.5px solid #444;
            color: var(--dark-text);
        }
        
        .feature-card:hover {
            transform: translateY(-10px) scale(1.04);
            box-shadow: 0 12px 32px rgba(255,215,0,0.10);
        }
        
        .feature-icon {
            width: 70px;
            height: 70px;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 2.5rem;
            box-shadow: 0 2px 12px rgba(255,215,0,0.10);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 2px 12px rgba(255,215,0,0.10); }
            50% { box-shadow: 0 8px 32px rgba(255,215,0,0.18); }
            100% { box-shadow: 0 2px 12px rgba(255,215,0,0.10); }
        }
        
        .counter {
            color: var(--gold);
            text-shadow: 0 2px 8px #fffbe6;
            font-size: 2.7rem;
            font-weight: bold;
        }
        
        .counter-label {
            color: var(--gray);
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .testimonial {
            border-left: 4px solid var(--gold);
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(6px);
            color: var(--black);
            transition: background 0.3s ease, color 0.3s ease;
        }
        
        [data-theme="dark"] .testimonial {
            background: rgba(45,45,45,0.98);
            color: var(--dark-text);
        }
        
        .testimonial-avatar {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 0.5rem;
            border: 2px solid var(--gold);
        }
        
        .footer-lux {
            background: var(--offwhite);
            color: var(--gray);
            box-shadow: 0 -4px 32px rgba(24,24,24,0.06);
            border-top-left-radius: 36px;
            border-top-right-radius: 36px;
            border-top: 1.5px solid #eee;
            transition: background 0.3s ease, border-color 0.3s ease;
        }
        
        [data-theme="dark"] .footer-lux {
            background: var(--dark-card);
            border-top: 1.5px solid #444;
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
        
        .animated-bg {
            position: absolute;
            top: -100px;
            right: -100px;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, var(--gold) 0%, var(--offwhite) 80%);
            opacity: 0.10;
            filter: blur(60px);
            z-index: 0;
            transition: background 0.3s ease;
        }
        
        [data-theme="dark"] .animated-bg {
            background: radial-gradient(circle, var(--gold) 0%, var(--dark-bg) 80%);
        }
        
        .section-title {
            font-weight: bold;
            font-size: 2.3rem;
            margin-bottom: 1.5rem;
            letter-spacing: 1px;
            color: var(--black);
            transition: color 0.3s ease;
        }
        
        [data-theme="dark"] .section-title {
            color: var(--dark-text);
        }
        
        .trending-idea-card {
            background: rgba(255,255,255,0.95);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid #eee;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        [data-theme="dark"] .trending-idea-card {
            background: rgba(45,45,45,0.95);
            border: 1px solid #444;
        }
        
        .trending-idea-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(255,215,0,0.15);
            text-decoration: none;
            color: inherit;
        }
        
        .trending-idea-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--gold);
        }
        
        .trending-stats {
            display: flex;
            gap: 1rem;
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        .trending-stats i {
            color: var(--gold);
        }
        
        .language-switcher {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1000;
            background: rgba(255,255,255,0.9);
            border-radius: 25px;
            padding: 0.5rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            transition: background 0.3s ease;
        }
        
        [data-theme="dark"] .language-switcher {
            background: rgba(45,45,45,0.9);
        }
        
        .language-switcher .btn {
            border-radius: 20px;
            padding: 0.25rem 0.75rem;
            font-size: 0.8rem;
            border: none;
            background: transparent;
            color: var(--gray);
            transition: all 0.3s ease;
        }
        
        .language-switcher .btn.active {
            background: var(--gold);
            color: #fff;
        }
        
        .language-switcher .btn:hover {
            background: var(--gold);
            color: #fff;
        }
    </style>
</head>
<body data-theme="light">
    <!-- Language Switcher -->
    <div class="language-switcher">
        <button class="btn <?php echo current_language() === 'en' ? 'active' : ''; ?>" onclick="switchLanguage('en')">EN</button>
        <button class="btn <?php echo current_language() === 'ar' ? 'active' : ''; ?>" onclick="switchLanguage('ar')">عربي</button>
    </div>

    <!-- Hero Section -->
    <section class="hero-section position-relative">
        <div class="animated-bg"></div>
        <div class="container position-relative" style="z-index:2;">
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="hero-glass mx-auto text-center">
                        <h1 class="display-2 fw-bold mb-3 gold-gradient"><?php echo __('Where Ideas Become Gold'); ?></h1>
                        <p class="lead mb-4" style="color: var(--gray);"><?php echo __('Welcome to'); ?> <b>IdeaVote</b> – <?php echo __('the most inspiring, beautiful, and powerful platform for sharing, voting, and discovering world-changing ideas. Join a community where your creativity shines and every vote counts.'); ?></p>
                        <a href="register.php" class="btn btn-gold btn-lg cta-btn shadow"><?php echo __('Get Started Free'); ?></a>
                        <div class="d-flex justify-content-center gap-4 mt-4">
                            <span class="feature-icon"><i class="bi bi-lightbulb"></i></span>
                            <span class="feature-icon"><i class="bi bi-stars"></i></span>
                            <span class="feature-icon"><i class="bi bi-people-fill"></i></span>
                            <span class="feature-icon"><i class="bi bi-hand-thumbs-up-fill"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-flex justify-content-center align-items-center">
                    <img src="https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=600&q=80" alt="<?php echo __('Golden Ideas'); ?>" class="img-fluid hero-img" loading="lazy">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="gold-gradient section-title"><?php echo __('Why Choose IdeaVote?'); ?></span>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <span class="feature-icon"><i class="bi bi-lightbulb"></i></span>
                        <h5 class="fw-bold mb-2 gold-gradient"><?php echo __('Share Brilliant Ideas'); ?></h5>
                        <p style="color: var(--gray);"><?php echo __('Post your creative, project, or personal ideas and let the world see your brilliance. Every idea is a spark of gold.'); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <span class="feature-icon"><i class="bi bi-hand-thumbs-up"></i></span>
                        <h5 class="fw-bold mb-2 gold-gradient"><?php echo __('Vote & Empower'); ?></h5>
                        <p style="color: var(--gray);"><?php echo __('Like or dislike ideas, join lively discussions, and help the best ideas rise to the top. Your vote is golden.'); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <span class="feature-icon"><i class="bi bi-people"></i></span>
                        <h5 class="fw-bold mb-2 gold-gradient"><?php echo __('Elite Community'); ?></h5>
                        <p style="color: var(--gray);"><?php echo __('Connect with a vibrant, ambitious community, collaborate, and make your voice heard in a world of innovators.'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Counters Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="feature-card">
                        <span class="feature-icon"><i class="bi bi-lightbulb"></i></span>
                        <div class="counter" id="ideasCounter">0</div>
                        <div class="counter-label"><?php echo __('Ideas Shared'); ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <span class="feature-icon"><i class="bi bi-people-fill"></i></span>
                        <div class="counter" id="usersCounter">0</div>
                        <div class="counter-label"><?php echo __('Active Users'); ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <span class="feature-icon"><i class="bi bi-hand-thumbs-up-fill"></i></span>
                        <div class="counter" id="votesCounter">0</div>
                        <div class="counter-label"><?php echo __('Votes Cast'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trending Ideas Section -->
    <?php if (!empty($trending_ideas)): ?>
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="gold-gradient section-title"><?php echo __('Trending Ideas'); ?></span>
            </div>
            <div class="row">
                <?php foreach ($trending_ideas as $idea): ?>
                <div class="col-md-4 mb-3">
                    <a href="idea.php?id=<?php echo $idea['id']; ?>" class="trending-idea-card">
                        <div class="d-flex align-items-center mb-2">
                            <img src="<?php echo $idea['avatar'] ?: 'assets/images/default-avatar.png'; ?>" 
                                 alt="<?php echo htmlspecialchars($idea['username']); ?>" 
                                 class="trending-idea-avatar me-2">
                            <span class="fw-bold"><?php echo htmlspecialchars($idea['username']); ?></span>
                        </div>
                        <h6 class="fw-bold mb-2"><?php echo htmlspecialchars($idea['title']); ?></h6>
                        <p class="text-muted small mb-2"><?php echo substr(htmlspecialchars($idea['description']), 0, 100); ?>...</p>
                        <div class="trending-stats">
                            <span><i class="bi bi-hand-thumbs-up"></i> <?php echo $idea['vote_count']; ?></span>
                            <span><i class="bi bi-chat"></i> <?php echo $idea['comment_count']; ?></span>
                            <span><i class="bi bi-eye"></i> <?php echo $idea['views_count'] ?? 0; ?></span>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Testimonials Carousel -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="gold-gradient section-title"><?php echo __('What Our Users Say'); ?></span>
            </div>
            <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="testimonial p-4 feature-card shadow-sm mx-auto" style="max-width:600px;">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" class="testimonial-avatar" alt="Alex">
                            <div class="mb-2"><i class="bi bi-quote fs-2 gold-gradient"></i></div>
                            <span class="gold-gradient">"<?php echo __('A platform that truly values my ideas!'); ?>"</span><br><span class="fw-bold" style="color: var(--black);">- Alex</span>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="testimonial p-4 feature-card shadow-sm mx-auto" style="max-width:600px;">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" class="testimonial-avatar" alt="Sarah">
                            <div class="mb-2"><i class="bi bi-quote fs-2 gold-gradient"></i></div>
                            <span class="gold-gradient">"<?php echo __('The best place to get feedback and support!'); ?>"</span><br><span class="fw-bold" style="color: var(--black);">- Sarah</span>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="testimonial p-4 feature-card shadow-sm mx-auto" style="max-width:600px;">
                            <img src="https://randomuser.me/api/portraits/men/54.jpg" class="testimonial-avatar" alt="John">
                            <div class="mb-2"><i class="bi bi-quote fs-2 gold-gradient"></i></div>
                            <span class="gold-gradient">"<?php echo __('Inspiring, beautiful, and easy to use.'); ?>"</span><br><span class="fw-bold" style="color: var(--black);">- John</span>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5" style="background: linear-gradient(90deg, var(--gold) 0%, var(--gold-light) 100%); color: var(--black);">
        <div class="container text-center">
            <h2 class="mb-3 fw-bold gold-gradient"><?php echo __('Ready to turn your ideas into gold?'); ?></h2>
            <a href="register.php" class="btn btn-gold btn-lg px-5 py-3 fs-5"><?php echo __('Join IdeaVote Now'); ?></a>
        </div>
    </section>

    <!-- Footer -->
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

    <script>
        // Animated counters with real data
        document.addEventListener('DOMContentLoaded', function() {
            var ideas = new countUp.CountUp('ideasCounter', <?php echo $stats['ideas']; ?>, {duration: 2});
            var users = new countUp.CountUp('usersCounter', <?php echo $stats['users']; ?>, {duration: 2});
            var votes = new countUp.CountUp('votesCounter', <?php echo $stats['votes']; ?>, {duration: 2});
            ideas.start(); users.start(); votes.start();
        });

        // Language switcher
        function switchLanguage(lang) {
            fetch('actions/language.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'language=' + lang
            }).then(() => {
                window.location.reload();
            });
        }

        // Dark mode toggle (if not already in navbar)
        function toggleDarkMode() {
            const body = document.body;
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }

        // Load saved theme
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.body.setAttribute('data-theme', savedTheme);
        });
    </script>
</body>
</html>