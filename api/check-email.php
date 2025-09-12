<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../controllers/AdminController.php';

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['email'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Email is required']);
    exit;
}

$email = $data['email'];

// Create admin controller instance
$adminController = new AdminController();

// Check if email exists
$admin = new Admin($adminController->getConnection());
$admin->email = $email;

$exists = $admin->emailExists();

// Return response
echo json_encode(['exists' => $exists]);
?> 