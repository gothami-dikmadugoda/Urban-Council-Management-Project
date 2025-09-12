<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$today = date('Y-m-d');
$month = date('Y-m');
$year = date('Y');

$data = [
    'today' => 0,
    'month' => 0,
    'year' => 0
];

try {
    // Appointments today
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE DATE(appointment_date) = ?");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $data['today'] = (int)$row['total'];

    // Appointments this month
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE DATE_FORMAT(appointment_date, '%Y-%m') = ?");
    $stmt->bind_param("s", $month);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $data['month'] = (int)$row['total'];

    // Appointments this year
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE YEAR(appointment_date) = ?");
    $stmt->bind_param("s", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $data['year'] = (int)$row['total'];

    echo json_encode($data);
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
