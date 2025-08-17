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
    $reactionType = $_POST['reaction'] ?? $_POST['reaction_type'] ?? '';
    $action = $_POST['action'] ?? '';

    $validReactions = ['👍', '❤️', '🎉', '🔥', '👏', '🤔'];

    // Debug logging
    error_log("Reaction request - User: $userId, Idea: $ideaId, Reaction: '$reactionType', Action: $action");

    if ($ideaId <= 0) {
        http_response_code(400);
        echo 'Invalid idea ID';
        exit;
    }

    if (!in_array($reactionType, $validReactions)) {
        http_response_code(400);
        echo 'Invalid reaction type: ' . $reactionType;
        exit;
    }

    if (!in_array($action, ['add', 'remove'])) {
        http_response_code(400);
        echo 'Invalid action: ' . $action;
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
        try {
            // Check if user already has a reaction on this idea
            $sql = "SELECT reaction_type FROM reactions WHERE user_id = ? AND idea_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $ideaId]);
            $existingReaction = $stmt->fetch();
            
            if ($existingReaction) {
                if ($existingReaction['reaction_type'] === $reactionType) {
                    // Same reaction already exists
                    echo 'reaction_exists';
                    exit;
                } else {
                    // User is changing their reaction, update it
                    $sql = "UPDATE reactions SET reaction_type = ?, created_at = NOW() WHERE user_id = ? AND idea_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $success = $stmt->execute([$reactionType, $userId, $ideaId]);
                    
                    if ($success) {
                        echo 'success';
                    } else {
                        http_response_code(500);
                        echo 'Failed to update reaction';
                    }
                    exit;
                }
            }
            
            // Add new reaction
            $sql = "INSERT INTO reactions (user_id, idea_id, reaction_type, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$userId, $ideaId, $reactionType]);
            
            if ($success) {
                echo 'success';
            } else {
                http_response_code(500);
                echo 'Failed to add reaction';
            }
        } catch (PDOException $e) {
            // Log the error for debugging
            error_log("Reaction database error: " . $e->getMessage());
            http_response_code(500);
            echo 'Database error occurred';
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
