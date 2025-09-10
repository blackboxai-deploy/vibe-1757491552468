<?php
/**
 * GV Florida Fleet Management System
 * API: Get Driver Details
 */

require_once '../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse([], false, 'Invalid request method');
}

$driverId = intval($_GET['driver_id'] ?? 0);

if (!$driverId) {
    jsonResponse([], false, 'Driver ID is required');
}

try {
    $driver = $db->fetchOne("SELECT * FROM drivers WHERE driver_id = ?", [$driverId]);
    
    if (!$driver) {
        jsonResponse([], false, 'Driver not found');
    }
    
    jsonResponse($driver, true, 'Driver details retrieved successfully');
    
} catch (Exception $e) {
    logError("Get driver error: " . $e->getMessage());
    jsonResponse([], false, 'An error occurred while retrieving driver details');
}
?>