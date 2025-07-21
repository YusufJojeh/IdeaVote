<?php
ob_start();
include 'includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IdeaVote – The Ultimate Idea Voting Platform</title>
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
        }
        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            background: var(--offwhite);
            color: var(--black);
            min-height: 100vh;
        }
        .navbar-lux {
            background: #fff !important;
            box-shadow: 0 8px 32px 0 rgba(24,24,24,0.06);
            border-bottom: 1px solid #eee;
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
            background: linear-gradient(120deg, #fff 0%, #f8fafc 100%);
            color: var(--black);
            position: relative;
            overflow: hidden;
        }
        .hero-glass {
            background: rgba(255,255,255,0.85);
            box-shadow: 0 8px 32px 0 rgba(24,24,24,0.08);
            backdrop-filter: blur(14px);
            border-radius: 36px;
            border: 1.5px solid #eee;
            padding: 3.5rem 2.5rem;
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
        .animated-bg {
            position: absolute;
            top: -100px;
            right: -100px;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, var(--gold) 0%, #fff 80%);
            opacity: 0.10;
            filter: blur(60px);
            z-index: 0;
        }
        .section-title {
            font-weight: bold;
            font-size: 2.3rem;
            margin-bottom: 1.5rem;
            letter-spacing: 1px;
            color: var(--black);
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section position-relative">
        <div class="animated-bg"></div>
        <div class="container position-relative" style="z-index:2;">
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="hero-glass mx-auto text-center">
                        <h1 class="display-2 fw-bold mb-3 gold-gradient">Where Ideas Become Gold</h1>
                        <p class="lead mb-4" style="color: var(--gray);">Welcome to <b>IdeaVote</b> – the most inspiring, beautiful, and powerful platform for sharing, voting, and discovering world-changing ideas. Join a community where your creativity shines and every vote counts.</p>
                        <a href="register.php" class="btn btn-gold btn-lg cta-btn shadow">Get Started Free</a>
                        <div class="d-flex justify-content-center gap-4 mt-4">
                            <span class="feature-icon"><i class="bi bi-lightbulb"></i></span>
                            <span class="feature-icon"><i class="bi bi-stars"></i></span>
                            <span class="feature-icon"><i class="bi bi-people-fill"></i></span>
                            <span class="feature-icon"><i class="bi bi-hand-thumbs-up-fill"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-flex justify-content-center align-items-center">
                    <img src="https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=600&q=80" alt="Golden Ideas" class="img-fluid hero-img" loading="lazy">
                </div>
            </div>
        </div>
    </section>
    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="gold-gradient section-title">Why Choose IdeaVote?</span>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <span class="feature-icon"><i class="bi bi-lightbulb"></i></span>
                        <h5 class="fw-bold mb-2 gold-gradient">Share Brilliant Ideas</h5>
                        <p style="color: var(--gray);">Post your creative, project, or personal ideas and let the world see your brilliance. Every idea is a spark of gold.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <span class="feature-icon"><i class="bi bi-hand-thumbs-up"></i></span>
                        <h5 class="fw-bold mb-2 gold-gradient">Vote & Empower</h5>
                        <p style="color: var(--gray);">Like or dislike ideas, join lively discussions, and help the best ideas rise to the top. Your vote is golden.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <span class="feature-icon"><i class="bi bi-people"></i></span>
                        <h5 class="fw-bold mb-2 gold-gradient">Elite Community</h5>
                        <p style="color: var(--gray);">Connect with a vibrant, ambitious community, collaborate, and make your voice heard in a world of innovators.</p>
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
                        <div class="counter-label">Ideas Shared</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <span class="feature-icon"><i class="bi bi-people-fill"></i></span>
                        <div class="counter" id="usersCounter">0</div>
                        <div class="counter-label">Active Users</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <span class="feature-icon"><i class="bi bi-hand-thumbs-up-fill"></i></span>
                        <div class="counter" id="votesCounter">0</div>
                        <div class="counter-label">Votes Cast</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Testimonials Carousel -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="gold-gradient section-title">What Our Users Say</span>
            </div>
            <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="testimonial p-4 feature-card shadow-sm mx-auto" style="max-width:600px;">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" class="testimonial-avatar" alt="Alex">
                            <div class="mb-2"><i class="bi bi-quote fs-2 gold-gradient"></i></div>
                            <span class="gold-gradient">"A platform that truly values my ideas!"</span><br><span class="fw-bold" style="color: var(--black);">- Alex</span>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="testimonial p-4 feature-card shadow-sm mx-auto" style="max-width:600px;">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" class="testimonial-avatar" alt="Sarah">
                            <div class="mb-2"><i class="bi bi-quote fs-2 gold-gradient"></i></div>
                            <span class="gold-gradient">"The best place to get feedback and support!"</span><br><span class="fw-bold" style="color: var(--black);">- Sarah</span>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="testimonial p-4 feature-card shadow-sm mx-auto" style="max-width:600px;">
                            <img src="https://randomuser.me/api/portraits/men/54.jpg" class="testimonial-avatar" alt="John">
                            <div class="mb-2"><i class="bi bi-quote fs-2 gold-gradient"></i></div>
                            <span class="gold-gradient">"Inspiring, beautiful, and easy to use."</span><br><span class="fw-bold" style="color: var(--black);">- John</span>
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
            <h2 class="mb-3 fw-bold gold-gradient">Ready to turn your ideas into gold?</h2>
            <a href="register.php" class="btn btn-gold btn-lg px-5 py-3 fs-5">Join IdeaVote Now</a>
        </div>
    </section>
    <!-- Footer -->
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
    <script>
        // Animated counters (replace with AJAX for live data)
        document.addEventListener('DOMContentLoaded', function() {
            var ideas = new countUp.CountUp('ideasCounter', 123, {duration: 2});
            var users = new countUp.CountUp('usersCounter', 45, {duration: 2});
            var votes = new countUp.CountUp('votesCounter', 321, {duration: 2});
            ideas.start(); users.start(); votes.start();
        });
    </script>
</body>
</html>