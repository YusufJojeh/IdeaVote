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
    $action = $_POST['action'] ?? '';

    if ($ideaId <= 0) {
        http_response_code(400);
        echo 'Invalid idea ID';
        exit;
    }

    // Verify idea exists
    $sql = "SELECT id FROM ideas WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ideaId]);
    
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo 'Idea not found';
        exit;
    }

    if ($action === 'add') {
        // Check if already bookmarked
        $sql = "SELECT id FROM bookmarks WHERE user_id = ? AND idea_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $ideaId]);
        
        if ($stmt->rowCount() > 0) {
            echo 'already_bookmarked';
            exit;
        }

        // Add bookmark
        $sql = "INSERT INTO bookmarks (user_id, idea_id, created_at) VALUES (?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$userId, $ideaId]);
        
        if ($success) {
            // Update bookmark count in ideas table
            $sql = "UPDATE ideas SET bookmarks_count = (SELECT COUNT(*) FROM bookmarks WHERE idea_id = ?) WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ideaId, $ideaId]);
            
            echo 'bookmarked';
        } else {
            http_response_code(500);
            echo 'Failed to bookmark';
        }
    } elseif ($action === 'remove') {
        // Remove bookmark
        $sql = "DELETE FROM bookmarks WHERE user_id = ? AND idea_id = ?";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$userId, $ideaId]);
        
        if ($success) {
            // Update bookmark count in ideas table
            $sql = "UPDATE ideas SET bookmarks_count = (SELECT COUNT(*) FROM bookmarks WHERE idea_id = ?) WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ideaId, $ideaId]);
            
            echo 'removed';
        } else {
            http_response_code(500);
            echo 'Failed to remove bookmark';
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
