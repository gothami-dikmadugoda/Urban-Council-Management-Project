<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../controllers/AnnouncementController.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$expiry_datetime = $_POST['expiry_datetime'] ?? null;

if (empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Title and content are required']);
    exit();
}

try {
    $announcementController = new AnnouncementController();
    $result = $announcementController->createAnnouncement($title, $content, $_SESSION['user_id'], $expiry_datetime);
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Error in create_announcement.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while creating the announcement']);
} 