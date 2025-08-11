<?php
session_start();
include '../includes/config.php';
include '../includes/db.php';
include '../includes/auth.php';
include '../includes/notifications.php';
include '../includes/i18n.php';

if (!is_logged_in()) {
    http_response_code(401);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Unauthorized';
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
                echo $success ? 'success' : 'error';
            } else {
                echo 'invalid_id';
            }
            break;
            
        case 'mark_all_read':
            $success = mark_all_notifications_read($userId);
            echo $success ? 'success' : 'error';
            break;
            
        default:
            http_response_code(400);
            echo 'invalid_action';
    }
    exit;
}

// GET request - return notifications list
header('Content-Type: application/json; charset=utf-8');

$limit = intval($_GET['limit'] ?? 10);
$offset = intval($_GET['offset'] ?? 0);
$unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';

$notifications = get_notifications($userId, $limit, $offset, $unreadOnly);
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
        'time_ago' => time_ago($notification['created_at']),
        'data' => $notification['data']
    ];
}

echo json_encode([
    'notifications' => $formattedNotifications,
    'unread_count' => $unreadCount,
    'has_more' => count($notifications) === $limit
]);

/**
 * Convert timestamp to "time ago" format
 */
function time_ago($timestamp) {
    $time = strtotime($timestamp);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return __('just_now');
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return __('minutes_ago', ['minutes' => $minutes]);
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return __('hours_ago', ['hours' => $hours]);
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return __('days_ago', ['days' => $days]);
    } else {
        return format_date($timestamp, 'M j');
    }
}
?>
