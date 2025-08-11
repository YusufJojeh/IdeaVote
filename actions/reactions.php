<?php
session_start();
include '../includes/config.php';
include '../includes/db.php';
include '../includes/auth.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo 'Unauthorized';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = current_user_id();
    $ideaId = intval($_POST['idea_id'] ?? 0);
    $reactionType = $_POST['reaction_type'] ?? '';
    $action = $_POST['action'] ?? '';

    $validReactions = ['like', 'love', 'fire', 'laugh', 'wow', 'sad', 'angry'];

    if ($ideaId <= 0 || !in_array($reactionType, $validReactions) || !in_array($action, ['add', 'remove'])) {
        http_response_code(400);
        echo 'Invalid parameters';
        exit;
    }

    // Check if idea exists
    $sql = "SELECT id FROM ideas WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ideaId]);
    
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo 'Idea not found';
        exit;
    }

    if ($action === 'add') {
        // Check if reaction already exists
        $sql = "SELECT id FROM reactions WHERE user_id = ? AND idea_id = ? AND reaction_type = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $ideaId, $reactionType]);
        
        if ($stmt->rowCount() > 0) {
            echo 'reaction_exists';
            exit;
        }
        
        // Add reaction
        $sql = "INSERT INTO reactions (user_id, idea_id, reaction_type, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$userId, $ideaId, $reactionType]);
        
        if ($success) {
            echo 'added';
        } else {
            http_response_code(500);
            echo 'Failed to add reaction';
        }
    } elseif ($action === 'remove') {
        // Remove reaction
        $sql = "DELETE FROM reactions WHERE user_id = ? AND idea_id = ? AND reaction_type = ?";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$userId, $ideaId, $reactionType]);
        
        if ($success) {
            echo 'removed';
        } else {
            http_response_code(500);
            echo 'Failed to remove reaction';
        }
    } else {
        http_response_code(400);
        echo 'Invalid action';
    }
} else {
    http_response_code(405);
    echo 'Method not allowed';
}
?>
