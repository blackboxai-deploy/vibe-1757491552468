<?php
/**
 * GV Florida Fleet Management System
 * Application Configuration
 */

session_start();

// Application Settings
define('APP_NAME', 'GV Florida Fleet Management System');
define('APP_VERSION', '1.0');
define('COMPANY_NAME', 'GV Florida Transport Inc.');

// Security Settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 6);
define('MAX_LOGIN_ATTEMPTS', 5);

// File Upload Settings
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Pagination Settings
define('RECORDS_PER_PAGE', 25);

// Application URLs
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$basePath = dirname($_SERVER['SCRIPT_NAME']);
define('BASE_URL', $protocol . $host . $basePath);

// Include database configuration
require_once 'database.php';

// Authentication Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'Admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/dashboard.php?error=access_denied');
        exit;
    }
}

function logout() {
    session_destroy();
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Utility Functions
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function getStatusBadge($status) {
    $badges = [
        'Active' => 'success',
        'Inactive' => 'secondary',
        'In Maintenance' => 'warning',
        'Retired' => 'danger',
        'Assigned' => 'info',
        'Available' => 'success',
        'On Trip' => 'primary'
    ];
    
    $class = isset($badges[$status]) ? $badges[$status] : 'secondary';
    return "<span class='badge bg-{$class}'>{$status}</span>";
}

function getSeverityBadge($severity) {
    $badges = [
        'Low' => 'success',
        'Medium' => 'warning',
        'High' => 'danger',
        'Critical' => 'dark'
    ];
    
    $class = isset($badges[$severity]) ? $badges[$severity] : 'secondary';
    return "<span class='badge bg-{$class}'>{$severity}</span>";
}

// Error handling
function logError($message, $file = '', $line = '') {
    $timestamp = date('Y-m-d H:i:s');
    $log = "[{$timestamp}] Error: {$message}";
    if ($file) $log .= " in {$file}";
    if ($line) $log .= " on line {$line}";
    error_log($log . PHP_EOL, 3, 'logs/errors.log');
}

function showAlert($message, $type = 'info') {
    return "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

// Check session timeout
if (isLoggedIn() && isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        logout();
    }
}
$_SESSION['last_activity'] = time();

// CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateToken();
}

function validateCSRF($token) {
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Response helper for AJAX
function jsonResponse($data, $success = true, $message = '') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}
?>