<?php
session_start();
require_once __DIR__ . '/../controllers/LocationController.php';

header('Content-Type: application/json');

$locationController = new LocationController();

try {
    $locationController->validateAccess();
    $result = $locationController->getActiveLocations();
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} 