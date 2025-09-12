<?php
require_once dirname(__DIR__) . '/config/paths.php';
require_once MODELS_PATH . '/Complaint.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Check if department_id is provided
if (!isset($_GET['department_id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Department ID is required']);
    exit();
}

$department_id = $_GET['department_id'];
$complaint = new Complaint();

try {
    $staff = $complaint->getAvailableStaff($department_id);
    echo json_encode($staff);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}
?> 