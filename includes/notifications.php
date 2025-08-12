<?php
/**
 * IdeaVote - Notifications System
 * Handles notification creation and management
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Create a new notification
 * 
 * @param int $user_id User ID to notify
 * @param string $type Notification type
 * @param string $title Notification title
 * @param string $message Notification message
 * @param array $data Additional data (optional)
 * @return bool Success status
 */
function create_notification($user_id, $type, $title, $message, $data = []) {
    global $pdo;
    
    try {
        $json_data = !empty($data) ? json_encode($data) : null;
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, data) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $type, $title, $message, $json_data]);
        return true;
    } catch (Exception $e) {
        error_log('Create notification failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get unread notifications count for a user
 * 
 * @param int $userId User ID
 * @return int Count of unread notifications
 */
function get_unread_notifications_count($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return (int)$result['count'];
    } catch (Exception $e) {
        error_log('Get unread notification count failed: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Get recent notifications for a user
 * 
 * @param int $user_id User ID
 * @param int $limit Maximum number of notifications to return
 * @param int $offset Offset for pagination
 * @param bool $unread_only Only return unread notifications
 * @return array Array of notifications
 */
function get_recent_notifications($user_id, $limit = 20, $offset = 0, $unread_only = false) {
    global $pdo;
    
    try {
        $where_clause = $unread_only ? "AND is_read = 0" : "";
        $stmt = $pdo->prepare(
            "SELECT * FROM notifications 
             WHERE user_id = ? {$where_clause} 
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$user_id, $limit, $offset]);
    $notifications = [];
    
        while ($row = $stmt->fetch()) {
            $notifications[] = [
                'id' => $row['id'],
                'type' => $row['type'],
                'title' => $row['title'],
                'message' => $row['message'],
                'is_read' => (bool)$row['is_read'],
                'created_at' => $row['created_at'],
                'time_ago' => format_date_time($row['created_at']),
                'data' => !empty($row['data']) ? json_decode($row['data'], true) : null
            ];
        }
        
    return $notifications;
    } catch (Exception $e) {
        error_log('Get recent notifications failed: ' . $e->getMessage());
        return [];
    }
}

/**
 * Mark notification as read
 * 
 * @param int $notification_id Notification ID
 * @param int $user_id User ID (for security check)
 * @return bool Success status
 */
function mark_notification_read($notification_id, $user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$notification_id, $user_id]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        error_log('Mark notification read failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Mark all notifications as read for a user
 * 
 * @param int $user_id User ID
 * @return bool Success status
 */
function mark_all_notifications_read($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return true;
    } catch (Exception $e) {
        error_log('Mark all notifications read failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Delete old notifications (cleanup)
 * 
 * @param int $days_old Delete notifications older than this many days
 * @return int Number of deleted notifications
 */
function cleanup_old_notifications($days_old = 30) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$days_old]);
        return $stmt->rowCount();
    } catch (Exception $e) {
        error_log('Cleanup old notifications failed: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Create a notification for a vote
 * 
 * @param int $recipient_id User to notify
 * @param int $actor_id User who performed the action
 * @param int $idea_id Related idea ID
 * @param string $vote_type 'like' or 'dislike'
 */
function notify_vote($recipient_id, $actor_id, $idea_id, $vote_type) {
    global $pdo;
    
    if ($recipient_id == $actor_id) {
        return; // Don't notify for own actions
    }
    
    try {
        // Get actor username
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$actor_id]);
        $actor = $stmt->fetch();
        $actor_name = $actor ? $actor['username'] : 'Someone';
    
    // Get idea title
        $stmt = $pdo->prepare("SELECT title FROM ideas WHERE id = ?");
        $stmt->execute([$idea_id]);
        $idea = $stmt->fetch();
        $idea_title = $idea ? $idea['title'] : 'your idea';
        
        // Create notification
        $action = $vote_type === 'like' ? 'liked' : 'disliked';
        $title = sprintf('%s %s your idea', $actor_name, $action);
        $message = sprintf('%s %s "%s"', $actor_name, $action, substr($idea_title, 0, 50) . (strlen($idea_title) > 50 ? '...' : ''));
        
        $data = json_encode([
            'actor_id' => $actor_id,
            'idea_id' => $idea_id,
            'vote_type' => $vote_type
        ]);
        
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, data) VALUES (?, 'vote', ?, ?, ?)");
        $stmt->execute([$recipient_id, $title, $message, $data]);
    } catch (Exception $e) {
        // Log error but don't interrupt flow
        error_log('Notification creation failed: ' . $e->getMessage());
    }
}

/**
 * Create a notification for a comment
 * 
 * @param int $recipient_id User to notify
 * @param int $actor_id User who performed the action
 * @param int $idea_id Related idea ID
 * @param string $comment Comment text
 */
function notify_comment($recipient_id, $actor_id, $idea_id, $comment) {
    global $pdo;
    
    if ($recipient_id == $actor_id) {
        return; // Don't notify for own actions
    }
    
    try {
        // Get actor username
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$actor_id]);
        $actor = $stmt->fetch();
        $actor_name = $actor ? $actor['username'] : 'Someone';
        
        // Get idea title
        $stmt = $pdo->prepare("SELECT title FROM ideas WHERE id = ?");
        $stmt->execute([$idea_id]);
        $idea = $stmt->fetch();
        $idea_title = $idea ? $idea['title'] : 'your idea';
        
        // Create notification
        $title = sprintf('%s commented on your idea', $actor_name);
        $message = sprintf('%s commented on "%s"', $actor_name, substr($idea_title, 0, 50) . (strlen($idea_title) > 50 ? '...' : ''));
        
        $data = json_encode([
            'actor_id' => $actor_id,
            'idea_id' => $idea_id,
            'comment_preview' => substr($comment, 0, 100)
        ]);
        
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, data) VALUES (?, 'comment', ?, ?, ?)");
        $stmt->execute([$recipient_id, $title, $message, $data]);
    } catch (Exception $e) {
        // Log error but don't interrupt flow
        error_log('Notification creation failed: ' . $e->getMessage());
    }
}

