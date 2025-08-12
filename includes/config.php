<?php
/**
 * IdeaVote Configuration
 */

// Error reporting - enable for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Session configuration
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set session cookie parameters - only if session not already started
if (session_status() == PHP_SESSION_ACTIVE) {
    // Session already started, can't modify parameters
} else {
    // Set cookie parameters before session_start
    session_set_cookie_params(0, '/', '', isset($_SERVER['HTTPS']), true);
}