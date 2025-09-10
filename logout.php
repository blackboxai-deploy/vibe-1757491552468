<?php
/**
 * GV Florida Fleet Management System
 * Logout Script
 */

require_once 'config/config.php';

// Clear all session data
session_unset();
session_destroy();

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Redirect to login page with logout message
header('Location: login.php?message=logged_out');
exit;
?>