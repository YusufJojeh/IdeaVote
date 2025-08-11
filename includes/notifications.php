<?php
/**
 * Notifications helper
 * Handles creating, reading, and managing user notifications
 */

require_once __DIR__ . '/db.php';

/**
 * Create a new notification
 * 
 * @param int $userId User ID to notify
 * @param string $type Notification type
 * @param string $title Notification title
 * @param string $message Notification message
 * @param array $data Additional data (optional)
 * @return bool Success status
 */
function create_notification($userId, $type, $title, $message, $data = []) {
    global $conn;
    
    $sql = "INSERT INTO notifications (user_id, type, title, message, data) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        return false;
    }
    
    $jsonData = !empty($data) ? json_encode($data) : null;
    mysqli_stmt_bind_param($stmt, 'issss', $userId, $type, $title, $message, $jsonData);
    
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

/**
 * Get unread notifications count for a user
 * 
 * @param int $userId User ID
 * @return int Count of unread notifications
 */
function get_unread_notifications_count($userId) {
    global $conn;
    
    $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        return 0;
    }
    
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return (int) $row['count'];
}

/**
 * Get notifications for a user
 * 
 * @param int $userId User ID
 * @param int $limit Number of notifications to return
 * @param int $offset Offset for pagination
 * @param bool $unreadOnly Only return unread notifications
 * @return array Array of notifications
 */
function get_notifications($userId, $limit = 20, $offset = 0, $unreadOnly = false) {
    global $conn;
    
    $whereClause = $unreadOnly ? "AND is_read = 0" : "";
    $sql = "SELECT * FROM notifications WHERE user_id = ? {$whereClause} ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        return [];
    }
    
    mysqli_stmt_bind_param($stmt, 'iii', $userId, $limit, $offset);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $notifications = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $row['data'] = $row['data'] ? json_decode($row['data'], true) : null;
        $notifications[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $notifications;
}

/**
 * Mark notification as read
 * 
 * @param int $notificationId Notification ID
 * @param int $userId User ID (for security)
 * @return bool Success status
 */
function mark_notification_read($notificationId, $userId) {
    global $conn;
    
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, 'ii', $notificationId, $userId);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

/**
 * Mark all notifications as read for a user
 * 
 * @param int $userId User ID
 * @return bool Success status
 */
function mark_all_notifications_read($userId) {
    global $conn;
    
    $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

/**
 * Delete old notifications (cleanup)
 * 
 * @param int $daysOld Delete notifications older than this many days
 * @return int Number of deleted notifications
 */
function cleanup_old_notifications($daysOld = 30) {
    global $conn;
    
    $sql = "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        return 0;
    }
    
    mysqli_stmt_bind_param($stmt, 'i', $daysOld);
    mysqli_stmt_execute($stmt);
    
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
    return $affected;
}

/**
 * Create notification for new vote
 * 
 * @param int $ideaId Idea ID
 * @param int $voterId Voter ID
 * @param int $ideaOwnerId Idea owner ID
 * @param string $voteType Vote type (up/down)
 */
function notify_vote($ideaId, $voterId, $ideaOwnerId, $voteType) {
    if ($voterId === $ideaOwnerId) {
        return; // Don't notify self
    }
    
    global $conn;
    
    // Get idea title
    $sql = "SELECT title FROM ideas WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $ideaId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $idea = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$idea) {
        return;
    }
    
    // Get voter name
    $sql = "SELECT username FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $voterId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $voter = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$voter) {
        return;
    }
    
    $voteText = $voteType === 'up' ? 'upvoted' : 'downvoted';
    $title = "New vote on your idea";
    $message = "{$voter['username']} {$voteText} your idea \"{$idea['title']}\"";
    
    create_notification($ideaOwnerId, 'vote', $title, $message, [
        'idea_id' => $ideaId,
        'voter_id' => $voterId,
        'vote_type' => $voteType
    ]);
}

/**
 * Create notification for new comment
 * 
 * @param int $ideaId Idea ID
 * @param int $commenterId Commenter ID
 * @param int $ideaOwnerId Idea owner ID
 * @param string $commentText Comment text (truncated)
 */
function notify_comment($ideaId, $commenterId, $ideaOwnerId, $commentText) {
    if ($commenterId === $ideaOwnerId) {
        return; // Don't notify self
    }
    
    global $conn;
    
    // Get idea title
    $sql = "SELECT title FROM ideas WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $ideaId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $idea = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$idea) {
        return;
    }
    
    // Get commenter name
    $sql = "SELECT username FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $commenterId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $commenter = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$commenter) {
        return;
    }
    
    $truncatedComment = strlen($commentText) > 50 ? substr($commentText, 0, 50) . '...' : $commentText;
    $title = "New comment on your idea";
    $message = "{$commenter['username']} commented on your idea \"{$idea['title']}\": \"{$truncatedComment}\"";
    
    create_notification($ideaOwnerId, 'comment', $title, $message, [
        'idea_id' => $ideaId,
        'commenter_id' => $commenterId,
        'comment_preview' => $truncatedComment
    ]);
}

/**
 * Create notification for new follow
 * 
 * @param int $followerId Follower ID
 * @param int $followingId User being followed ID
 */
function notify_follow($followerId, $followingId) {
    global $conn;
    
    // Get follower name
    $sql = "SELECT username FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $followerId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $follower = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$follower) {
        return;
    }
    
    $title = "New follower";
    $message = "{$follower['username']} started following you";
    
    create_notification($followingId, 'follow', $title, $message, [
        'follower_id' => $followerId
    ]);
}
