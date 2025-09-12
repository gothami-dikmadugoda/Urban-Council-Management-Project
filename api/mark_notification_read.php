<?php
session_start();
require_once __DIR__ . '/../controllers/NotificationController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['notification_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
    exit;
}

$notificationController = new NotificationController();
$result = $notificationController->markAsRead($data['notification_id']);

echo json_encode([
    'success' => $result,
    'message' => $result ? 'Notification marked as read' : 'Failed to mark notification as read'
]);
?> 