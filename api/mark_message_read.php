<?php
session_start();
require_once __DIR__ . '/../controllers/ChatController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

// Get the message ID from the request
$data = json_decode(file_get_contents('php://input'), true);
$messageId = $data['message_id'] ?? null;

if (!$messageId) {
    echo json_encode(['success' => false, 'message' => 'Message ID is required']);
    exit();
}

$chatController = new ChatController();

try {
    // Mark the message as read
    $success = $chatController->markMessageAsRead($messageId, $_SESSION['user_id']);
    
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Message marked as read' : 'Failed to mark message as read'
    ]);
} catch (Exception $e) {
    error_log("Error in mark_message_read.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error marking message as read'
    ]);
} 