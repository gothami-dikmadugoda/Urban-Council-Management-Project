<?php
session_start();
require_once __DIR__ . '/../controllers/NotificationController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$notificationController = new NotificationController();
$notifications = $notificationController->getNotificationsByType(
    $_SESSION['user_id'],
    ['collection_update', 'collection_reply', 'collection_request']
);

echo json_encode([
    'success' => true,
    'count' => count($notifications)
]); 