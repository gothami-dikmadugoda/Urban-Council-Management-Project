<?php
session_start();
require_once __DIR__ . '/../../controllers/AdminController.php';
require_once __DIR__ . '/../../controllers/PaymentController.php';

// Debug information
error_log("Session variables: " . print_r($_SESSION, true));

// Validate IT staff access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || !isset($_SESSION['department']) || !isset($_SESSION['job_role'])) {
    error_log("Missing session variables");
    $_SESSION['redirect_after_login'] = '/urban2/views/admin/it_dashboard.php';
    header('Location: /urban2/login.php');
    exit;
}

// Additional debug logging
error_log("User Role: " . $_SESSION['user_role']);
error_log("Department: " . $_SESSION['department']);
error_log("Job Role: " . $_SESSION['job_role']);

// Check specific roles (using exact values from database)
if ($_SESSION['user_role'] !== 'staff' || strtolower($_SESSION['department']) !== 'it' || strtolower($_SESSION['job_role']) !== 'it_staff') {
    error_log("Invalid role combination for IT dashboard");
    $_SESSION['error_message'] = "Access denied. This page is only for IT staff.";
    header('Location: /urban2/views/admin/staff_dashboard.php');
    exit;
}

// Initialize controllers
$adminController = new AdminController();
$paymentController = new PaymentController();

// Get staff details
try {
    $staffResult = $adminController->getStaffDetails($_SESSION['user_id']);
    if ($staffResult['success']) {
        $staff = $staffResult['data'];
    } else {
        throw new Exception($staffResult['message']);
    }
} catch (Exception $e) {
    error_log("Error fetching staff details: " . $e->getMessage());
    $staff = [
        'first_name' => 'Unknown',
        'last_name' => 'User',
        'profile_picture' => '/assets/images/default-avatar.png'
    ];
}

// Get filter parameters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$paymentType = isset($_GET['payment_type']) ? $_GET['payment_type'] : '';

