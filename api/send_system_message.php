<?php
session_start();
require_once __DIR__ . '/../controllers/SystemMessageController.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$message = $_POST['message'] ?? '';

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit();
}

try {
    $controller = new SystemMessageController();
    $response = $controller->sendSystemMessage(
        $_SESSION['user_id'],
        $message,
        isset($_FILES['file']) ? $_FILES['file'] : null
    );

    echo json_encode($response);
} catch (Exception $e) {
    error_log("Error in send_system_message.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while sending the message']);
} 