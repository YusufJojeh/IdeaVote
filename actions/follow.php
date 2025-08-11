<?php
session_start();
include '../includes/config.php';
include '../includes/db.php';
include '../includes/auth.php';
include '../includes/notifications.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo 'Unauthorized';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = current_user_id();
    $targetType = $_POST['type'] ?? '';
    $targetId = intval($_POST['target_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if (!in_array($targetType, ['user', 'category']) || $targetId <= 0 || !in_array($action, ['follow', 'unfollow'])) {
        http_response_code(400);
        echo 'Invalid parameters';
        exit;
    }

    // Prevent self-following
    if ($targetType === 'user' && $targetId === $userId) {
        http_response_code(400);
        echo 'Cannot follow yourself';
        exit;
    }

    if ($targetType === 'user') {
        // Check if target user exists
        $sql = "SELECT id FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$targetId]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo 'User not found';
            exit;
        }
        
        if ($action === 'follow') {
            // Check if already following
            $sql = "SELECT id FROM follows WHERE follower_id = ? AND following_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $targetId]);
            
            if ($stmt->rowCount() > 0) {
                echo 'already_following';
                exit;
            }
            
            // Create follow relationship
            $sql = "INSERT INTO follows (follower_id, following_id, created_at) VALUES (?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$userId, $targetId]);
            
            if ($success) {
                // Send notification
                notify_follow($userId, $targetId);
                echo 'followed';
            } else {
                http_response_code(500);
                echo 'Failed to follow';
            }
        } elseif ($action === 'unfollow') {
            // Unfollow
            $sql = "DELETE FROM follows WHERE follower_id = ? AND following_id = ?";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$userId, $targetId]);
            
            if ($success) {
                echo 'unfollowed';
            } else {
                http_response_code(500);
                echo 'Failed to unfollow';
            }
        }
    } else {
        // Category follow
        // Check if target category exists
        $sql = "SELECT id FROM categories WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$targetId]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo 'Category not found';
            exit;
        }
        
        if ($action === 'follow') {
            // Check if already following
            $sql = "SELECT id FROM category_follows WHERE user_id = ? AND category_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $targetId]);
            
            if ($stmt->rowCount() > 0) {
                echo 'already_following';
                exit;
            }
            
            // Create category follow relationship
            $sql = "INSERT INTO category_follows (user_id, category_id, created_at) VALUES (?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$userId, $targetId]);
            
            if ($success) {
                echo 'followed';
            } else {
                http_response_code(500);
                echo 'Failed to follow category';
            }
        } elseif ($action === 'unfollow') {
            // Unfollow category
            $sql = "DELETE FROM category_follows WHERE user_id = ? AND category_id = ?";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$userId, $targetId]);
            
            if ($success) {
                echo 'unfollowed';
            } else {
                http_response_code(500);
                echo 'Failed to unfollow category';
            }
        }
    }
} else {
    http_response_code(405);
    echo 'Method not allowed';
}
?>
