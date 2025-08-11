<?php
require_once 'db.php';

function escape($str) {
    // Only escape for HTML output; input should be handled with prepared statements
    return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_username($username) {
    return preg_match('/^[A-Za-z0-9_\x{0600}-\x{06FF}]{3,30}$/u', $username);
}

function get_vote_counts($idea_id) {
    global $pdo;
    $likes = 0; $dislikes = 0;
    $sql = "SELECT vote_type, COUNT(*) as count FROM votes WHERE idea_id=? GROUP BY vote_type";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idea_id]);
    while ($row = $stmt->fetch()) {
        if ($row['vote_type'] == 'like') $likes = $row['count'];
        if ($row['vote_type'] == 'dislike') $dislikes = $row['count'];
    }
    return ['like' => $likes, 'dislike' => $dislikes];
}

function get_category_name($cat_id, $lang = 'ar') {
    global $pdo;
    $col = $lang == 'en' ? 'name_en' : 'name_ar';
    $sql = "SELECT $col FROM categories WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cat_id]);
    $row = $stmt->fetch();
    return $row[$col] ?? '';
}

function get_client_ip_binary(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $bin = inet_pton($ip);
    return $bin !== false ? $bin : inet_pton('0.0.0.0');
}

function generate_secure_token(int $length = 32): string {
    return bin2hex(random_bytes($length));
}

function hash_token_for_storage(string $rawHexToken): string {
    // Store binary SHA-256 to save space; input is hex token shown to user
    return hash('sha256', $rawHexToken, true);
}

/**
 * Create a URL-friendly slug from a string
 */
function create_slug($string) {
    // Convert to lowercase
    $string = strtolower($string);
    
    // Replace non-alphanumeric characters with hyphens
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    
    // Replace multiple spaces or hyphens with single hyphen
    $string = preg_replace('/[\s-]+/', '-', $string);
    
    // Trim hyphens from beginning and end
    $string = trim($string, '-');
    
    // If empty, use 'idea'
    if (empty($string)) {
        $string = 'idea';
    }
    
    return $string;
}

/**
 * Trigger webhook event
 */
function trigger_webhook($event, $data) {
    global $pdo;
    
    // Store webhook event in database for processing
    $stmt = $pdo->prepare("INSERT INTO webhook_events (event_type, payload, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$event, json_encode($data)]);
    
    // In a production environment, you would also send this to external webhook endpoints
    // For now, we just store it in the database
}

/**
 * Get trending score for an idea
 */
function calculate_trending_score($idea_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            i.created_at,
            (SELECT COUNT(*) FROM votes WHERE idea_id = i.id AND vote_type = 'like') as likes,
            (SELECT COUNT(*) FROM votes WHERE idea_id = i.id AND vote_type = 'dislike') as dislikes,
            (SELECT COUNT(*) FROM comments WHERE idea_id = i.id) as comments,
            (SELECT COUNT(*) FROM reactions WHERE idea_id = i.id) as reactions,
            i.views_count
        FROM ideas i 
        WHERE i.id = ?
    ");
    $stmt->execute([$idea_id]);
    $data = $stmt->fetch();
    
    if (!$data) return 0;
    
    // Calculate time decay (newer ideas get higher scores)
    $age_hours = (time() - strtotime($data['created_at'])) / 3600;
    $time_decay = max(0.1, 1 - ($age_hours / 168)); // Decay over 1 week
    
    // Calculate engagement score
    $engagement = ($data['likes'] * 2) + ($data['comments'] * 3) + ($data['reactions'] * 1.5) + ($data['views_count'] * 0.1) - ($data['dislikes'] * 1);
    
    // Final trending score
    $trending_score = $engagement * $time_decay;
    
    // Update the trending score in database
    $stmt = $pdo->prepare("UPDATE ideas SET trending_score = ? WHERE id = ?");
    $stmt->execute([$trending_score, $idea_id]);
    
    return $trending_score;
}
?> 