<?php
/**
 * GV Florida Fleet Management System
 * API: Get Conductor Details
 */

require_once '../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse([], false, 'Invalid request method');
}

$conductorId = intval($_GET['conductor_id'] ?? 0);

if (!$conductorId) {
    jsonResponse([], false, 'Conductor ID is required');
}

try {
    $conductor = $db->fetchOne("SELECT * FROM conductors WHERE conductor_id = ?", [$conductorId]);
    
    if (!$conductor) {
        jsonResponse([], false, 'Conductor not found');
    }
    
    jsonResponse($conductor, true, 'Conductor details retrieved successfully');
    
} catch (Exception $e) {
    logError("Get conductor error: " . $e->getMessage());
    jsonResponse([], false, 'An error occurred while retrieving conductor details');
}
?>