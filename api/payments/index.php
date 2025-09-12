<?php
require_once __DIR__ . '/../../controllers/PaymentController.php';

header('Content-Type: application/json');

$paymentController = new PaymentController();

// Get payment details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $paymentId = $_GET['id'];
    $payment = $paymentController->getPaymentDetails($paymentId);
    
    if ($payment) {
        echo json_encode(['success' => true, 'data' => $payment]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Payment not found']);
    }
}

// Update payment status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] === 'update-status') {
    $paymentId = $_GET['id'];
    $status = $_POST['status'];
    
    if ($paymentController->updatePaymentStatus($paymentId, $status)) {
        echo json_encode(['success' => true, 'message' => 'Payment status updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update payment status']);
    }
}

// Export reports
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'export') {
    $filter = $_GET['filter'] ?? 'all';
    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';
    $status = $_GET['status'] ?? '';
    $exportType = $_GET['export_type'] ?? 'excel';

    $payments = $paymentController->getPayments($filter, $startDate, $endDate, $status);
    
    if ($exportType === 'excel') {
        $filename = $paymentController->exportToExcel($payments);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        readfile(__DIR__ . '/../../public/reports/' . $filename);
    } elseif ($exportType === 'pdf') {
        $filename = $paymentController->exportToPDF($payments);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        readfile(__DIR__ . '/../../public/reports/' . $filename);
    } elseif ($exportType === 'jasper') {
        $filename = $paymentController->generateJasperReport($payments);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        readfile(__DIR__ . '/../../public/reports/' . $filename);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid export type']);
    }
    exit;
}
?> 