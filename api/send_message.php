<?php
session_start();
require_once __DIR__ . '/../controllers/ChatController.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$message = $_POST['message'] ?? '';
$type = $_POST['type'] ?? '';
$receiver_id = $_POST['receiver_id'] ?? null;
$group_id = $_POST['group_id'] ?? null;

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit();
}

if ($type === 'individual' && empty($receiver_id)) {
    echo json_encode(['success' => false, 'message' => 'Receiver ID is required for individual messages']);
    exit();
}

if ($type === 'group' && empty($group_id)) {
    echo json_encode(['success' => false, 'message' => 'Group ID is required for group messages']);
    exit();
}

$controller = new ChatController();
$data = [
    'type' => $type,
    'message' => $message,
    'sender_id' => $_SESSION['user_id'],
    'receiver_id' => $receiver_id,
    'group_id' => $group_id
];

try {
    $response = $controller->sendMessage($data);
    if ($response) {
        echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send message']);
    }
} catch (Exception $e) {
    error_log("Error in send_message.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while sending the message']);
} 