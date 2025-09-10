<?php
/**
 * GV Florida Fleet Management System
 * API: Get Bus Details
 */

require_once '../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse([], false, 'Invalid request method');
}

$busId = intval($_GET['bus_id'] ?? 0);

if (!$busId) {
    jsonResponse([], false, 'Bus ID is required');
}

try {
    $bus = $db->fetchOne("SELECT * FROM buses WHERE bus_id = ?", [$busId]);
    
    if (!$bus) {
        jsonResponse([], false, 'Bus not found');
    }
    
    jsonResponse($bus, true, 'Bus details retrieved successfully');
    
} catch (Exception $e) {
    logError("Get bus error: " . $e->getMessage());
    jsonResponse([], false, 'An error occurred while retrieving bus details');
}
?>