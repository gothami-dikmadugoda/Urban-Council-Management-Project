<?php
require_once dirname(__DIR__) . '/config/paths.php';
require_once CONTROLLERS_PATH . '/ComplaintController.php';

// Set error reporting
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

$departmentId = $_GET['department_id'] ?? null;

if (!$departmentId) {
    echo json_encode(['success' => false, 'message' => 'Department ID is required.']);
    exit;
}

try {
    $controller = new ComplaintController();
    // Note: getCategoriesForDepartment in the controller needs to be implemented
    // to fetch actual data and echo JSON directly.
    // This structure assumes the controller method handles the JSON output and exit.
    $controller->getCategoriesForDepartment($departmentId); 
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error fetching categories: ' . $e->getMessage()]);
} 