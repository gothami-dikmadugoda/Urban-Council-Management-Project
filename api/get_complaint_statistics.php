<?php
session_start();
require_once '../controllers/ComplaintController.php';

header('Content-Type: application/json');

// Add debug logging
error_log("Complaint statistics API called");

if (!isset($_SESSION['user_id'])) {
    error_log("User not authenticated");
    echo json_encode([
        'status' => 'error',
        'message' => 'User not authenticated'
    ]);
    exit;
}

try {
    $complaintController = new ComplaintController();
    $timeframe = isset($_GET['timeframe']) ? $_GET['timeframe'] : 'monthly';
    
    // Validate timeframe
    if (!in_array($timeframe, ['weekly', 'monthly', 'yearly'])) {
        $timeframe = 'monthly';
    }
    
    error_log("Fetching statistics for user_id: " . $_SESSION['user_id'] . ", timeframe: " . $timeframe);
    
    $result = $complaintController->getComplaintStatistics($_SESSION['user_id'], $timeframe);
    
    // Log the result
    error_log("API Response: " . json_encode($result));
    
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Error in complaint statistics API: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch complaint statistics'
    ]);
} 