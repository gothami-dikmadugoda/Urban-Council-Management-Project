<?php
session_start();
require_once __DIR__ . '/../controllers/SystemMessageController.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

try {
    $controller = new SystemMessageController();
    $response = $controller->getUnreadCount($_SESSION['user_id']);
    echo json_encode($response);
} catch (Exception $e) {
    error_log("Error in get_system_messages_count.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while getting message count']);
} 