// Get filtered payments
$payments = $paymentController->getFilteredPayments($startDate, $endDate, $status, null, $paymentType);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Dashboard - Payment Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #3D365C;
            --secondary: #7C4585;
            --accent: #C95792;
            --highlight: #F8B55F;
            --dark: #2A2A2A;
            --light: #F5F5F5;
            --gray: #6c757d;
            --border-radius: 15px;
            --transition: all 0.3s ease;
            --gradient-primary: linear-gradient(135deg, #3D365C, #7C4585);
            --gradient-accent: linear-gradient(135deg, #7C4585, #C95792);
            --gradient-highlight: linear-gradient(135deg, #C95792, #F8B55F);
            --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            --sidebar-width: 280px;
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --secondary-color: #858796;
            --info-color: #36b9cc;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f3ff;
            color: var(--dark);
            line-height: 1.6;
        }
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: var(--sidebar-width);
            background: var(--gradient-primary);
            padding: 0;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            color: var(--light);
            transition: var(--transition);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .profile-section {
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: var(--gradient-primary);
            position: sticky;
            top: 0;
            z-index: 2;
        }
        .nav-container {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }
        .nav-container::-webkit-scrollbar {
            width: 6px;
        }
        .nav-container::-webkit-scrollbar-track {
            background: transparent;
        }
        .nav-container::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }
        .nav-container::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width));
            transition: var(--transition);
        }
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        .dashboard-header {
            margin-bottom: 2rem;
        }
        .dashboard-header h1 {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: #f3f0fa;
            border-radius: 24px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .stat-card:nth-child(1) {
            background: linear-gradient(135deg, #e9e7ff 0%, #d4d1ff 100%);
            border-left: 4px solid #7C4585;
            color: #3D365C;
        }
        .stat-card:nth-child(2) {
            background: linear-gradient(135deg, #e0d9ff 0%, #cbc3ff 100%);
            border-left: 4px solid #7C4585;
            color: #3D365C;
        }
        .stat-card:nth-child(3) {
            background: linear-gradient(135deg, #d7ccff 0%, #c2b5ff 100%);
            border-left: 4px solid #7C4585;
            color: #3D365C;
        }
        .stat-card:nth-child(4) {
            background: linear-gradient(135deg, #cebfff 0%, #b9a7ff 100%);
            border-left: 4px solid #7C4585;
            color: #3D365C;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-color: rgba(255, 255, 255, 0.4);
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .stat-card:hover::before {
            opacity: 1;
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            background: rgba(61, 54, 92, 0.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        .stat-icon:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        .stat-icon i {
            font-size: 24px;
            color: #3D365C;
            transition: all 0.3s ease;
        }
        .stat-icon:hover i {
            transform: scale(1.1);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 600;
            color: #3D365C;
            margin: 0.5rem 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .stat-description {
            color: #7C4585;
            font-size: 0.85rem;
            line-height: 1.4;
        }
        .stat-card .trend-indicator {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
        }
        .stat-card .trend-indicator.up {
            color: var(--success-color);
        }
        .stat-card .trend-indicator.down {
            color: var(--danger-color);
        }
        .filter-section {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        .filter-section h2 {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .filter-group {
            margin-bottom: 1rem;
        }
        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--gray);
            font-weight: 500;
        }
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            background: white;
            transition: all 0.3s ease;
        }
        .filter-group input:focus,
        .filter-group select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(201, 87, 146, 0.1);
            outline: none;
        }
        .filter-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }
        .btn-light {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.1);
            color: var(--gray);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.8rem 1rem;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        .nav-link i {
            font-size: 1.2rem;
        }
        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid var(--accent);
            padding: 3px;
            margin-bottom: 1rem;
            object-fit: cover;
        }
        .card {
            background: white;
            border-radius: 15px;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 30px;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .card-header {
            background: var(--gradient-primary);
            padding: 20px 25px;
            border-radius: 15px 15px 0 0;
        }
        .card-header h5 {
            color: white;
            font-weight: 600;
            margin: 0;
        }
        .table {
            margin: 0;
        }
        .table th {
            background: rgba(0,0,0,0.02);
            font-weight: 600;
            color: var(--dark);
            padding: 15px;
            border-bottom: 2px solid rgba(0,0,0,0.05);
        }
        .table td {
            padding: 15px;
            vertical-align: middle;
            color: var(--dark);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        .status-completed { 
            background-color: #d4edda; 
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-pending { 
            background-color: #fff3cd; 
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .status-failed { 
            background-color: #f8d7da; 
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-refunded { 
            background-color: #cce5ff; 
            color: #004085;
            border: 1px solid #b8daff;
        }
        .status-under_review { 
            background-color: #e2e3e5; 
            color: #383d41;
            border: 1px solid #d6d8db;
        }
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
        }
        .modal-header {
            background: var(--gradient-primary);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px 25px;
        }
        .modal-body {
            padding: 25px;
        }
        .modal-footer {
            border-top: 1px solid rgba(0,0,0,0.05);
            padding: 20px 25px;
        }
        .payment-stats {
            background: transparent;
            border-radius: 10px;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
            }
            
            .dashboard-container {
                padding: 0.5rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="profile-section">
                <img src="<?php 
                    $profilePicture = isset($staff['profile_picture']) && !empty($staff['profile_picture']) 
                        ? '/urban2/uploads/profile_pictures/' . $staff['profile_picture'] 
                        : '/urban2/assets/images/default-avatar.png';
                    echo $profilePicture;
                ?>" 
                     alt="Profile Image" class="profile-picture">
                <h5 class="text-white mb-1"><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></h5>
                <p class="text-muted mb-0">IT Department</p>
            </div>

            <div class="nav-container">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/">
                        <i class='bx bxs-home'></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/admin/staff_dashboard.php">
                        <i class='bx bxs-dashboard'></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/admin/staff_profile.php">
                        <i class='bx bxs-user'></i> Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/urban2/views/admin/it_dashboard.php">
                        <i class='bx bxs-credit-card'></i> Payments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/admin/analytics.php">
                        <i class='bx bxs-report'></i> Analytics
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/admin/announcements.php">
                        <i class='bx bxs-megaphone'></i> Announcements
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/admin/notifications.php">
                        <i class='bx bxs-bell'></i> Notifications
                        <?php if (isset($stats['unread_notifications']) && $stats['unread_notifications'] > 0): ?>
                            <span class="badge bg-danger ms-2"><?php echo $stats['unread_notifications']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="/urban2/logout.php" onclick="return confirm('Are you sure you want to logout?');">
                        <i class='bx bxs-log-out'></i> Logout
                    </a>
                </li>
            </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container-fluid">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Payment Management</h1>
                    <div class="d-flex align-items-center">
                        <!-- Notification Bell -->
                        <?php include_once __DIR__ . '/../notification_bell.php'; ?>
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class='bx bxs-user-circle'></i> <?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="staff_profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Payment Statistics -->
                <div class="row payment-stats">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class='bx bxs-credit-card'></i>
                            </div>
                            <h6>Total Payments</h6>
                            <h3><?php echo $paymentController->getTotalPayments(); ?></h3>
                            <p class="mb-0 text-muted">All time payments</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class='bx bxs-check-circle'></i>
                            </div>
                            <h6>Successful Payments</h6>
                            <h3><?php echo $paymentController->getSuccessfulPayments(); ?></h3>
                            <p class="mb-0 text-success">Completed transactions</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class='bx bxs-time'></i>
                            </div>
                            <h6>Pending Payments</h6>
                            <h3><?php echo $paymentController->getPendingPayments(); ?></h3>
                            <p class="mb-0 text-warning">Awaiting verification</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class='bx bxs-dollar-circle'></i>
                            </div>
                            <h6>Total Revenue</h6>
                            <h3>Rs. <?php echo number_format($paymentController->getTotalRevenue(), 2); ?></h3>
                            <p class="mb-0 text-primary">Total earnings</p>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <h5 class="mb-4">Filter Payments</h5>
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bxs-calendar'></i></span>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bxs-calendar'></i></span>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Payment Status</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bxs-filter-alt'></i></span>
                            <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="payment_type" class="form-label">Payment Type</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bxs-category'></i></span>
                            <select class="form-select" id="payment_type" name="payment_type">
                                    <option value="">All Types</option>
                                    <option value="tax" <?php echo $paymentType === 'tax' ? 'selected' : ''; ?>>Tax</option>
                                    <option value="service_charge" <?php echo $paymentType === 'service_charge' ? 'selected' : ''; ?>>Service Charge</option>
                                    <option value="fine" <?php echo $paymentType === 'fine' ? 'selected' : ''; ?>>Fine</option>
                                    <option value="other" <?php echo $paymentType === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class='bx bxs-filter-alt'></i> Apply Filters
                            </button>
                            <a href="it_dashboard.php" class="btn btn-light">
                                <i class='bx bxs-refresh'></i> Reset
                            </a>
                            <button type="button" onclick="printFilteredReport()" class="btn btn-info float-end">
                                <i class='bx bxs-printer'></i> Print Report
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Payments Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Payment Records</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="paymentsTable">
                                <thead>
                                    <tr>
                                        <th>Payment ID</th>
                                        <th>User</th>
                                        <th>Amount</th>
                                        <th>Payment Type</th>
                                        <th>Payment Method</th>
                                        <th>Status</th>
                                        <th>Bank Details</th>
                                        <th>Verification</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?php echo $payment['payment_id']; ?></td>
                                        <td><?php echo htmlspecialchars($payment['user_name']); ?></td>
                                        <td>Rs. <?php echo number_format($payment['amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($payment['payment_type']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                        <td><?php echo ucfirst($payment['payment_status']); ?></td>
                                        <td>
                                            <?php if ($payment['payment_method'] === 'bank_transfer'): ?>
                                                <?php echo htmlspecialchars($payment['bank_name'] ?? 'N/A'); ?><br>
                                                <small>Ref: <?php echo htmlspecialchars($payment['reference_number'] ?? 'N/A'); ?></small>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($payment['verified_by']) {
                                                echo 'Verified';
                                                if (!empty($payment['verifier_name'])) {
                                                    echo ' by ' . htmlspecialchars($payment['verifier_name']);
                                                }
                                            } else {
                                                echo 'Not verified';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Details Modal -->
    <div class="modal fade" id="paymentDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="paymentDetailsContent">
                    <!-- Payment details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable with proper button configuration and remove search
            $('#paymentsTable').DataTable({
                dom: 'Bfrtip',
                searching: false, // Disable search functionality
                buttons: [
                    {
                        extend: 'copy',
                        className: 'btn btn-secondary'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-info'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-primary',
                        customize: function (win) {
                            $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                        }
                    }
                ]
            });

            // Handle filter form submission
            $('form').on('submit', function(e) {
                e.preventDefault();
                refreshPaymentData();
            });
        });

        function viewPaymentDetails(paymentId) {
            $.get(`/urban2/api/payments/${paymentId}`, function(data) {
                $('#paymentDetailsContent').html(data);
                $('#paymentDetailsModal').modal('show');
            });
        }

        function updatePaymentStatus(paymentId, newStatus) {
            $.post(`/urban2/api/payments/${paymentId}/update-status`, { status: newStatus }, function(response) {
                if (response.success) {
                    alert('Payment status updated successfully');
                    refreshPaymentData();
                } else {
                    alert('Failed to update payment status');
                }
            });
        }

        function exportReport(type) {
            const filters = new FormData($('form')[0]);
            filters.append('export_type', type);
            
            if (type === 'print') {
                // Open printable HTML report in new window
                window.open(`/urban2/api/payments/export?${new URLSearchParams(filters).toString()}`, '_blank');
            } else {
                // Download CSV or XML file
                window.location.href = `/urban2/api/payments/export?${new URLSearchParams(filters).toString()}`;
            }
        }

        function refreshPaymentData() {
            const filters = new FormData($('form')[0]);
            window.location.href = `?${new URLSearchParams(filters).toString()}`;
        }

        function copyToClipboard() {
            const table = document.getElementById('paymentsTable');
            const range = document.createRange();
            range.selectNode(table);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            document.execCommand('copy');
            window.getSelection().removeAllRanges();
            alert('Table copied to clipboard!');
        }

        function exportToExcel() {
            // Add Excel export logic
            alert('Excel export functionality will be implemented');
        }

        function printTable() {
            window.print();
        }

        function viewPayment(id) {
            // Add view payment logic
            window.location.href = `view_payment.php?id=${id}`;
        }

        function verifyPayment(id) {
            // Add verify payment logic
            if (confirm('Are you sure you want to verify this payment?')) {
                // Implement verification logic
            }
        }

        function queryPayment(id) {
            // Add query payment logic
            window.location.href = `query_payment.php?id=${id}`;
        }

        function printFilteredReport() {
            // Get current filter values
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const status = document.getElementById('status').value;
            const paymentType = document.getElementById('payment_type').value;

            // Construct URL with filter parameters
            const url = `/urban2/reports/generate_payment_report.php?start_date=${startDate}&end_date=${endDate}&status=${status}&payment_type=${paymentType}&format=jasper`;

            // Open report in new window
            window.open(url, '_blank');
        }
    </script>
</body>
</html>
