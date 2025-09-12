<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../AdminController.php';

$adminController = new AdminController();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'getRecentActivities':
            $activities = $adminController->getRecentActivities();
            echo json_encode($activities);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
} 