<?php
session_start();
require_once __DIR__ . '/../controllers/ChatController.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$chat_type = $_GET['chat_type'] ?? '';
$chat_id = $_GET['chat_id'] ?? null;
$query = $_GET['query'] ?? '';

if (empty($chat_type) || empty($chat_id) || empty($query)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

try {
    $chatController = new ChatController();
    $results = $chatController->searchMessages(
        $_SESSION['user_id'],
        $query,
        $chat_type,
        $chat_id
    );
    
    echo json_encode([
        'success' => true,
        'data' => $results
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error searching messages'
    ]);
} 