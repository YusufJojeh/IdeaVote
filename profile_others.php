<?php
ob_start();
include 'includes/navbar.php';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($user_id <= 0) {
    echo '<div class="container py-5"><div class="alert alert-danger">User not found.</div></div>';
    exit();
}
// Fetch user info
$stmt = mysqli_prepare($conn, "SELECT username, email, bio, created_at, image_url FROM users WHERE id=?");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);
if (!$user) {
    echo '<div class="container py-5"><div class="alert alert-danger">User not found.</div></div>';
    exit();
}
// Fetch user's ideas
$ideas = [];
$stmt = mysqli_prepare($conn, "SELECT * FROM ideas WHERE user_id=? AND is_public=1 ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) {
    $ideas[] = $row;
}
mysqli_stmt_close($stmt);
// Handle chat/messages
$chat_feedback = '';
$logged_in = isset($_SESSION['user_id']);
$my_id = $logged_in ? $_SESSION['user_id'] : 0;
// Only allow chat if logged in and not viewing own profile
$can_chat = $logged_in && $my_id != $user_id;
// (Removed auto-creation of messages table)
if ($can_chat && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $msg = trim($_POST['message']);
    if (strlen($msg) < 1) {
        $chat_feedback = '<div class="alert alert-danger">Message cannot be empty.</div>';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iis', $my_id, $user_id, $msg);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header('Location: profile_others.php?user_id=' . $user_id . '&msg=sent');
            exit();
        } else {
            $chat_feedback = '<div class="alert alert-danger">Failed to send message.</div>';
            mysqli_stmt_close($stmt);
        }
    }
}
if (isset($_GET['msg']) && $_GET['msg'] === 'sent') {
    $chat_feedback = '<div class="alert alert-success">Message sent!</div>';
}
// Fetch chat messages (show last 10) only if can_chat
$messages = [];
if ($can_chat) {
    $stmt = mysqli_prepare($conn, "SELECT m.*, u.username FROM messages m JOIN users u ON m.sender_id=u.id WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY m.created_at DESC LIMIT 10");
    mysqli_stmt_bind_param($stmt, 'iiii', $my_id, $user_id, $user_id, $my_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $messages[] = $row;
    }
    mysqli_stmt_close($stmt);
    $messages = array_reverse($messages); // show oldest first
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['username']) ?> - Profile - IdeaVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Inter', 'Segoe UI', Arial, sans-serif; }
        .profile-glass { background: rgba(255,255,255,0.97); box-shadow: 0 8px 32px 0 rgba(24,24,24,0.08); border-radius: 32px; border: 1.5px solid #eee; padding: 2.5rem 2rem; }
        .icon-gold { color: #FFD700; }
        .idea-card { background: #fff; border-radius: 18px; box-shadow: 0 2px 12px rgba(24,24,24,0.06); border: 1.5px solid #eee; margin-bottom: 1.2rem; }
        .idea-card .card-title { color: #181818; }
        .idea-card .badge { background: #FFD700; color: #181818; }
        .idea-card .text-muted { color: #888 !important; }
        .gold-gradient { background: linear-gradient(90deg, #FFD700 0%, #FFEF8E 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; color: #FFD700; }
        .chat-glass { background: rgba(255,255,255,0.98); border-radius: 20px; box-shadow: 0 2px 12px rgba(24,24,24,0.06); border: 1.5px solid #eee; margin-bottom: 1.2rem; padding: 1.5rem; }
        .chat-msg { margin-bottom: 1rem; }
        .chat-msg.me { text-align: right; }
        .chat-msg .msg-bubble { display: inline-block; padding: 0.5em 1em; border-radius: 16px; background: #FFD700; color: #181818; }
        .chat-msg.me .msg-bubble { background: #FFEF8E; color: #181818; }
        .chat-username { font-weight: 600; color: #FFD700; }
        .chat-date { color: #888; font-size: 0.95em; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="profile-glass shadow mb-4">
                <h3 class="mb-3 gold-gradient"><i class="bi bi-person-circle icon-gold"></i> <?= htmlspecialchars($user['username']) ?>'s Profile</h3>
                <img src="<?= htmlspecialchars($user['image_url'] ?? 'https://api.dicebear.com/6.x/initials/svg?seed=' . urlencode($user['username'])) ?>" alt="avatar" class="rounded-circle mb-3" width="96" height="96">
                <ul class="list-unstyled mb-0">
                    <li><strong>Job / Education:</strong> <?= htmlspecialchars($user['bio']) ?></li>
                    <li><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></li>
                    <li><strong>Member since:</strong> <?= htmlspecialchars($user['created_at']) ?></li>
                </ul>
            </div>
            <div class="profile-glass shadow mt-4">
                <h4 class="mb-3 gold-gradient"><i class="bi bi-lightbulb icon-gold"></i> Public Ideas</h4>
                <?php if (empty($ideas)): ?>
                    <div class="alert alert-info">No public ideas yet.</div>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($ideas as $idea): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center idea-card">
                                <a href="idea.php?id=<?= $idea['id'] ?>" class="fw-bold gold-gradient"> <?= htmlspecialchars($idea['title']) ?> </a>
                                <span class="badge"><i class="bi bi-hand-thumbs-up-fill icon-gold"></i> <?= get_vote_counts($idea['id'])['like'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="profile-glass shadow position-relative">
                <h4 class="mb-3 gold-gradient"><i class="bi bi-chat-dots icon-gold"></i> Chat / Messages</h4>
                <?php if ($can_chat): ?>
                <div class="chat-glass mb-3">
                  <?php if (empty($messages)): ?>
                    <div class="text-muted">No messages yet.</div>
                  <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                      <div class="chat-msg<?= ($msg['sender_id'] == $my_id) ? ' me' : '' ?>">
                        <span class="chat-username"><?= htmlspecialchars($msg['username']) ?></span>
                        <span class="msg-bubble"><?= htmlspecialchars($msg['message']) ?></span>
                        <div class="chat-date"> <?= htmlspecialchars($msg['created_at']) ?> </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
                <?= $chat_feedback ?>
                <form method="POST">
                  <div class="input-group">
                    <input type="text" name="message" class="form-control" placeholder="Type your message..." required>
                    <button class="btn btn-gold" type="submit" name="send_message">Send</button>
                  </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- Add Bootstrap JS for modal functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 