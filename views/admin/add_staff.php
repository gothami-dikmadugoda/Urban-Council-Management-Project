<?php
session_start();
require_once __DIR__ . '/../../controllers/AdminController.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$adminController = new AdminController();
$adminController->validateAdminAccess();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $adminController->register($_POST);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
    
    header('Location: /urban2/views/admin/staff.php');
    exit;
} else {
    header('Location: /urban2/views/admin/staff.php');
    exit;
}
?> 