/**
 * Create a notification for a follow
 * 
 * @param int $recipient_id User to notify
 * @param int $actor_id User who performed the action
 */
function notify_follow($recipient_id, $actor_id) {
    global $pdo;
    
    if ($recipient_id == $actor_id) {
        return; // Don't notify for own actions
    }
    
    try {
        // Get actor username
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$actor_id]);
        $actor = $stmt->fetch();
        $actor_name = $actor ? $actor['username'] : 'Someone';
        
        // Create notification
        $title = sprintf('%s started following you', $actor_name);
        $message = sprintf('%s is now following you', $actor_name);
        
        $data = json_encode([
            'actor_id' => $actor_id
        ]);
        
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, data) VALUES (?, 'follow', ?, ?, ?)");
        $stmt->execute([$recipient_id, $title, $message, $data]);
    } catch (Exception $e) {
        // Log error but don't interrupt flow
        error_log('Notification creation failed: ' . $e->getMessage());
    }
}

/**
 * Create a notification for a bookmark
 * 
 * @param int $recipient_id User to notify
 * @param int $actor_id User who performed the action
 * @param int $idea_id Related idea ID
 */
function notify_bookmark($recipient_id, $actor_id, $idea_id) {
    global $pdo;
    
    if ($recipient_id == $actor_id) {
        return; // Don't notify for own actions
    }
    
    try {
        // Get actor username
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$actor_id]);
        $actor = $stmt->fetch();
        $actor_name = $actor ? $actor['username'] : 'Someone';
        
        // Get idea title
        $stmt = $pdo->prepare("SELECT title FROM ideas WHERE id = ?");
        $stmt->execute([$idea_id]);
        $idea = $stmt->fetch();
        $idea_title = $idea ? $idea['title'] : 'your idea';
        
        // Create notification
        $title = sprintf('%s bookmarked your idea', $actor_name);
        $message = sprintf('%s bookmarked "%s"', $actor_name, substr($idea_title, 0, 50) . (strlen($idea_title) > 50 ? '...' : ''));
        
        $data = json_encode([
            'actor_id' => $actor_id,
            'idea_id' => $idea_id
        ]);
        
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, data) VALUES (?, 'bookmark', ?, ?, ?)");
        $stmt->execute([$recipient_id, $title, $message, $data]);
    } catch (Exception $e) {
        // Log error but don't interrupt flow
        error_log('Notification creation failed: ' . $e->getMessage());
    }
}

/**
 * Create a notification for a reaction
 * 
 * @param int $recipient_id User to notify
 * @param int $actor_id User who performed the action
 * @param int $idea_id Related idea ID
 * @param string $reaction_type Type of reaction
 */
function notify_reaction($recipient_id, $actor_id, $idea_id, $reaction_type = 'like') {
    global $pdo;
    
    if ($recipient_id == $actor_id) {
        return; // Don't notify for own actions
    }
    
    try {
        // Get actor username
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$actor_id]);
        $actor = $stmt->fetch();
        $actor_name = $actor ? $actor['username'] : 'Someone';
        
        // Get idea title
        $stmt = $pdo->prepare("SELECT title FROM ideas WHERE id = ?");
        $stmt->execute([$idea_id]);
        $idea = $stmt->fetch();
        $idea_title = $idea ? $idea['title'] : 'your idea';
        
        // Create notification
        $reaction_labels = [
            'like' => 'liked',
            'love' => 'loved',
            'fire' => 'fire-reacted to',
            'laugh' => 'laughed at',
            'wow' => 'was wowed by',
            'sad' => 'was saddened by',
            'angry' => 'was angered by',
            'rocket' => 'rocket-reacted to',
            'brain' => 'thought deeply about',
            'star' => 'starred'
        ];
        
        $action = $reaction_labels[$reaction_type] ?? 'reacted to';
        $title = sprintf('%s %s your idea', $actor_name, $action);
        $message = sprintf('%s %s "%s"', $actor_name, $action, substr($idea_title, 0, 50) . (strlen($idea_title) > 50 ? '...' : ''));
        
        $data = json_encode([
            'actor_id' => $actor_id,
            'idea_id' => $idea_id,
            'reaction_type' => $reaction_type
        ]);
        
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, data) VALUES (?, 'reaction', ?, ?, ?)");
        $stmt->execute([$recipient_id, $title, $message, $data]);
    } catch (Exception $e) {
        // Log error but don't interrupt flow
        error_log('Notification creation failed: ' . $e->getMessage());
    }
}