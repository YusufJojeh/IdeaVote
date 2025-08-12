<?php
// Prevent any output before JSON response
ob_start();

session_start();
include '../includes/config.php';
include '../includes/db.php';
include '../includes/auth.php';
include '../includes/notifications.php';
include '../includes/i18n.php';

// Clear any output buffer
ob_clean();

// Set proper headers
header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'mark_read':
            $notificationId = intval($_POST['notification_id'] ?? 0);
            if ($notificationId > 0) {
                $success = mark_notification_read($notificationId, $userId);
                echo json_encode(['success' => $success]);
            } else {
                echo json_encode(['error' => 'invalid_id']);
            }
            break;
            
        case 'mark_all_read':
            $success = mark_all_notifications_read($userId);
            echo json_encode(['success' => $success]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'invalid_action']);
    }
    exit;
}

// GET request - return notifications list
try {
    $limit = intval($_GET['limit'] ?? 10);
    $offset = intval($_GET['offset'] ?? 0);
    $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';

    // Get notifications
    $notifications = get_recent_notifications($userId, $limit, $offset, $unreadOnly);
    $unreadCount = get_unread_notifications_count($userId);

    // Format notifications for display
    $formattedNotifications = [];
    foreach ($notifications as $notification) {
        $formattedNotifications[] = [
            'id' => $notification['id'],
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'is_read' => (bool) $notification['is_read'],
            'created_at' => $notification['created_at'],
            'time_ago' => format_time_ago($notification['created_at']),
            'data' => $notification['data']
        ];
    }

    echo json_encode([
        'success' => true,
        'notifications' => $formattedNotifications,
        'unread_count' => $unreadCount,
        'has_more' => count($notifications) === $limit
    ]);

} catch (Exception $e) {
    error_log('Notifications error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load notifications',
        'notifications' => [],
        'unread_count' => 0
    ]);
}

/**
 * Convert timestamp to "time ago" format
 */
function format_time_ago($timestamp) {
    $time = strtotime($timestamp);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j', $time);
    }
}
?>
