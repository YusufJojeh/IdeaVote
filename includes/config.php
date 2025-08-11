<?php
require_once __DIR__ . '/env.php';
// Database configuration (read from .env if available)
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'idea');

// Site settings
$site_name = 'منصة اقتراح الأفكار والتصويت عليها';
$default_lang = 'ar'; // or 'en'
?>