<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../controllers/AnnouncementController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $announcementController = new AnnouncementController();
    $result = $announcementController->getActiveAnnouncements();
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Error in get_announcements.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while fetching announcements']);
} 