<?php
session_start();
require_once '../../controllers/NotificationController.php';

// Check if user is logged in and is IT staff
if (!isset($_SESSION['user_id']) || 
    !isset($_SESSION['department']) || 
    !isset($_SESSION['job_role']) || 
    strtolower($_SESSION['department']) !== 'it' || 
    strtolower($_SESSION['job_role']) !== 'it_staff') {
    error_log('Unauthorized access attempt to payment_notification_bell.php');
    exit();
}

$notificationController = new NotificationController();
$unreadNotifications = $notificationController->getUnreadNotifications($_SESSION['user_id'], 'payment');

$notifications = [];
foreach ($unreadNotifications as $notification) {
    $notifications[] = [
        'notification_id' => $notification['notification_id'],
        'message' => $notification['message'],
        'created_at' => $notification['created_at'],
        'link' => '/urban2/views/staff/payment_details.php?id=' . $notification['reference_id'] . '&notification_id=' . $notification['notification_id']
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'count' => count($notifications),
    'notifications' => $notifications
]);
?> 