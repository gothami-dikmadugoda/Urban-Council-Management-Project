<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['department_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Department ID is required']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Get department name first
    $deptQuery = "SELECT name FROM departments WHERE id = ?";
    $deptStmt = $db->prepare($deptQuery);
    $deptStmt->execute([$_GET['department_id']]);
    $department = $deptStmt->fetch(PDO::FETCH_ASSOC);

    if (!$department) {
        throw new Exception('Department not found');
    }

    // Get department categories
    $categoryQuery = "SELECT id, name FROM complaint_categories WHERE department_id = ?";
    $categoryStmt = $db->prepare($categoryQuery);
    $categoryStmt->execute([$_GET['department_id']]);
    $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get department staff using department name from enum
    $staffQuery = "SELECT id, CONCAT(first_name, ' ', last_name) as name 
                  FROM users 
                  WHERE department = LOWER(?) 
                  AND role IN ('staff', 'admin')";
    $staffStmt = $db->prepare($staffQuery);
    $staffStmt->execute([$department['name']]);
    $staff = $staffStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'staff' => $staff,
        'debug' => [
            'department_id' => $_GET['department_id'],
            'department_name' => $department['name'],
            'category_count' => count($categories),
            'staff_count' => count($staff)
        ]
    ]);

} catch (PDOException $e) {
    error_log("Database error in get-department-data.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage(),
        'debug' => [
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
} catch (Exception $e) {
    error_log("General error in get-department-data.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 