<?php
require_once __DIR__ . '/../controllers/AnalyticsController.php';
require_once __DIR__ . '/../config/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

try {
    // Check if user is logged in and has appropriate role
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        throw new Exception('Access denied');
    }

    // Get filter parameters
    $reportType = isset($_GET['report_type']) ? $_GET['report_type'] : 'complaints';
    $dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : 'month';
    $department = isset($_GET['department']) ? $_GET['department'] : '';

    // Validate parameters
    $validReportTypes = ['complaints', 'staff', 'revenue', 'maintenance'];
    $validDateRanges = ['today', 'week', 'month', 'year'];
    $validDepartments = ['', 'health', 'engineering', 'it', 'reception'];

    if (!in_array($reportType, $validReportTypes)) {
        throw new Exception('Invalid report type');
    }

    if (!in_array($dateRange, $validDateRanges)) {
        throw new Exception('Invalid date range');
    }

    if (!in_array($department, $validDepartments)) {
        throw new Exception('Invalid department');
    }

    $analyticsController = new AnalyticsController();
    $data = $analyticsController->getFilteredReports($reportType, $dateRange, $department);

    // Ensure we have all required data
    $response = [
        'success' => true,
        'total_complaints' => $data['total_complaints'] ?? 0,
        'resolved_complaints' => $data['resolved_complaints'] ?? 0,
        'pending_complaints' => $data['pending_complaints'] ?? 0,
        'categories' => []
    ];

    // Format category data if available
    if (isset($data['categories']) && is_array($data['categories'])) {
        foreach ($data['categories'] as $category) {
            if (isset($category['name'], $category['total'], $category['resolved'])) {
                $response['categories'][] = [
                    'name' => htmlspecialchars($category['name']),
                    'total' => (int)$category['total'],
                    'resolved' => (int)$category['resolved'],
                    'pending' => (int)($category['total'] - $category['resolved']),
                ];
            }
        }
    }

    // Add metadata
    $response['metadata'] = [
        'generated_at' => date('Y-m-d H:i:s'),
        'report_type' => $reportType,
        'date_range' => $dateRange,
        'department' => $department ?: 'all'
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code($e->getMessage() === 'Access denied' ? 403 : 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'details' => DEBUG_MODE ? $e->getTraceAsString() : null
    ]);
} 