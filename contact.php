<?php
ob_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';
require_once 'includes/i18n.php';

// Initialize variables
$success = false;
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validate inputs
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    }
    
    // If no errors, process the form
    if (empty($errors)) {
        try {
            // Store the message in the database
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, message, created_at) VALUES (?, ?, NOW())");
            $sender_id = is_logged_in() ? current_user_id() : null;
            
            // Combine form data into a structured message
            $full_message = json_encode([
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message,
                'user_id' => $sender_id
            ]);
            
            $stmt->execute([$sender_id, $full_message]);
            
            // Set success flag
            $success = true;
            
            // Clear form data
            $name = $email = $subject = $message = '';
            
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

include 'includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="<?= current_language() ?>" dir="<?= lang_dir() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('Contact Us') ?> - IdeaVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <style>
        .contact-hero {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
            padding: 5rem 0;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }
        
        .contact-hero::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGZpbGw9IiNmZmZmZmYiIG9wYWNpdHk9IjAuMSIgY3g9IjEwIiBjeT0iMTAiIHI9IjIiLz48Y2lyY2xlIGZpbGw9IiNmZmZmZmYiIG9wYWNpdHk9IjAuMSIgY3g9IjUwIiBjeT0iMzAiIHI9IjQiLz48Y2lyY2xlIGZpbGw9IiNmZmZmZmYiIG9wYWNpdHk9IjAuMSIgY3g9IjgwIiBjeT0iMTUiIHI9IjMiLz48Y2lyY2xlIGZpbGw9IiNmZmZmZmYiIG9wYWNpdHk9IjAuMSIgY3g9IjMwIiBjeT0iNjAiIHI9IjMiLz48Y2lyY2xlIGZpbGw9IiNmZmZmZmYiIG9wYWNpdHk9IjAuMSIgY3g9IjcwIiBjeT0iODAiIHI9IjQiLz48Y2lyY2xlIGZpbGw9IiNmZmZmZmYiIG9wYWNpdHk9IjAuMSIgY3g9IjkwIiBjeT0iNTAiIHI9IjIiLz48L3N2Zz4=');
            opacity: 0.3;
        }
        
        .contact-hero h1 {
            color: var(--black);
            font-weight: 700;
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .contact-hero p {
            color: var(--black);
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .contact-form {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(var(--shadow-rgb), 0.1);
            border: 1px solid var(--border-color);
        }
        
        .contact-info {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(var(--shadow-rgb), 0.1);
            border: 1px solid var(--border-color);
            height: 100%;
        }
        
        .contact-info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .contact-info-item i {
            font-size: 1.5rem;
            color: var(--gold);
            margin-right: 1rem;
            margin-top: 0.25rem;
        }
        
        .contact-info-item .content h4 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
        
        .contact-info-item .content p {
            color: var(--text-muted);
            margin-bottom: 0;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--gold);
            color: var(--black);
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(var(--shadow-rgb), 0.2);
        }
    </style>
</head>
<body>
    <div class="contact-hero">
        <div class="container text-center position-relative" style="z-index: 1;">
            <h1><?= __('Get In Touch') ?></h1>
            <p><?= __('Have questions, feedback, or need assistance? We\'re here to help. Reach out to our team using the form below.') ?></p>
        </div>
    </div>
    
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="contact-form">
                    <h2 class="mb-4"><?= __('Send Us a Message') ?></h2>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <?= __('Thank you! Your message has been sent successfully. We\'ll get back to you soon.') ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" novalidate>
                        <?= csrf_field(); ?>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label"><?= __('Your Name') ?></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($name ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label"><?= __('Email Address') ?></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label"><?= __('Subject') ?></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-chat-left-text"></i></span>
                                <input type="text" class="form-control" id="subject" name="subject" required value="<?= htmlspecialchars($subject ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="form-label"><?= __('Message') ?></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-pencil"></i></span>
                                <textarea class="form-control" id="message" name="message" rows="6" required><?= htmlspecialchars($message ?? '') ?></textarea>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-gold py-2 px-4">
                            <i class="bi bi-send me-2"></i> <?= __('Send Message') ?>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-5 mb-4">
                <div class="contact-info">
                    <h2 class="mb-4"><?= __('Contact Information') ?></h2>
                    
                    <div class="contact-info-item">
                        <i class="bi bi-geo-alt"></i>
                        <div class="content">
                            <h4><?= __('Our Location') ?></h4>
                            <p>123 Innovation Street, Tech Valley<br>San Francisco, CA 94103</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <i class="bi bi-telephone"></i>
                        <div class="content">
                            <h4><?= __('Phone Number') ?></h4>
                            <p>+1 (555) 123-4567</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <i class="bi bi-envelope"></i>
                        <div class="content">
                            <h4><?= __('Email Address') ?></h4>
                            <p>contact@ideavote.com</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <i class="bi bi-clock"></i>
                        <div class="content">
                            <h4><?= __('Working Hours') ?></h4>
                            <p>Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 10:00 AM - 4:00 PM</p>
                        </div>
                    </div>
                    
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d50470.09854211439!2d-122.43913288156437!3d37.76400255272697!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80859a6d00690021%3A0x4a501367f076adff!2sSan%20Francisco%2C%20CA%2C%20USA!5e0!3m2!1sen!2s!4v1692604029276!5m2!1sen!2s" 
                            width="100%" 
                            height="450" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/theme.js"></script>
</body>
</html>