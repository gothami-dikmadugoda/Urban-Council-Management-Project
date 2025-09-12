<?php
session_start();
require_once __DIR__ . '/../controllers/ChatController.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$status = $_POST['status'] ?? 'offline';

try {
    $chatController = new ChatController();
    $result = $chatController->updateUserStatus($_SESSION['user_id'], $status);
    
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Status updated successfully' : 'Failed to update status'
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error updating status'
    ]);
} 