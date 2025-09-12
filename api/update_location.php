<?php
session_start();
require_once __DIR__ . '/../controllers/LocationController.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Validate user authentication and role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['department']) || !isset($_SESSION['job_role'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ]);
    exit;
}

// Check if user is authorized for location tracking
if ($_SESSION['department'] !== 'health' || !in_array($_SESSION['job_role'], ['garbage_collector', 'field_visitor'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['latitude']) || !isset($data['longitude'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid location data'
    ]);
    exit;
}

$locationController = new LocationController();

try {
    $result = $locationController->updateLocation($data);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} 