<?php
session_start();
require_once __DIR__ . '/../controllers/SystemMessageController.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$controller = new SystemMessageController();
$response = $controller->getSystemMessages($_GET['limit'] ?? 50);

echo json_encode($response); 