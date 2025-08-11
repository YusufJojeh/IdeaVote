<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

// Verify webhook signature (basic HMAC)
$webhookSecret = $_ENV['WEBHOOK_SECRET'] ?? '';
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
$payload = file_get_contents('php://input');

if ($webhookSecret && $signature) {
    $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
    if (!hash_equals($expectedSignature, $signature)) {
        http_response_code(401);
        echo json_encode(['error' => 'invalid_signature']);
        exit;
    }
}

// Parse JSON payload
$data = json_decode($payload, true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_json']);
    exit;
}

$event = $data['event'] ?? '';
$payload = $data['payload'] ?? [];

try {
    switch ($event) {
        case 'idea.created':
            // Webhook for new idea creation
            $ideaId = $payload['idea_id'] ?? 0;
            if ($ideaId > 0) {
                // Get idea details
                $sql = "SELECT i.*, u.username, c.name as category_name 
                        FROM ideas i 
                        JOIN users u ON i.user_id = u.id 
                        LEFT JOIN categories c ON i.category_id = c.id 
                        WHERE i.id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, 'i', $ideaId);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $idea = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
                
                if ($idea) {
                    echo json_encode([
                        'success' => true,
                        'event' => $event,
                        'data' => [
                            'idea_id' => $idea['id'],
                            'title' => $idea['title'],
                            'description' => $idea['description'],
                            'author' => $idea['username'],
                            'category' => $idea['category_name'],
                            'created_at' => $idea['created_at'],
                            'url' => "http://{$_SERVER['HTTP_HOST']}/idea.php?id={$idea['id']}"
                        ]
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'idea_not_found']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'invalid_idea_id']);
            }
            break;
            
        case 'vote.created':
            // Webhook for new vote
            $voteId = $payload['vote_id'] ?? 0;
            if ($voteId > 0) {
                $sql = "SELECT v.*, i.title as idea_title, u.username 
                        FROM votes v 
                        JOIN ideas i ON v.idea_id = i.id 
                        JOIN users u ON v.user_id = u.id 
                        WHERE v.id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, 'i', $voteId);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $vote = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
                
                if ($vote) {
                    echo json_encode([
                        'success' => true,
                        'event' => $event,
                        'data' => [
                            'vote_id' => $vote['id'],
                            'idea_id' => $vote['idea_id'],
                            'idea_title' => $vote['idea_title'],
                            'voter' => $vote['username'],
                            'vote_type' => $vote['vote_type'],
                            'created_at' => $vote['created_at']
                        ]
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'vote_not_found']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'invalid_vote_id']);
            }
            break;
            
        case 'comment.created':
            // Webhook for new comment
            $commentId = $payload['comment_id'] ?? 0;
            if ($commentId > 0) {
                $sql = "SELECT c.*, i.title as idea_title, u.username 
                        FROM comments c 
                        JOIN ideas i ON c.idea_id = i.id 
                        JOIN users u ON c.user_id = u.id 
                        WHERE c.id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, 'i', $commentId);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $comment = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
                
                if ($comment) {
                    echo json_encode([
                        'success' => true,
                        'event' => $event,
                        'data' => [
                            'comment_id' => $comment['id'],
                            'idea_id' => $comment['idea_id'],
                            'idea_title' => $comment['idea_title'],
                            'commenter' => $comment['username'],
                            'content' => $comment['content'],
                            'created_at' => $comment['created_at']
                        ]
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'comment_not_found']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'invalid_comment_id']);
            }
            break;
            
        case 'user.registered':
            // Webhook for new user registration
            $userId = $payload['user_id'] ?? 0;
            if ($userId > 0) {
                $sql = "SELECT id, username, email, created_at FROM users WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, 'i', $userId);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
                
                if ($user) {
                    echo json_encode([
                        'success' => true,
                        'event' => $event,
                        'data' => [
                            'user_id' => $user['id'],
                            'username' => $user['username'],
                            'email' => $user['email'],
                            'registered_at' => $user['created_at']
                        ]
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'user_not_found']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'invalid_user_id']);
            }
            break;
            
        case 'stats.daily':
            // Daily statistics webhook
            $date = $payload['date'] ?? date('Y-m-d');
            
            // Get daily stats
            $sql = "SELECT 
                        COUNT(DISTINCT i.id) as new_ideas,
                        COUNT(DISTINCT v.id) as new_votes,
                        COUNT(DISTINCT c.id) as new_comments,
                        COUNT(DISTINCT u.id) as new_users
                    FROM ideas i 
                    LEFT JOIN votes v ON DATE(v.created_at) = ?
                    LEFT JOIN comments c ON DATE(c.created_at) = ?
                    LEFT JOIN users u ON DATE(u.created_at) = ?
                    WHERE DATE(i.created_at) = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ssss', $date, $date, $date, $date);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $stats = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
            echo json_encode([
                'success' => true,
                'event' => $event,
                'data' => [
                    'date' => $date,
                    'new_ideas' => (int) $stats['new_ideas'],
                    'new_votes' => (int) $stats['new_votes'],
                    'new_comments' => (int) $stats['new_comments'],
                    'new_users' => (int) $stats['new_users']
                ]
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'unknown_event']);
    }
} catch (Exception $e) {
    error_log("Webhook error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'server_error']);
}
