<?php
session_start();
require_once __DIR__ . '/../../controllers/ChatController.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get search parameters
$search_term = $_POST['search_term'] ?? '';
$type = $_POST['type'] ?? 'all';
$group_id = $_POST['group_id'] ?? null;

if (empty($search_term)) {
    echo json_encode(['success' => false, 'message' => 'Search term is required']);
    exit();
}

try {
    $chatController = new ChatController();
    $results = $chatController->searchMessages(
        $_SESSION['user_id'],
        $search_term,
        $type,
        $group_id
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