<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'citizen') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

require_once '../controllers/PaymentController.php';
$paymentController = new PaymentController();

$userId = $_SESSION['user_id'];
$payments = $paymentController->getPaymentsByUserId($userId);

// Prepare data grouped by month and type
$months = [];
$breakdown = [];
foreach ($payments as $payment) {
    $month = date('M Y', strtotime($payment['created_at']));
    if (!in_array($month, $months)) {
        $months[] = $month;
    }
    $ptype = strtolower($payment['payment_type']);
    if ($ptype === 'service_charge') {
        $key = 'schedule booking';
    } elseif ($ptype === 'other') {
        $key = 'schedule collection';
    } else {
        $key = 'others';
    }
    $breakdown[$month][$key] = ($breakdown[$month][$key] ?? 0) + floatval($payment['amount']);
}

sort($months);
$types = ['schedule booking', 'schedule collection', 'others'];
$datasets = [];
foreach ($types as $type) {
    $datasets[$type] = [];
    foreach ($months as $month) {
        $total = 0;
        foreach ($types as $t) {
            $total += $breakdown[$month][$t] ?? 0;
        }
        $amount = $breakdown[$month][$type] ?? 0;
        // Convert to percent of total for the month
        $percent = $total > 0 ? round(($amount / $total) * 100, 1) : 0;
        $datasets[$type][] = $percent;
    }
}

echo json_encode([
    'status' => 'success',
    'data' => [
        'labels' => $months,
        'datasets' => $datasets
    ]
]); 