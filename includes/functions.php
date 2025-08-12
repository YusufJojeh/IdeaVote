<?php
/**
 * General helper functions
 */

/**
 * Format a datetime string into a human-readable format
 * 
 * @param string $datetime Datetime string
 * @param bool $full Whether to show full date
 * @return string Formatted date
 */
function format_date_time($datetime, $full = false) {
    $timestamp = strtotime($datetime);
    $now = time();
    $diff = $now - $timestamp;
    
    // If full date is requested or date is older than 7 days
    if ($full || $diff > 604800) {
        return date('M j, Y', $timestamp);
    }
    
    // Within last 24 hours
    if ($diff < 86400) {
        $hours = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);
        
        if ($hours > 0) {
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } else if ($minutes > 0) {
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } else {
            return 'Just now';
        }
    }
    
    // Within last 7 days
    $days = floor($diff / 86400);
    return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
}

/**
 * Truncate a string to a specified length
 * 
 * @param string $string String to truncate
 * @param int $length Maximum length
 * @param string $etc String to append if truncated
 * @return string Truncated string
 */
function str_truncate($string, $length, $etc = '...') {
    if (mb_strlen($string) <= $length) {
        return $string;
    }
    
    return mb_substr($string, 0, $length) . $etc;
}

/**
 * HTML escape shorthand
 * 
 * @param string $string String to escape
 * @return string Escaped string
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if a user has voted on an idea
 * 
 * @param int $userId User ID
 * @param int $ideaId Idea ID
 * @return string|bool Vote type ('like', 'dislike') or false if no vote
 */
function has_user_voted($userId, $ideaId) {
    global $pdo;
    
    if (!$userId) {
        return false;
    }
    
    $stmt = $pdo->prepare("SELECT vote_type FROM votes WHERE user_id = ? AND idea_id = ?");
    $stmt->execute([$userId, $ideaId]);
    $result = $stmt->fetch();
    
    return $result ? $result['vote_type'] : false;
}

/**
 * Get vote counts for an idea
 * 
 * @param int $ideaId Idea ID
 * @return array Array with 'like' and 'dislike' counts
 */
function get_vote_counts($ideaId) {
    global $pdo;
    
    $likes = 0;
    $dislikes = 0;
    
    $stmt = $pdo->prepare("SELECT vote_type, COUNT(*) as count FROM votes WHERE idea_id = ? GROUP BY vote_type");
    $stmt->execute([$ideaId]);
    
    while ($row = $stmt->fetch()) {
        if ($row['vote_type'] === 'like') {
            $likes = $row['count'];
        } else if ($row['vote_type'] === 'dislike') {
            $dislikes = $row['count'];
        }
    }
    
    return [
        'like' => $likes,
        'dislike' => $dislikes
    ];
}

/**
 * Get category name in specified language
 * 
 * @param int $categoryId Category ID
 * @param string $lang Language code ('en' or 'ar')
 * @return string Category name
 */
function get_category_name($categoryId, $lang = null) {
    global $pdo;
    
    if (!$lang) {
        $lang = current_language();
    }
    
    $field = $lang === 'ar' ? 'name_ar' : 'name_en';
    
    $stmt = $pdo->prepare("SELECT {$field} FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $result = $stmt->fetch();
    
    return $result ? $result[$field] : 'Unknown';
}

/**
 * Check if a user has bookmarked an idea
 * 
 * @param int $userId User ID
 * @param int $ideaId Idea ID
 * @return bool True if bookmarked, false otherwise
 */
function has_user_bookmarked($userId, $ideaId) {
    global $pdo;
    
    if (!$userId) {
        return false;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND idea_id = ?");
    $stmt->execute([$userId, $ideaId]);
    
    return $stmt->rowCount() > 0;
}

/**
 * Check if a user is following another user
 * 
 * @param int $followerId Follower user ID
 * @param int $followingId Following user ID
 * @return bool True if following, false otherwise
 */
function is_following($followerId, $followingId) {
    global $pdo;
    
    if (!$followerId || !$followingId) {
        return false;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM follows WHERE follower_id = ? AND following_id = ?");
    $stmt->execute([$followerId, $followingId]);
    
    return $stmt->rowCount() > 0;
}

/**
 * Get user's reaction to an idea
 * 
 * @param int $userId User ID
 * @param int $ideaId Idea ID
 * @return string|null Reaction type or null if no reaction
 */
function get_user_reaction($userId, $ideaId) {
    global $pdo;
    
    if (!$userId) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT reaction_type FROM reactions WHERE user_id = ? AND idea_id = ?");
    $stmt->execute([$userId, $ideaId]);
    $result = $stmt->fetch();
    
    return $result ? $result['reaction_type'] : null;
}

/**
 * Get reaction counts for an idea
 * 
 * @param int $ideaId Idea ID
 * @return array Array with reaction types as keys and counts as values
 */
function get_reaction_counts($ideaId) {
    global $pdo;
    
    $reactions = [
        'like' => 0,
        'love' => 0,
        'fire' => 0,
        'laugh' => 0,
        'wow' => 0,
        'sad' => 0,
        'angry' => 0,
        'rocket' => 0,
        'brain' => 0,
        'star' => 0
    ];
    
    $stmt = $pdo->prepare("SELECT reaction_type, COUNT(*) as count FROM reactions WHERE idea_id = ? GROUP BY reaction_type");
    $stmt->execute([$ideaId]);
    
    while ($row = $stmt->fetch()) {
        if (isset($reactions[$row['reaction_type']])) {
            $reactions[$row['reaction_type']] = $row['count'];
        }
    }
    
    return $reactions;
}

/**
 * Get count of followers for a user
 * 
 * @param int $userId User ID
 * @return int Follower count
 */
function get_follower_count($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM follows WHERE following_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    return (int) $result['count'];
}

/**
 * Get count of users being followed by a user
 * 
 * @param int $userId User ID
 * @return int Following count
 */
function get_following_count($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM follows WHERE follower_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    return (int) $result['count'];
}

/**
 * Get user profile data
 * 
 * @param int $userId User ID
 * @return array|false User data or false if not found
 */
function get_user_profile($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, username, email, bio, image_url, language, theme, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    
    return $stmt->fetch();
}

/**
 * Get trending ideas
 * 
 * @param int $limit Number of ideas to return
 * @return array Array of trending ideas
 */
function get_trending_ideas($limit = 5) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM trending_ideas WHERE is_public = 1 LIMIT ?");
    $stmt->execute([$limit]);
    
    return $stmt->fetchAll();
}

/**
 * Parse tags from database format
 * 
 * @param string $tags_string Tags string from database
 * @return array Array of tag strings
 */
function parse_tags($tags_string) {
    if (empty($tags_string)) {
        return [];
    }
    
    // Handle JSON array format
    if (strpos($tags_string, '[') === 0) {
        $tags = json_decode($tags_string, true);
        if (is_array($tags)) {
            return array_filter(array_map('trim', $tags));
        }
    }
    
    // Handle comma-separated format
    $tags = array_map('trim', explode(',', $tags_string));
    return array_filter($tags);
}