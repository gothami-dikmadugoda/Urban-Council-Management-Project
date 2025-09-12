<?php
// Include database configuration
require_once '../config/database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if visitor_id is provided
if (!isset($_GET['visitor_id']) || empty($_GET['visitor_id'])) {
    // Return empty array if no visitor ID is provided
    echo json_encode([]);
    exit;
}

// Sanitize input
$visitor_id = intval($_GET['visitor_id']);

// Query to get appointments for the selected visitor
$sql = "SELECT 
            appointment_id as id, 
            appointment_date as date, 
            purpose, 
            status, 
            duration 
        FROM appointments 
        WHERE visitor_id = ?
        ORDER BY appointment_date DESC";

// Prepare statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $visitor_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

// Return the results as JSON
echo json_encode($appointments);
?>