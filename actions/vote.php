<?php
session_start();
include '../includes/config.php';
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/auth.php';

header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

if (!is_logged_in()) {
    http_response_code(401);
    echo 'Unauthorized';
    exit;
}

$userId = current_user_id();
$ideaId = isset($_POST['idea_id']) ? intval($_POST['idea_id']) : 0;
$voteType = ($_POST['vote_type'] ?? 'like') === 'dislike' ? 'dislike' : 'like';

if ($ideaId <= 0) {
    http_response_code(400);
    echo 'Invalid idea.';
    exit;
}

// Toggle/switch logic
// 1) Check if vote exists
$sql = "SELECT id, vote_type FROM votes WHERE user_id=? AND idea_id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId, $ideaId]);
$existing = $stmt->fetch();

$message = '';
if ($existing) {
    if ($existing['vote_type'] === $voteType) {
        // Toggle off
        $stmt = $pdo->prepare("DELETE FROM votes WHERE id=?");
        $stmt->execute([$existing['id']]);
        $message = 'vote_removed';
    } else {
        // Switch
        $stmt = $pdo->prepare("UPDATE votes SET vote_type=? WHERE id=?");
        $stmt->execute([$voteType, $existing['id']]);
        $message = 'vote_switched';
    }
} else {
    $stmt = $pdo->prepare("INSERT INTO votes (user_id, idea_id, vote_type, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$userId, $ideaId, $voteType]);
    $message = 'vote_added';
}

// Update ideas.votes_count (simple like count)
$stmt = $pdo->prepare("UPDATE ideas SET votes_count=(SELECT COUNT(*) FROM votes WHERE idea_id=? AND vote_type='like') WHERE id=?");
$stmt->execute([$ideaId, $ideaId]);

// Redirect back if not AJAX
$ref = $_SERVER['HTTP_REFERER'] ?? ('/idea.php?id=' . $ideaId);
header('Location: ' . $ref);
exit;
?>


