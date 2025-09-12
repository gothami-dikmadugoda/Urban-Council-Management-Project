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

$id = $_POST['id'] ?? null;

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Announcement ID is required']);
    exit();
}

try {
    $announcementController = new AnnouncementController();
    $result = $announcementController->deleteAnnouncement($id);
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Error in delete_announcement.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while deleting the announcement']);
} 