<?php
session_start();
include '../includes/config.php';
include '../includes/i18n.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $language = $_POST['language'] ?? 'en';
    
    // Validate language
    if (in_array($language, ['en', 'ar'])) {
        $_SESSION['language'] = $language;
        echo 'success';
    } else {
        http_response_code(400);
        echo 'Invalid language';
    }
} else {
    http_response_code(405);
    echo 'Method not allowed';
}
?>
