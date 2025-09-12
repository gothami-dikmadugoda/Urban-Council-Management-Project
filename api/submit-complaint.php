<?php
// Prevent any output before headers
ob_start();

// Error reporting (keep display off for production, log errors)
error_reporting(E_ALL);
ini_set('display_errors', 0); 
ini_set('log_errors', 1);
// ini_set('error_log', dirname(__DIR__) . '/logs/php_error.log'); // Ensure logs dir exists and is writable

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/config/paths.php';
require_once CONTROLLERS_PATH . '/ComplaintController.php';

// Set JSON header
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in. Please log in to submit a complaint.');
    }

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // Prepare complaint data from POST and SESSION
    // Basic sanitization/trimming (consider more robust sanitization)
    $complaintData = [
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'department_id' => filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT),
        'category' => trim($_POST['category'] ?? ''),
        'user_id' => $_SESSION['user_id'], // Get from session
        'priority' => trim($_POST['priority'] ?? 'medium'),
        'assigned_to' => filter_input(INPUT_POST, 'assigned_to', FILTER_VALIDATE_INT),
        'status' => 'pending' // Default status
    ];

     // Include image data only if uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $complaintData['image'] = $_FILES['image'];
    } else if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle upload errors other than 'no file'
         throw new Exception('Error uploading image: Code ' . $_FILES['image']['error']);
    }

    // Submit complaint using the controller
    $complaintController = new ComplaintController();
    $result = $complaintController->createComplaint($complaintData);

    // Clear any output buffers before sending JSON
    while (ob_get_level()) {
        ob_end_clean();
    }

    if ($result['success']) {
        http_response_code(201); // Created
        echo json_encode([
            'success' => true,
            'message' => $result['message'],
            'complaint_id' => $result['complaint_id']
        ]);
    } else {
        // Use 400 Bad Request for validation errors, 500 for server errors
        http_response_code(strpos($result['message'], 'Missing required field') !== false ? 400 : 500); 
        echo json_encode([
            'success' => false,
            'message' => $result['message'] ?? 'An unexpected error occurred.'
        ]);
    }
    exit;

} catch (Exception $e) {
    error_log("Critical Error in submit-complaint.php: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
    
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false,
        'message' => 'A server error occurred. Please try again later.' // Don't expose detailed error messages
        // 'debug_message' => $e->getMessage() // Optional: for development debugging only
    ]);
    exit;
}
?> 