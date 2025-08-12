<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/i18n.php';

// Stats + trending ideas
$idea_count = $user_count = $vote_count = 0;
$trending_ideas = [];
try {
    $idea_count = (int)$pdo->query("SELECT COUNT(*) AS c FROM ideas WHERE is_public = 1")->fetch()['c'];
    $user_count = (int)$pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
    $vote_count = (int)$pdo->query("SELECT COUNT(*) AS c FROM votes")->fetch()['c'];

    $stmt = $pdo->query(
        "SELECT i.id, i.title, i.description, i.image_url, i.votes_count, i.views_count,
                c.name_en AS category_name, u.username,
               (SELECT COUNT(*) FROM comments WHERE idea_id = i.id) AS comments_count
        FROM ideas i 
         LEFT JOIN categories c ON c.id = i.category_id
         LEFT JOIN users u ON u.id = i.user_id
         WHERE i.is_public = 1
         ORDER BY i.trending_score DESC, i.views_count DESC
         LIMIT 6"
    );
    $trending_ideas = $stmt->fetchAll();
} catch (Throwable $e) {
    // fail silently on landing
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('Voteapp by IdeaVote — Share Ideas, Win Votes, Build Together') ?> 🚀</title>
    <meta name="description" content="<?= __('Post ideas, gather reactions, and climb the trending board. Collaborate with a global community to turn sparks into products.') ?>">
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
        .btn-outline-gold{border:2px solid var(--gold);color:#fff}
        .section{padding:4.5rem 0}
        .subtle{color:var(--muted)}
        .rounded-3xl{border-radius:1.25rem}
        .shadow-soft{box-shadow:0 10px 30px rgba(0,0,0,.25)}
        .badge-gold{background:rgba(255,215,0,.15);border:1px solid rgba(255,215,0,.35);color:#ffe98f}

        /* Hero */
        .hero{position:relative;min-height:82vh;display:flex;align-items:center}
        .hero::before{content:"";position:absolute;inset:0;background:url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1900&q=80') center/cover no-repeat;filter:brightness(.35)}
        .hero::after{content:"";position:absolute;inset:0;background:radial-gradient(80% 60% at 50% 0%,rgba(255,215,0,.18),transparent 60%)}
        .hero .content{position:relative;z-index:2}
        .hero h1{font-weight:800;letter-spacing:-.02em}
        .hero-cta .btn{padding:.85rem 1.2rem;border-radius:.85rem}
        .hero-stat{backdrop-filter:blur(8px);background:rgba(15,20,28,.65);border:1px solid var(--border)}

        /* Idea cards */
        .idea-card{background:var(--card);border:1px solid var(--border)}
        .idea-card .title{font-weight:700}
        .idea-card .meta{color:#aab1bb}
        .idea-thumb{width:100%;height:160px;object-fit:cover;border-top-left-radius:1rem;border-top-right-radius:1rem}

        /* Trust bar */
        .trust img{opacity:.85;filter:grayscale(1);transition:opacity .2s}
        .trust img:hover{opacity:1}

        /* Gallery */
        .gallery img{border-radius:1rem;border:1px solid var(--border);height:230px;object-fit:cover}

        /* Testimonials */
        .quote{background:linear-gradient(180deg,rgba(255,215,0,.08),rgba(255,215,0,.02));border:1px solid var(--border)}

        /* FAQ */
        .accordion-button{background:var(--card);color:var(--text);border:1px solid var(--border)}
        .accordion-item{background:transparent;border:0}

        .footer{border-top:1px solid var(--border);color:var(--muted)}

        /* Gold Gradient Text Effect */
        .gold-gradient-text {
            background: linear-gradient(135deg, 
                #FFD700 0%, 
                #FFEF8E 25%, 
                #FFD700 50%, 
                #FFEF8E 75%, 
                #FFD700 100%);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: goldShimmer 3s ease-in-out infinite;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(255, 215, 0, 0.3);
        }

        .gold-gradient-text-static {
            background: linear-gradient(135deg, 
                #FFD700 0%, 
                #FFEF8E 25%, 
                #FFD700 50%, 
                #FFEF8E 75%, 
                #FFD700 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(255, 215, 0, 0.3);
        }

        @keyframes goldShimmer {
            0%, 100% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
        }

        /* Enhanced gold gradient with more colors */
        .gold-gradient-rich {
            background: linear-gradient(135deg, 
                #FFD700 0%, 
                #FFEF8E 15%, 
                #FFF8DC 30%, 
                #FFEF8E 45%, 
                #FFD700 60%, 
                #FFEF8E 75%, 
                #FFF8DC 90%, 
                #FFD700 100%);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: goldShimmerRich 4s ease-in-out infinite;
            font-weight: 800;
            text-shadow: 0 4px 8px rgba(255, 215, 0, 0.4);
        }

        @keyframes goldShimmerRich {
            0%, 100% {
                background-position: 0% 50%;
            }
            25% {
                background-position: 100% 50%;
            }
            50% {
                background-position: 100% 100%;
            }
            75% {
                background-position: 0% 100%;
            }
        }

        /* Dark mode adjustments */
        [data-theme="dark"] .gold-gradient-text,
        [data-theme="dark"] .gold-gradient-text-static,
        [data-theme="dark"] .gold-gradient-rich {
            text-shadow: 0 2px 4px rgba(255, 215, 0, 0.5);
        }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<!-- Hero -->
<header class="hero">
  <div class="container content">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <span class="badge badge-gold rounded-pill px-3 py-2 mb-3">🔥 <?= __('The community where ideas win') ?></span>
        <h1 class="gold-gradient-text">
            Voteapp by IdeaVote — Share Ideas, Win Votes, Build Together <span style="color:none;">🚀</span>
        </h1>
        <p class="gold-gradient-text">
            Post ideas, gather reactions, and climb the trending board. Collaborate with a global community to turn sparks into products.
        </p>
        <div class="hero-cta d-flex gap-3 flex-wrap">
          <?php if (is_logged_in()): ?>
            <a href="dashboard.php" class="btn btn-gold"><i class="bi bi-rocket-takeoff-fill me-2"></i><?= __('Submit your idea') ?></a>
            <a href="ideas.php" class="btn btn-outline-gold"><i class="bi bi-stars me-2"></i><?= __('Explore trending') ?></a>
          <?php else: ?>
            <a href="register.php" class="btn btn-gold"><i class="bi bi-person-plus-fill me-2"></i><?= __('Join for free') ?></a>
            <a href="ideas.php" class="btn btn-outline-gold"><i class="bi bi-lightbulb me-2"></i><?= __('Browse ideas') ?></a>
          <?php endif; ?>
        </div>
        <div class="d-flex gap-3 mt-4">
          <div class="p-3 rounded-3 hero-stat"><div class="h3 mb-0" id="statIdeas">0</div><div class="subtle"><?= __('Ideas shared') ?></div></div>
          <div class="p-3 rounded-3 hero-stat"><div class="h3 mb-0" id="statUsers">0</div><div class="subtle"><?= __('Members') ?></div></div>
          <div class="p-3 rounded-3 hero-stat"><div class="h3 mb-0" id="statVotes">0</div><div class="subtle"><?= __('Votes cast') ?></div></div>
        </div>
    </div>
      <div class="col-lg-5 mt-5 mt-lg-0">
        <div class="rounded-3xl overflow-hidden shadow-soft border" style="border-color:var(--border)">
          <img alt="Brainstorm" class="w-100" style="object-fit:cover;height:420px" src="https://images.unsplash.com/photo-1553877522-43269d4ea984?auto=format&fit=crop&w=1200&q=80">
                        </div>
                    </div>
                </div>
                </div>
</header>

<!-- Trust bar -->
<section class="section trust">
  <div class="container text-center">
    <div class="subtle mb-4"><?= __('Join thousands of innovators worldwide') ?></div>
    <div class="row g-4 justify-content-center align-items-center">
      <div class="col-6 col-sm-4 col-md-2">
        <div class="trust-stat">
          <div class="h4 gold mb-1">10K+</div>
          <div class="small subtle"><?= __('Active Users') ?></div>
        </div>
      </div>
      <div class="col-6 col-sm-4 col-md-2">
        <div class="trust-stat">
          <div class="h4 gold mb-1">5K+</div>
          <div class="small subtle"><?= __('Ideas Shared') ?></div>
        </div>
      </div>
      <div class="col-6 col-sm-4 col-md-2">
        <div class="trust-stat">
          <div class="h4 gold mb-1">50K+</div>
          <div class="small subtle"><?= __('Votes Cast') ?></div>
        </div>
      </div>
      <div class="col-6 col-sm-4 col-md-2">
        <div class="trust-stat">
          <div class="h4 gold mb-1">100+</div>
          <div class="small subtle"><?= __('Countries') ?></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Trending ideas -->
<section class="section">
        <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-3">
      <div>
        <h2 class="mb-1"><?= __('Trending now') ?> 🔥</h2>
        <div class="subtle"><?= __('Fresh ideas getting the most love right now.') ?></div>
            </div>
      <a href="ideas.php" class="btn btn-outline-gold"><?= __('See all') ?></a>
                    </div>
    <div class="row g-4">
      <?php if (empty($trending_ideas)): ?>
        <div class="col-12"><div class="alert alert-secondary border-0"><?= __('No trending ideas yet. Be the first to post!') ?> ✨</div></div>
      <?php else: foreach ($trending_ideas as $idea): ?>
        <div class="col-md-6 col-lg-4">
          <div class="idea-card rounded-3xl shadow-soft h-100">
            <?php $thumb = $idea['image_url'] ?: 'https://images.unsplash.com/photo-1504384764586-bb4cdc1707b0?auto=format&fit=crop&w=1200&q=60'; ?>
            <img class="idea-thumb" src="<?= htmlspecialchars($thumb) ?>" alt="Idea thumbnail">
            <div class="p-3">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="title text-truncate"><?= htmlspecialchars($idea['title']) ?></div>
                <span class="badge badge-gold rounded-pill px-2 py-1"><?= htmlspecialchars($idea['category_name'] ?: __('General')) ?></span>
                </div>
              <div class="subtle mt-1 mb-2" style="min-height:48px;"><?= htmlspecialchars(str_truncate($idea['description'], 110)) ?></div>
              <div class="d-flex justify-content-between meta">
                <div class="d-flex gap-3">
                  <span><i class="bi bi-hand-thumbs-up-fill gold"></i> <?= (int)$idea['votes_count'] ?></span>
                  <span><i class="bi bi-chat-left-text gold"></i> <?= (int)$idea['comments_count'] ?></span>
                  <span><i class="bi bi-eye gold"></i> <?= (int)$idea['views_count'] ?></span>
                    </div>
                <a class="stretched-link text-decoration-none" href="idea.php?id=<?= (int)$idea['id'] ?>"><?= __('Read') ?></a>
                    </div>
                </div>
          </div>
        </div>
      <?php endforeach; endif; ?>
            </div>
        </div>
    </section>

<!-- How it works -->
<section class="section">
        <div class="container">
    <h2 class="mb-4"><?= __('How it works') ?> ⚙️</h2>
    <div class="row g-4">
      <div class="col-md-4"><div class="p-4 rounded-3xl idea-card h-100"><div class="fs-2">🧠</div><h5 class="mt-2"><?= __('Share your idea') ?></h5><p class="subtle"><?= __('Share your idea desc') ?></p></div></div>
      <div class="col-md-4"><div class="p-4 rounded-3xl idea-card h-100"><div class="fs-2">💬</div><h5 class="mt-2"><?= __('Gather feedback') ?></h5><p class="subtle"><?= __('Gather feedback desc') ?></p></div></div>
      <div class="col-md-4"><div class="p-4 rounded-3xl idea-card h-100"><div class="fs-2">📈</div><h5 class="mt-2"><?= __('Climb the trends') ?></h5><p class="subtle"><?= __('Climb the trends desc') ?></p></div></div>
                    </div>
                </div>
</section>

<!-- Gallery -->
<section class="section">
  <div class="container">
    <h2 class="mb-4"><?= __('Made by makers, worldwide') ?> 🌍</h2>
    <div class="row g-3 gallery">
      <div class="col-6 col-md-3"><img class="w-100" src="https://images.unsplash.com/photo-1529336953121-ad5a0d43d0d2?auto=format&fit=crop&w=800&q=60" alt="workspace"></div>
      <div class="col-6 col-md-3"><img class="w-100" src="https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=800&q=60" alt="team"></div>
      <div class="col-6 col-md-3"><img class="w-100" src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=800&q=60" alt="collab"></div>
      <div class="col-6 col-md-3"><img class="w-100" src="https://images.unsplash.com/photo-1529333166437-7750f0f9b1e2?auto=format&fit=crop&w=800&q=60" alt="whiteboard"></div>
            </div>
        </div>
    </section>

<!-- Testimonials -->
<section class="section">
        <div class="container">
    <h2 class="mb-4"><?= __('Loved by creators') ?> ❤️</h2>
    <div class="row g-4">
      <div class="col-md-4"><div class="p-4 rounded-3xl quote h-100"><p class="mb-3"><?= __('quote1') ?></p><div class="subtle">— Lina</div></div></div>
      <div class="col-md-4"><div class="p-4 rounded-3xl quote h-100"><p class="mb-3"><?= __('quote2') ?></p><div class="subtle">— Omar</div></div></div>
      <div class="col-md-4"><div class="p-4 rounded-3xl quote h-100"><p class="mb-3"><?= __('quote3') ?></p><div class="subtle">— Sara</div></div></div>
            </div>
        </div>
    </section>

<!-- FAQ -->
<section class="section">
        <div class="container">
    <h2 class="mb-4"><?= __('FAQ') ?> ❓</h2>
    <div class="accordion" id="faq">
      <div class="accordion-item">
        <h2 class="accordion-header" id="q1"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a1"><?= __('Is Voteapp free?') ?></button></h2>
        <div id="a1" class="accordion-collapse collapse" data-bs-parent="#faq"><div class="accordion-body subtle">Yes. You can register, post ideas, react, and comment for free.</div></div>
            </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="q2"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a2"><?= __('Do I keep ownership of my ideas?') ?></button></h2>
        <div id="a2" class="accordion-collapse collapse" data-bs-parent="#faq"><div class="accordion-body subtle"><?= __('Absolutely. You own your content. Public ideas are visible to the community.') ?></div></div>
                        </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="q3"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a3"><?= __('How does trending work?') ?></button></h2>
        <div id="a3" class="accordion-collapse collapse" data-bs-parent="#faq"><div class="accordion-body subtle"><?= __('A mix of votes, comments, views, and recency — designed to surface quality.') ?></div></div>
                        </div>
                    </div>
                </div>
</section>

<!-- CTA -->
<section class="section text-center">
  <div class="container">
    <div class="p-5 rounded-3xl shadow-soft" style="background:linear-gradient(90deg, rgba(255,215,0,.15), rgba(255,215,0,.05));border:1px solid var(--border)">
      <h2 class="mb-2"><?= __('Ready to launch your idea?') ?> 🚀</h2>
      <p class="subtle mb-4"><?= __('Join thousands of makers using Voteapp to validate, build, and grow.') ?></p>
      <?php if (is_logged_in()): ?>
        <a class="btn btn-gold" href="dashboard.php"><i class="bi bi-rocket-takeoff me-2"></i><?= __('Open dashboard') ?></a>
      <?php else: ?>
        <a class="btn btn-gold" href="register.php"><i class="bi bi-person-plus-fill me-2"></i><?= __('Create your account') ?></a>
      <?php endif; ?>
            </div>
        </div>
    </section>

<footer class="footer py-4 text-center">
  <div class="container small">© <?= date('Y') ?> IdeaVote. <?= __('Made with love by makers.') ?> <a class="ms-2" href="contact.php"><?= __('Contact') ?></a></div>
    </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme.js"></script>
    <script>
// Animated counters when visible
function animateValue(el, end, duration){
  const start = 0; const range = end - start; const startTime = performance.now();
  function step(now){
    const progress = Math.min((now - startTime) / duration, 1);
    el.textContent = Math.floor(start + range * progress).toLocaleString();
    if(progress < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

const observer = new IntersectionObserver((entries)=>{
  entries.forEach(e=>{
    if(e.isIntersecting){
      animateValue(document.getElementById('statIdeas'), <?= (int)$idea_count ?>, 1200);
      animateValue(document.getElementById('statUsers'), <?= (int)$user_count ?>, 1400);
      animateValue(document.getElementById('statVotes'), <?= (int)$vote_count ?>, 1600);
      observer.disconnect();
    }
  });
});
observer.observe(document.querySelector('.hero-cta'));
    </script>
</body>
</html>