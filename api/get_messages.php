<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../controllers/ChatController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if receiver_id is provided
if (!isset($_GET['receiver_id'])) {
    echo json_encode(['success' => false, 'message' => 'Receiver ID is required']);
    exit();
}

try {
    $chatController = new ChatController();
    $messages = $chatController->getMessages($_SESSION['user_id'], $_GET['receiver_id']);
    echo json_encode(['success' => true, 'data' => $messages]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error retrieving messages']);
} 