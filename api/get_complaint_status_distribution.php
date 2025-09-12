<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'citizen') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

require_once '../controllers/ComplaintController.php';
$complaintController = new ComplaintController();

$userId = $_SESSION['user_id'];

// Fetch all complaints for this user
$complaints = $complaintController->getComplaintsByUserId($userId);

// Initialize status counts
$statusCounts = [
    'Resolved' => 0,
    'In Process' => 0,
    'Pending' => 0,
    'Closed' => 0
];

// Map DB status to display status
$statusMap = [
    'resolved' => 'Resolved',
    'in_progress' => 'In Process',
    'pending' => 'Pending',
    'closed' => 'Closed'
];

foreach ($complaints as $complaint) {
    $dbStatus = $complaint['status'];
    if (isset($statusMap[$dbStatus])) {
        $displayStatus = $statusMap[$dbStatus];
        $statusCounts[$displayStatus]++;
    }
}

echo json_encode([
    'status' => 'success',
    'data' => $statusCounts
]); 