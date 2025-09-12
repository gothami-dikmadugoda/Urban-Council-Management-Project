<?php
session_start();
require_once '../controllers/NotificationController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$notificationController = new NotificationController();

try {
    // Get unread payment notifications
    $notifications = $notificationController->getUnreadNotifications($_SESSION['user_id'], 'payment');
    
    echo json_encode([
        'success' => true,
        'count' => count($notifications),
        'notifications' => $notifications
    ]);
} catch (Exception $e) {
    error_log("Error getting payment notifications: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching notifications'
    ]);
}

include_once __DIR__ . '/../views/payment_notification_bell.php'; ?> 