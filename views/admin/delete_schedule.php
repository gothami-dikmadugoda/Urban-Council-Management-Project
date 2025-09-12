<?php
session_start();
require_once __DIR__ . '/../controllers/GarbageScheduleController.php';

// Validate staff access and department
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff' || 
    $_SESSION['department'] !== 'health' || $_SESSION['job_role'] !== 'garbage_manager') {
    header('Location: /urban2/login.php');
    exit;
}

$scheduleController = new GarbageScheduleController();

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid schedule ID";
    header('Location: /urban2/admin/staff_dashboard.php');
    exit;
}

$scheduleId = $_GET['id'];
$result = $scheduleController->deleteSchedule($scheduleId);

if ($result['success']) {
    $_SESSION['success_message'] = $result['message'];
} else {
    $_SESSION['error_message'] = $result['message'];
}

header('Location: /urban2/admin/staff_dashboard.php');
exit;
?> 