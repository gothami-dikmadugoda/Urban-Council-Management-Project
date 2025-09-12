<?php
session_start();
require_once __DIR__ . '/../../controllers/AdminController.php';
require_once __DIR__ . '/../../controllers/AnalyticsController.php';

// Validate admin access
$adminController = new AdminController();
$adminController->validateAdminAccess();

// Get filter parameters
$reportType = isset($_GET['report_type']) ? $_GET['report_type'] : 'complaints';
$dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : 'month';
$department = isset($_GET['department']) ? $_GET['department'] : '';

// Get report data
$analyticsController = new AnalyticsController();
$reportsData = $analyticsController->getFilteredReports($reportType, $dateRange, $department);

// Format department name
$departmentName = $department ? ucfirst($department) : 'All Departments';

// Format date range
$dateRangeText = ucfirst($dateRange);

// Get user information
$userInfo = $adminController->getUserInfo($_SESSION['user_id']);
$generatedBy = isset($userInfo) ? $userInfo['first_name'] . ' ' . $userInfo['last_name'] : 'Admin User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jasper Report - Urban Council Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0091D5;
            --secondary-color: #1C4E80;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --info-color: #0091D5;
            --dark-color: #1a1f2b;
            --light-color: #F1F1F1;
            --gray-color: #7E909A;
            --border-radius: 15px;
            --box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
            --gradient-primary: linear-gradient(135deg, #1C4E80, #0091D5);
            --gradient-success: linear-gradient(135deg, #28a745, #20c997);
            --gradient-info: linear-gradient(135deg, #0091D5, #1C4E80);
        }

        body {
            background: #f8f9fa;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }

        .jasper-report {
            max-width: 1100px;
            margin: 40px auto;
            background: white;
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .report-header {
            text-align: center;
            padding: 2rem;
            background: var(--gradient-primary);
            color: white;
            position: relative;
        }

        .logo {
            max-width: 100px;
            position: absolute;
            left: 2rem;
            top: 50%;
            transform: translateY(-50%);
            filter: brightness(0) invert(1);
        }

        .report-title {
            font-size: 2rem;
            font-weight: 600;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .report-meta {
            color: rgba(255,255,255,0.9);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .summary-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            padding: 2rem;
            background: #fff;
        }

        .summary-box {
            background: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            text-align: center;
            transition: var(--transition);
            border: 1px solid #e9ecef;
        }

        .summary-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .summary-box h3 {
            margin: 0;
            font-size: 0.9rem;
            color: var(--gray-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-box p {
            margin: 1rem 0 0;
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary-color);
            line-height: 1;
        }

        .report-section {
            padding: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title::before {
            content: '';
            width: 4px;
            height: 24px;
            background: var(--gradient-primary);
            border-radius: 2px;
        }

        .jasper-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 1rem 0;
        }

        .jasper-table th {
            background: var(--gradient-primary);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 500;
            font-size: 0.9rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .jasper-table th:first-child {
            border-top-left-radius: 10px;
        }

        .jasper-table th:last-child {
            border-top-right-radius: 10px;
        }

        .jasper-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.9rem;
            color: #495057;
        }

        .jasper-table tr:last-child td:first-child {
            border-bottom-left-radius: 10px;
        }

        .jasper-table tr:last-child td:last-child {
            border-bottom-right-radius: 10px;
        }

        .jasper-table tr:hover td {
            background: #f8f9fa;
        }

        .jasper-table tr:nth-child(even) {
            background: #f8f9fa;
        }

        .report-footer {
            text-align: center;
            padding: 2rem;
            background: #f8f9fa;
            color: var(--gray-color);
            font-size: 0.9rem;
            border-top: 1px solid #e9ecef;
        }

        .print-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
        }

        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.25);
        }

        .print-btn i {
            font-size: 1.2rem;
        }

        @media print {
            body {
                background: white;
            }
            .jasper-report {
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .print-btn {
                display: none;
            }
            .report-header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .jasper-table th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .summary-box:hover {
                transform: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="jasper-report">
        <div class="report-header">
            <img src="/urban2/assets/images/logo.png" alt="Logo" class="logo">
            <h1 class="report-title">Urban Council Report</h1>
            <div class="report-meta">
                Generated on: <?php echo date('Y-m-d H:i:s'); ?><br>
                Department: <?php echo htmlspecialchars($departmentName); ?><br>
                Period: <?php echo htmlspecialchars($dateRangeText); ?>
            </div>
        </div>

        <div class="summary-section">
            <div class="summary-box">
                <h3>Total Complaints</h3>
                <p><?php echo number_format($reportsData['total_complaints'] ?? 0); ?></p>
            </div>
            <div class="summary-box">
                <h3>Resolved</h3>
                <p><?php echo number_format($reportsData['resolved_complaints'] ?? 0); ?></p>
            </div>
            <div class="summary-box">
                <h3>Pending</h3>
                <p><?php echo number_format($reportsData['pending_complaints'] ?? 0); ?></p>
            </div>
            <div class="summary-box">
                <h3>Resolution Rate</h3>
                <p><?php 
                    $total = $reportsData['total_complaints'] ?? 0;
                    $resolved = $reportsData['resolved_complaints'] ?? 0;
                    echo $total > 0 ? number_format(($resolved / $total) * 100, 1) : '0.0';
                ?>%</p>
            </div>
        </div>

        <div class="report-section">
            <h2 class="section-title">Detailed Analysis</h2>
            <div class="table-responsive">
                <table class="jasper-table">
                    <?php if ($reportType === 'complaints'): ?>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Total</th>
                            <th>Resolved</th>
                            <th>Pending</th>
                            <th>Resolution Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($reportsData['categories']) && !empty($reportsData['categories'])): ?>
                            <?php foreach ($reportsData['categories'] as $category): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><?php echo number_format($category['total']); ?></td>
                                    <td><?php echo number_format($category['resolved']); ?></td>
                                    <td><?php echo number_format($category['pending']); ?></td>
                                    <td><?php echo number_format($category['resolution_rate'], 1); ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php elseif ($reportType === 'staff'): ?>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Staff</th>
                            <th>Total Complaints</th>
                            <th>Resolved</th>
                            <th>Pending</th>
                            <th>Resolution Rate</th>
                            <th>Avg. Resolution Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($reportsData['detailed_data']) && !empty($reportsData['detailed_data'])): ?>
                            <?php foreach ($reportsData['detailed_data'] as $data): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($data['date']); ?></td>
                                    <td><?php echo number_format($data['total_staff']); ?></td>
                                    <td><?php echo number_format($data['total_complaints']); ?></td>
                                    <td><?php echo number_format($data['resolved']); ?></td>
                                    <td><?php echo number_format($data['pending']); ?></td>
                                    <td><?php echo number_format($data['resolution_rate'], 1); ?>%</td>
                                    <td><?php echo number_format($data['avg_resolution_time'], 1); ?> days</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <div class="report-footer">
            <div>Urban Council Management System - Analytics Report</div>
            <div>Generated by <?php echo htmlspecialchars($generatedBy); ?></div>
            <div><?php echo date('Y-m-d H:i:s'); ?></div>
        </div>
    </div>

    <button onclick="window.print()" class="print-btn">
        <i class='bx bx-printer'></i> Print Report
    </button>
</body>
</html> 