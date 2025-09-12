<?php
session_start();
require_once __DIR__ . '/../../controllers/AdminController.php';

$adminController = new AdminController();
$adminController->validateAdminAccess();

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid staff ID";
    header('Location: /urban2/views/admin/staff.php');
    exit;
}

$staffId = $_GET['id'];
$result = $adminController->deleteStaff($staffId);

if ($result['success']) {
    $_SESSION['success_message'] = $result['message'];
} else {
    $_SESSION['error_message'] = $result['message'];
}

header('Location: /urban2/views/admin/staff.php');
exit;
?> 