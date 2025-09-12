<?php
session_start();
require_once __DIR__ . '/../controllers/ChatController.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$message_id = $data['message_id'] ?? null;

if (empty($message_id)) {
    echo json_encode(['success' => false, 'message' => 'Message ID is required']);
    exit();
}

try {
    $chatController = new ChatController();
    $result = $chatController->deleteMessage($_SESSION['user_id'], $message_id);
    
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Message deleted successfully' : 'Failed to delete message'
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting message'
    ]);
} 