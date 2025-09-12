<?php
require_once __DIR__ . '/../controllers/PaymentController.php';
require_once __DIR__ . '/../config/database.php';

// Remove the session_start() since session is already started in the including file
// session_start();

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || $_SESSION['department'] !== 'it' || $_SESSION['job_role'] !== 'it_staff') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

// Get filter parameters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;
$status = isset($_GET['status']) ? $_GET['status'] : null;
$paymentType = isset($_GET['payment_type']) ? $_GET['payment_type'] : null;

// Initialize PaymentController
try {
    $paymentController = new PaymentController();
    $payments = $paymentController->getFilteredPayments($startDate, $endDate, $status, null, $paymentType);
} catch (Exception $e) {
    error_log("Error in payment report generation: " . $e->getMessage());
    $payments = [];
}

// Calculate totals
$totalAmount = 0;
$statusCounts = ['pending' => 0, 'completed' => 0, 'failed' => 0];
$typeCounts = [];

foreach ($payments as $payment) {
    $totalAmount += $payment['amount'];
    $statusCounts[$payment['payment_status']]++;
    
    if (!isset($typeCounts[$payment['payment_type']])) {
        $typeCounts[$payment['payment_type']] = [
            'count' => 0,
            'amount' => 0
        ];
    }
    $typeCounts[$payment['payment_type']]['count']++;
    $typeCounts[$payment['payment_type']]['amount'] += $payment['amount'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Report</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background: #fff;
            color: #333;
            line-height: 1.6;
        }
        .jasper-report {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .report-header {
            text-align: center;
            padding: 20px;
            border-bottom: 2px solid #2c3e50;
            position: relative;
        }
        .logo {
            max-width: 120px;
            position: absolute;
            left: 20px;
            top: 20px;
        }
        .report-title {
            font-size: 24px;
            color: #2c3e50;
            margin: 0;
            padding: 10px 0;
        }
        .report-meta {
            color: #666;
            font-size: 12px;
            margin-top: 10px;
        }
        .summary-section {
            display: flex;
            justify-content: space-between;
            margin: 20px;
            gap: 15px;
        }
        .summary-box {
            flex: 1;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            text-align: center;
        }
        .summary-box h3 {
            margin: 0;
            font-size: 14px;
            color: #495057;
            text-transform: uppercase;
        }
        .summary-box p {
            margin: 10px 0 0;
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
        }
        .report-section {
            margin: 30px 20px;
        }
        .section-title {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e9ecef;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 13px;
        }
        th {
            background: #2c3e50;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: normal;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e9ecef;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        .report-footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e9ecef;
        }
        @media print {
            body {
                padding: 0;
            }
            .jasper-report {
                box-shadow: none;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="jasper-report">
        <div class="report-header">
            <img src="/urban2/assets/images/logo.png" alt="Logo" class="logo">
            <h1 class="report-title">Payment Report</h1>
            <div class="report-meta">
                Generated on: <?php echo date('Y-m-d H:i:s'); ?><br>
                <?php if ($startDate && $endDate): ?>
                Period: <?php echo date('d/m/Y', strtotime($startDate)); ?> - <?php echo date('d/m/Y', strtotime($endDate)); ?><br>
                <?php endif; ?>
                <?php if (isset($_GET['status'])): ?>
                Status Filter: <?php echo ucfirst($_GET['status']); ?><br>
                <?php endif; ?>
                <?php if (isset($_GET['payment_type'])): ?>
                Payment Type Filter: <?php echo ucfirst(str_replace('_', ' ', $_GET['payment_type'])); ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="summary-section">
            <div class="summary-box">
                <h3>Total Amount</h3>
                <p>Rs. <?php echo number_format($totalAmount, 2); ?></p>
            </div>
            <div class="summary-box">
                <h3>Total Transactions</h3>
                <p><?php echo count($payments); ?></p>
            </div>
            <div class="summary-box">
                <h3>Completed Payments</h3>
                <p><?php echo $statusCounts['completed']; ?></p>
            </div>
            <div class="summary-box">
                <h3>Pending Payments</h3>
                <p><?php echo $statusCounts['pending']; ?></p>
            </div>
        </div>

        <div class="report-section">
            <h2 class="section-title">Payment Type Distribution</h2>
            <table>
                <thead>
                    <tr>
                        <th>Payment Type</th>
                        <th>Count</th>
                        <th>Total Amount</th>
                        <th>% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($typeCounts as $type => $data): ?>
                    <tr>
                        <td><?php echo ucfirst(str_replace('_', ' ', $type)); ?></td>
                        <td><?php echo $data['count']; ?></td>
                        <td>Rs. <?php echo number_format($data['amount'], 2); ?></td>
                        <td><?php echo number_format(($data['amount'] / $totalAmount) * 100, 1); ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="font-weight: bold; background: #f8f9fa;">
                        <td>Total</td>
                        <td><?php echo array_sum(array_column($typeCounts, 'count')); ?></td>
                        <td>Rs. <?php echo number_format($totalAmount, 2); ?></td>
                        <td>100%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="report-section">
            <h2 class="section-title">Detailed Transaction List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Date</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo $payment['payment_id']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($payment['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($payment['user_name']); ?></td>
                        <td>Rs. <?php echo number_format($payment['amount'], 2); ?></td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_type'])); ?></td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $payment['payment_status']; ?>">
                                <?php echo ucfirst($payment['payment_status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($payment['reference_number'] ?? 'N/A'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="report-footer">
            <div>Urban Council Management System - Payment Report</div>
            <div>Generated by IT Department</div>
            <div><?php echo date('Y-m-d H:i:s'); ?></div>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Print Report
        </button>
    </div>
</body>
</html> 