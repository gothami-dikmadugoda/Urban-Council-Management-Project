<?php
session_start();
require_once __DIR__ . '/../controllers/NotificationController.php';
require_once __DIR__ . '/../models/Chat.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $notificationController = new NotificationController();
    $chat = new Chat();

    // Get unread notifications count
    $unreadNotifications = $notificationController->getUnreadCount($_SESSION['user_id']);
    
    // Get unread messages count
    $unreadMessages = $chat->getUnreadCount($_SESSION['user_id']);

    // Total count
    $totalCount = $unreadNotifications + $unreadMessages;

    echo json_encode([
        'success' => true,
        'count' => $totalCount,
        'notifications' => $unreadNotifications,
        'messages' => $unreadMessages
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching unread counts'
    ]);
} 