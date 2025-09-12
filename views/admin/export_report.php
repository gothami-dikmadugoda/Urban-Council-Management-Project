<?php
session_start();
require_once __DIR__ . '/../../controllers/AdminController.php';
require_once __DIR__ . '/../../controllers/AnalyticsController.php';

$adminController = new AdminController();
$analyticsController = new AnalyticsController();

$adminController->validateAdminAccess();

// Get filter parameters
$reportType = isset($_GET['report_type']) ? $_GET['report_type'] : 'complaints';
$dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : 'month';
$department = isset($_GET['department']) ? $_GET['department'] : '';

// Get filtered report data
$reportsData = $analyticsController->getFilteredReports($reportType, $dateRange, $department);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $reportType . '_report_' . date('Y-m-d') . '.csv"');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel encoding
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add report header
fputcsv($output, ['Report Type: ' . ucfirst($reportType)]);
fputcsv($output, ['Date Range: ' . ucfirst($dateRange)]);
fputcsv($output, ['Department: ' . ($department ? ucfirst($department) : 'All Departments')]);
fputcsv($output, ['Generated on: ' . date('Y-m-d H:i:s')]);
fputcsv($output, []); // Empty line

// Add summary statistics
fputcsv($output, ['Summary Statistics']);
fputcsv($output, ['Total Complaints', 'Resolved Complaints', 'Pending Complaints', 'Average Resolution Time (days)']);
fputcsv($output, [
    $reportsData['total_complaints'],
    $reportsData['resolved_complaints'],
    $reportsData['pending_complaints'],
    $reportsData['avg_resolution_time']
]);
fputcsv($output, []); // Empty line

// Add detailed data
fputcsv($output, ['Detailed Report Data']);
fputcsv($output, ['Date', 'Department', 'Total Complaints', 'Resolved', 'Pending', 'Resolution Rate (%)', 'Avg. Resolution Time (days)']);

foreach ($reportsData['detailed_data'] as $row) {
    fputcsv($output, [
        $row['date'],
        $row['department'],
        $row['total_complaints'],
        $row['resolved'],
        $row['pending'],
        $row['resolution_rate'],
        $row['avg_resolution_time']
    ]);
}

// Close the file pointer
fclose($output); 