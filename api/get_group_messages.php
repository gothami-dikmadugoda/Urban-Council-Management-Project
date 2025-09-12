<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../controllers/ChatController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if group_id is provided
if (!isset($_GET['group_id'])) {
    echo json_encode(['success' => false, 'message' => 'Group ID is required']);
    exit();
}

try {
    $chatController = new ChatController();
    $messages = $chatController->getGroupMessages($_GET['group_id']);
    echo json_encode(['success' => true, 'data' => $messages]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error retrieving group messages']);
} 