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

// Get current filter selections for form
$currentReportType = $reportType;
$currentDateRange = $dateRange;
$currentDepartment = $department;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Urban Council Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #FF69B4;
            --secondary-color: #9370DB;
            --success-color: #3CB371;
            --warning-color: #FFD700;
            --info-color: #4169E1;
            --dark-color: #202020;
            --light-color: #F1F1F1;
            --cream-color: #FFFDD0;
            --border-radius: 15px;
            --box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
            --gradient-primary: linear-gradient(135deg, #FF69B4, #9370DB);
            --gradient-success: linear-gradient(135deg, #3CB371, #4169E1);
            --gradient-warning: linear-gradient(135deg, #FFD700, #FFFDD0);
            --card-bg: #202020;
            --text-light: #F1F1F1;
            --text-gray: #7E909A;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #151921;
            color: var(--text-light);
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--dark-color);
            color: var(--text-light);
            padding: 1.5rem;
            transition: var(--transition);
            box-shadow: var(--box-shadow);
            z-index: 1000;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: var(--transition);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 0.5rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .nav-link:hover, .nav-link.active {
            color: white;
            background: var(--gradient-primary);
            transform: translateX(5px);
        }

        .nav-link i {
            margin-right: 15px;
            font-size: 1.2rem;
        }

        .card {
            background: linear-gradient(145deg, #1a1f2b, #202632);
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 2.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }

        .card-header {
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
            color: var(--text-light);
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 105, 180, 0.3);
        }

        .report-card {
            background: linear-gradient(145deg, #1a1f2b, #202632);
            color: var(--text-light);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .report-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient-primary);
            opacity: 0.05;
            z-index: 0;
        }

        .report-card h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            position: relative;
            z-index: 1;
        }

        .report-card p {
            margin: 0;
            color: var(--text-gray);
            font-size: 1.1rem;
            position: relative;
            z-index: 1;
        }

        .report-card i {
            position: absolute;
            right: 1rem;
            bottom: 1rem;
            font-size: 3rem;
            opacity: 0.1;
            color: var(--primary-color);
        }

        .chart-container {
            background: linear-gradient(145deg, #1a1f2b, #202632);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
            height: 350px;
        }

        .filter-section {
            background: linear-gradient(145deg, #1a1f2b, #202632);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            color: var(--text-light);
            padding: 0.6rem 2.5rem 0.6rem 1rem;
            font-size: 0.95rem;
            transition: var(--transition);
            /* Remove default arrow */
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            /* Custom arrow */
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
            width: 100%;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-color);
            color: var(--text-light);
            box-shadow: none;
        }

        /* Dropdown menu styling */
        .form-select option {
            background-color: #1a1f2b;
            color: var(--text-light);
            padding: 10px;
            white-space: normal;
        }

        /* Style for select elements in the filter section */
        .filter-section select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            color: var(--text-light);
            padding: 0.6rem 2.5rem 0.6rem 1rem;
            font-size: 0.95rem;
            transition: var(--transition);
            /* Remove default arrow */
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            /* Custom arrow */
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
            width: 100%;
        }

        .filter-section select:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-color);
            color: var(--text-light);
            box-shadow: 0 0 0 0.2rem rgba(255, 105, 180, 0.25);
        }

        .filter-section select option {
            background-color: #1a1f2b;
            color: var(--text-light);
            padding: 10px;
        }

        /* Remove any existing form-select background image */
        .form-select {
            background-image: none;
        }

        .form-label {
            font-weight: 500;
            color: var(--text-light);
        }

        .table {
            color: var(--text-light);
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--text-gray);
            border-color: rgba(255, 255, 255, 0.05);
        }

        .table td {
            border-color: rgba(255, 255, 255, 0.05);
            vertical-align: middle;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
        }

        .badge.bg-success {
            background: var(--success-color) !important;
        }

        .badge.bg-warning {
            background: var(--warning-color) !important;
            color: #000;
        }

        .badge.bg-danger {
            background: var(--gradient-warning) !important;
            color: #000;
        }

        .dropdown-menu {
            background: linear-gradient(145deg, #1a1f2b, #202632);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: var(--border-radius);
        }

        .dropdown-item {
            color: var(--text-light);
        }

        .dropdown-item:hover {
            background: var(--gradient-primary);
            color: white;
        }

        .btn-light {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }

        .btn-light:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card, .report-card, .filter-section {
            animation: fadeIn 0.5s ease-out;
        }

        /* Chart Customization */
        canvas {
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gradient-primary);
            border-radius: 10px;
        }

        /* Page Title */
        h2 {
            color: var(--text-light);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        /* Jasper Report Styles */
        .jasper-report {
            max-width: 1000px;
            margin: 0 auto;
            background: #1a1f2b;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            color: var(--text-light);
            padding: 20px;
        }
        .report-header {
            text-align: center;
            padding: 20px;
            border-bottom: 2px solid var(--primary-color);
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
            color: var(--text-light);
            margin: 0;
            padding: 10px 0;
        }
        .report-meta {
            color: var(--text-gray);
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
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            text-align: center;
        }
        .summary-box h3 {
            margin: 0;
            font-size: 14px;
            color: var(--text-gray);
            text-transform: uppercase;
        }
        .summary-box p {
            margin: 10px 0 0;
            font-size: 20px;
            font-weight: bold;
            color: var(--primary-color);
        }
        .report-section {
            margin: 30px 20px;
        }
        .section-title {
            font-size: 18px;
            color: var(--text-light);
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }
        .jasper-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 13px;
        }
        .jasper-table th {
            background: var(--primary-color);
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: normal;
        }
        .jasper-table td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .jasper-table tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.05);
        }
        .report-footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: var(--text-gray);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        @media print {
            body {
                background: white !important;
                color: #333 !important;
            }
            .jasper-report {
                box-shadow: none;
                background: white !important;
                color: #333 !important;
                padding: 0;
                margin: 0;
            }
            .sidebar, .main-header, .no-print {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            .summary-box {
                background: #f8f9fa !important;
                border: 1px solid #dee2e6 !important;
                color: #333 !important;
            }
            .jasper-table th {
                background: #2c3e50 !important;
                color: white !important;
            }
            .jasper-table tr:nth-child(even) {
                background: #f8f9fa !important;
            }
            .report-meta, .report-footer {
                color: #666 !important;
            }
        }
        .card-header.d-flex {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 1.2rem;
        }
        .card-header.d-flex h5 {
            margin-bottom: 0.7rem !important;
        }
        .btn-group {
            margin-bottom: 0.2rem;
        }
        .row.align-cards {
            display: flex;
            flex-wrap: wrap;
        }
        .row.align-cards > [class^='col-'] {
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include __DIR__ . '/includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Reports & Analytics</h2>
                <div class="d-flex align-items-center">
                    <span class="me-3">Welcome, Admin</span>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class='bx bxs-user-circle'></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/urban2/views/admin/profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="/urban2/views/admin/settings.php">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/urban2/logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" class="row g-3" id="reportForm">
                    <div class="col-md-3">
                        <label class="form-label">Report Type</label>
                        <select class="form-control" name="report_type" id="report_type">
                            <option value="complaints" <?php echo $currentReportType == 'complaints' ? 'selected' : ''; ?>>Complaints Report</option>
                            <option value="staff" <?php echo $currentReportType == 'staff' ? 'selected' : ''; ?>>Staff Performance</option>
                            <option value="revenue" <?php echo $currentReportType == 'revenue' ? 'selected' : ''; ?>>Revenue Report</option>
                            <option value="maintenance" <?php echo $currentReportType == 'maintenance' ? 'selected' : ''; ?>>Maintenance Report</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date Range</label>
                        <select class="form-control" name="date_range" id="date_range">
                            <option value="today" <?php echo $currentDateRange == 'today' ? 'selected' : ''; ?>>Today</option>
                            <option value="week" <?php echo $currentDateRange == 'week' ? 'selected' : ''; ?>>This Week</option>
                            <option value="month" <?php echo $currentDateRange == 'month' ? 'selected' : ''; ?>>This Month</option>
                            <option value="year" <?php echo $currentDateRange == 'year' ? 'selected' : ''; ?>>This Year</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <select class="form-control" name="department" id="department">
                            <option value="">All Departments</option>
                            <option value="health" <?php echo $currentDepartment == 'health' ? 'selected' : ''; ?>>Health</option>
                            <option value="engineering" <?php echo $currentDepartment == 'engineering' ? 'selected' : ''; ?>>Engineering</option>
                            <option value="it" <?php echo $currentDepartment == 'it' ? 'selected' : ''; ?>>IT</option>
                            <option value="reception" <?php echo $currentDepartment == 'reception' ? 'selected' : ''; ?>>Reception</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">Apply Filters</button>
                            <button type="button" class="btn btn-primary" onclick="openJasperReport()">
                                <i class='bx bx-file'></i> Jasper
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Report Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="report-card">
                        <h3><?php echo $reportsData['total_complaints']; ?></h3>
                        <p>Total Complaints</p>
                        <i class='bx bxs-report'></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="report-card">
                        <h3><?php echo $reportsData['resolved_complaints']; ?></h3>
                        <p>Resolved Complaints</p>
                        <i class='bx bxs-check-circle'></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="report-card">
                        <h3><?php echo $reportsData['pending_complaints']; ?></h3>
                        <p>Pending Complaints</p>
                        <i class='bx bxs-time'></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="report-card">
                        <h3><?php echo $reportsData['avg_resolution_time']; ?> days</h3>
                        <p>Avg. Resolution Time</p>
                        <i class='bx bxs-timer'></i>
                    </div>
                </div>
            </div>

            <!-- Detailed Reports -->
            <div class="row align-cards">
                <div class="col-md-8 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header d-flex">
                            <h5 class="mb-0">Complaint Resolution Trends</h5>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" onclick="updateChartPeriod('daily')">Daily</button>
                                <button class="btn btn-sm btn-outline-primary active" onclick="updateChartPeriod('weekly')">Weekly</button>
                                <button class="btn btn-sm btn-outline-primary" onclick="updateChartPeriod('monthly')">Monthly</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="complaintTrendsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <h5 class="mb-0">Department Performance</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="departmentPerformanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Data Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detailed Report Data</h5>
                    <button class="btn btn-primary" onclick="exportReport()">
                        <i class='bx bx-export'></i> Export Report
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Department</th>
                                    <th>Total Complaints</th>
                                    <th>Resolved</th>
                                    <th>Pending</th>
                                    <th>Resolution Rate</th>
                                    <th>Avg. Resolution Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($reportsData['detailed_data']) && is_array($reportsData['detailed_data']) && count($reportsData['detailed_data']) > 0): ?>
                                <?php foreach ($reportsData['detailed_data'] as $row): ?>
                                <tr>
                                    <td><?php echo $row['date']; ?></td>
                                    <td><?php echo ucfirst($row['department']); ?></td>
                                    <td><?php echo $row['total_complaints']; ?></td>
                                    <td><?php echo $row['resolved']; ?></td>
                                    <td><?php echo $row['pending']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $row['resolution_rate'] >= 80 ? 'success' : ($row['resolution_rate'] >= 60 ? 'warning' : 'danger'); ?>">
                                            <?php echo $row['resolution_rate']; ?>%
                                        </span>
                                    </td>
                                    <td><?php echo $row['avg_resolution_time']; ?> days</td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center text-muted">No detailed data available for the selected filters.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Jasper Report Section -->
            <div id="jasperReport" class="jasper-report" style="display: none;">
                <div class="report-header">
                    <img src="/urban2/assets/images/gov-seal.jpeg" alt="Logo" class="logo">
                    <h1 class="report-title">Urban Council Report</h1>
                    <div class="report-meta">
                        Generated on: <?php echo date('Y-m-d H:i:s'); ?><br>
                        Department: <span id="reportDepartment">All Departments</span><br>
                        Period: <span id="reportPeriod">This Month</span>
                    </div>
                </div>

                <div class="summary-section">
                    <div class="summary-box">
                        <h3>Total Complaints</h3>
                        <p id="totalComplaints"><?php echo $reportsData['total_complaints'] ?? 0; ?></p>
                    </div>
                    <div class="summary-box">
                        <h3>Resolved</h3>
                        <p id="resolvedComplaints"><?php echo $reportsData['resolved_complaints'] ?? 0; ?></p>
                    </div>
                    <div class="summary-box">
                        <h3>Pending</h3>
                        <p id="pendingComplaints"><?php echo $reportsData['pending_complaints'] ?? 0; ?></p>
                    </div>
                    <div class="summary-box">
                        <h3>Resolution Rate</h3>
                        <p id="resolutionRate"><?php 
                            $total = $reportsData['total_complaints'] ?? 0;
                            $resolved = $reportsData['resolved_complaints'] ?? 0;
                            echo $total > 0 ? round(($resolved / $total) * 100, 1) : 0;
                        ?>%</p>
                    </div>
                </div>

                <div class="report-section">
                    <h2 class="section-title">Detailed Analysis</h2>
                    <div class="table-responsive">
                        <table class="jasper-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Total</th>
                                    <th>Resolved</th>
                                    <th>Pending</th>
                                    <th>Resolution Rate</th>
                                </tr>
                            </thead>
                            <tbody id="reportTableBody">
                                <!-- Data will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="report-footer">
                    <div>Urban Council Management System - Analytics Report</div>
                    <div>Generated by <?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></div>
                    <div><?php echo date('Y-m-d H:i:s'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update chart data with filtered results
        const complaintTrendsData = <?php echo json_encode($reportsData['complaint_trends']); ?>;
        const departmentPerformanceData = <?php echo json_encode($reportsData['department_performance']); ?>;

        let complaintTrendsChart;

        // Initialize charts
        function initializeCharts() {
            // Complaint Trends Chart
            const complaintTrendsCtx = document.getElementById('complaintTrendsChart').getContext('2d');
            complaintTrendsChart = new Chart(complaintTrendsCtx, {
                type: 'line',
                data: {
                    labels: complaintTrendsData.map(item => item.date),
                    datasets: [{
                        label: 'Total Complaints',
                        data: complaintTrendsData.map(item => item.total),
                        borderColor: '#4361ee',
                        borderWidth: 3,
                        tension: 0.3,
                        fill: true,
                        backgroundColor: 'rgba(67, 97, 238, 0.1)',
                        pointBackgroundColor: '#4361ee',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#4361ee',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }, {
                        label: 'Resolved Complaints',
                        data: complaintTrendsData.map(item => item.resolved),
                        borderColor: '#4cc9f0',
                        borderWidth: 3,
                        tension: 0.3,
                        fill: true,
                        backgroundColor: 'rgba(76, 201, 240, 0.1)',
                        pointBackgroundColor: '#4cc9f0',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#4cc9f0',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Department Performance Chart
            const departmentCtx = document.getElementById('departmentPerformanceChart').getContext('2d');
            new Chart(departmentCtx, {
                type: 'bar',
                data: {
                    labels: departmentPerformanceData.map(item => item.department),
                    datasets: [{
                        label: 'Resolution Rate (%)',
                        data: departmentPerformanceData.map(item => item.resolution_rate),
                        backgroundColor: '#4361ee',
                        borderColor: '#4361ee',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                display: true,
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Update chart period
        function updateChartPeriod(period) {
            // Update button states
            document.querySelectorAll('.btn-group .btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');

            // Fetch new data
            fetch(`/urban2/controllers/AnalyticsController.php?action=getHistoricalData&period=${period}`)
                .then(response => response.json())
                .then(data => {
                    // Update chart data
                    complaintTrendsChart.data.labels = data.map(item => item.time_period);
                    complaintTrendsChart.data.datasets[0].data = data.map(item => item.total_complaints);
                    complaintTrendsChart.data.datasets[1].data = data.map(item => item.resolved_complaints);
                    complaintTrendsChart.update();
                })
                .catch(error => console.error('Error:', error));
        }

        // Initialize charts when the page loads
        document.addEventListener('DOMContentLoaded', initializeCharts);

        // Add export functionality
        function exportReport() {
            const params = new URLSearchParams(window.location.search);
            params.append('export', 'true');
            window.location.href = '/urban2/views/admin/export_report.php?' + params.toString();
        }

        function openJasperReport() {
            // Get current filter values
            const reportType = document.getElementById('report_type').value;
            const dateRange = document.getElementById('date_range').value;
            const department = document.getElementById('department').value;

            // Open report in new tab
            const reportUrl = `/urban2/views/admin/jasper_report.php?report_type=${reportType}&date_range=${dateRange}&department=${department}`;
            window.open(reportUrl, '_blank');
        }
    </script>
</body>
</html> 