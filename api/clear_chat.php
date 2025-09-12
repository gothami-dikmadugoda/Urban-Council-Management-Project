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
$chat_type = $data['chat_type'] ?? '';
$chat_id = $data['chat_id'] ?? null;

if (empty($chat_type) || empty($chat_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

try {
    $chatController = new ChatController();
    $result = $chatController->clearChat($_SESSION['user_id'], $chat_type, $chat_id);
    
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Chat cleared successfully' : 'Failed to clear chat'
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error clearing chat'
    ]);
} 