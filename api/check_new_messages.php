<?php
session_start();
require_once __DIR__ . '/../controllers/ChatController.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $chatController = new ChatController();
    $unreadCount = $chatController->getUnreadCount($_SESSION['user_id']);
    
    echo json_encode([
        'success' => true,
        'has_new' => $unreadCount > 0,
        'count' => $unreadCount
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error checking new messages'
    ]);
} 