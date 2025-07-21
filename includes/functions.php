<?php
require_once 'db.php';

function escape($str) {
    global $conn;
    return htmlspecialchars(mysqli_real_escape_string($conn, $str));
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_username($username) {
    return preg_match('/^[A-Za-z0-9_\x{0600}-\x{06FF}]{3,30}$/u', $username);
}

function get_vote_counts($idea_id) {
    global $conn;
    $likes = 0; $dislikes = 0;
    $sql = "SELECT vote_type, COUNT(*) as count FROM votes WHERE idea_id=? GROUP BY vote_type";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $idea_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['vote_type'] == 'like') $likes = $row['count'];
        if ($row['vote_type'] == 'dislike') $dislikes = $row['count'];
    }
    return ['like' => $likes, 'dislike' => $dislikes];
}

function get_category_name($cat_id, $lang = 'ar') {
    global $conn;
    $col = $lang == 'en' ? 'name_en' : 'name_ar';
    $sql = "SELECT $col FROM categories WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $cat_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row[$col] ?? '';
}
?> 