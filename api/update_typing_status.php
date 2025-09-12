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
$user_id = $_SESSION['user_id'];
$chat_id = $data['chat_id'] ?? null;
$is_typing = $data['is_typing'] ?? false;

if (empty($chat_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

try {
    $chatController = new ChatController();
    $result = $chatController->updateTypingStatus($user_id, $chat_id, $is_typing);
    
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Typing status updated successfully' : 'Failed to update typing status'
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error updating typing status'
    ]);
} 