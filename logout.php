<?php
session_start();
require_once 'controllers/UserController.php';

$userController = new UserController();
$result = $userController->logout();

if ($result['success']) {
    $_SESSION['success_message'] = $result['message'];
} else {
    $_SESSION['error_message'] = $result['message'];
}

header('Location: login.php');
exit;
?> 