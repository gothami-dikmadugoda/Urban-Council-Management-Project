<?php
session_start();
require_once __DIR__ . '/../../controllers/AdminController.php';
require_once __DIR__ . '/../../controllers/PaymentController.php';
require_once __DIR__ . '/../../controllers/AnnouncementController.php';

// Check if user is logged in and is an IT team member
if (!isset($_SESSION['user_id']) || $_SESSION['department'] !== 'it' || $_SESSION['job_role'] !== 'it_staff') {
    header('Location: /urban2/login.php');
    exit;
}

$adminController = new AdminController();
$paymentController = new PaymentController();
$announcementController = new AnnouncementController();

// Get staff details
$staffResult = $adminController->getStaffDetails($_SESSION['user_id']);
$staff = $staffResult && isset($staffResult['data']) ? $staffResult['data'] : [];

// Get current week's data
$currentWeekStart = date('Y-m-d', strtotime('monday this week'));
$currentWeekEnd = date('Y-m-d', strtotime('sunday this week'));

// Get weekly payments data
$weeklyPayments = $paymentController->getWeeklyPayments($currentWeekStart, $currentWeekEnd);
$totalPayments = array_sum(array_column($weeklyPayments, 'amount'));

// Get payment type distribution
$paymentTypes = $paymentController->getPaymentTypeDistribution($currentWeekStart, $currentWeekEnd);
$paymentStatus = $paymentController->getPaymentStatusDistribution($currentWeekStart, $currentWeekEnd);

// Get weekly announcements data
$weeklyAnnouncements = $announcementController->getWeeklyAnnouncements($currentWeekStart, $currentWeekEnd);
$totalAnnouncements = count($weeklyAnnouncements);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Reports - Urban Council Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            --card-gradient-1: linear-gradient(135deg, #7C4585 0%, #C95792 100%);
            --card-gradient-2: linear-gradient(135deg, #F8B55F 0%, #C95792 100%);
            --card-gradient-3: linear-gradient(135deg, #3D365C 0%, #7C4585 100%);
            --card-gradient-4: linear-gradient(135deg, #C95792 0%, #F8B55F 100%);
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
        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid var(--accent);
            padding: 3px;
            margin-bottom: 1rem;
            object-fit: cover;
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
        .nav-item.mt-4 {
            margin-top: 2rem;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width));
            transition: var(--transition);
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
            }
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
        }
        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(61, 54, 92, 0.10);
            margin-bottom: 1.5rem;
            transition: box-shadow 0.3s, transform 0.3s;
            background: white;
        }
        .card:hover {
            box-shadow: 0 8px 32px rgba(61, 54, 92, 0.18);
            transform: translateY(-4px) scale(1.01);
        }
        .card-header {
            background: var(--gradient-primary);
            color: #fff;
            border-bottom: none;
            padding: 1.5rem 1.5rem 1rem 1.5rem;
            border-radius: 18px 18px 0 0 !important;
        }
        .card-header h5, .card-header .card-title {
            color: #fff !important;
        }
        .card-title {
            font-weight: 600;
            color: var(--primary);
        }
        /* Enhanced summary cards */
        .summary-card {
            background: var(--card-gradient-1);
            color: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(124, 69, 133, 0.10);
            padding: 2rem 1.5rem;
            margin-bottom: 1.5rem;
            transition: box-shadow 0.3s, transform 0.3s;
            position: relative;
            overflow: hidden;
        }
        .summary-card .icon {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            font-size: 2.5rem;
            opacity: 0.18;
        }
        .summary-card .main-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .summary-card .label {
            font-size: 1.1rem;
            font-weight: 500;
            opacity: 0.9;
        }
        .summary-card.payments { background: var(--card-gradient-1); }
        .summary-card.announcements { background: var(--card-gradient-2); }
        /* Chart containers */
        .chart-container {
            position: relative;
            height: 320px;
            margin-bottom: 1rem;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(61, 54, 92, 0.07);
            padding: 1.5rem 1rem 1rem 1rem;
        }
        /* Table improvements */
        .table {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
        }
        .table thead th {
            background: var(--gradient-primary);
            color: #fff;
            border: none;
        }
        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: #f8f6ff;
        }
        .table-hover tbody tr:hover {
            background: var(--gradient-accent);
            color: #fff;
        }
        .badge.bg-success {
            background: linear-gradient(135deg, #28a745, #218838) !important;
            color: #fff;
        }
        .badge.bg-danger {
            background: linear-gradient(135deg, #dc3545, #c82333) !important;
            color: #fff;
        }
        .badge.bg-warning {
            background: linear-gradient(135deg, #F8B55F, #C95792) !important;
            color: #fff;
        }
        .badge.bg-info {
            background: linear-gradient(135deg, #36b9cc, #7C4585) !important;
            color: #fff;
        }
        .badge.bg-secondary {
            background: linear-gradient(135deg, #6c757d, #3D365C) !important;
            color: #fff;
        }
        .badge.bg-light {
            background: #f5f5f5 !important;
            color: #6c757d;
        }
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
            }
            .summary-card {
                padding: 1.2rem 1rem;
            }
            .chart-container {
                padding: 1rem 0.5rem 0.5rem 0.5rem;
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
                    $profilePicture = isset($_SESSION['profile_picture']) && !empty($_SESSION['profile_picture']) 
                        ? '/urban2/uploads/profile_pictures/' . $_SESSION['profile_picture'] 
                        : '/urban2/assets/images/default-avatar.png';
                    echo $profilePicture;
                ?>" 
                     alt="Profile Image" class="profile-picture">
                <h5 class="text-white mb-1">
                    <?php 
                    if (isset($staff) && isset($staff['first_name']) && isset($staff['last_name'])) {
                        echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']);
                    } else {
                        echo 'IT Staff';
                    }
                    ?>
                </h5>
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
                    <a class="nav-link" href="/urban2/views/admin/it_dashboard.php">
                        <i class='bx bxs-credit-card'></i> Payments
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
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/urban2/views/admin/analytics.php">
                        <i class='bx bxs-report'></i> Analytics
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">Analytics Reports</h1>
                </div>

                <!-- Weekly Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="summary-card payments">
                            <div class="icon"><i class='bx bxs-credit-card'></i></div>
                            <div class="main-value"><?php echo number_format($totalPayments, 2); ?></div>
                            <div class="label">Total Payments (This Week)</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="summary-card announcements">
                            <div class="icon"><i class='bx bxs-megaphone'></i></div>
                            <div class="main-value"><?php echo $totalAnnouncements; ?></div>
                            <div class="label">Total Announcements (This Week)</div>
                        </div>
                    </div>
                </div>

                <!-- Payment Analytics -->
                <div class="row">
                    <!-- Payment Type Distribution -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Payment Type Distribution</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="paymentTypeChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Payment Status Distribution -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Payment Status Distribution</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="paymentStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Analytics -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Weekly Payment Trend</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="paymentTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Weekly Announcement Trend</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="announcementTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Reports -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Weekly Payments Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Amount</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($weeklyPayments as $payment): ?>
                                            <tr>
                                                <td><?php echo isset($payment['date']) ? date('Y-m-d', strtotime($payment['date'])) : 'N/A'; ?></td>
                                                <td><?php echo isset($payment['amount']) ? number_format($payment['amount'], 2) : '0.00'; ?></td>
                                                <td><?php echo isset($payment['payment_type']) ? ucfirst($payment['payment_type']) : 'N/A'; ?></td>
                                                <td>
                                                    <?php if (isset($payment['payment_status'])): ?>
                                                    <span class="badge bg-<?php 
                                                        switch($payment['payment_status']) {
                                                            case 'completed':
                                                                echo 'success';
                                                                break;
                                                            case 'pending':
                                                                echo 'warning';
                                                                break;
                                                            case 'failed':
                                                                echo 'danger';
                                                                break;
                                                            case 'refunded':
                                                                echo 'info';
                                                                break;
                                                            case 'under_review':
                                                                echo 'secondary';
                                                                break;
                                                            default:
                                                                echo 'light';
                                                        }
                                                    ?>">
                                                        <?php echo ucfirst($payment['payment_status']); ?>
                                                    </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo isset($payment['count']) ? $payment['count'] : '0'; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Weekly Announcements Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Title</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($weeklyAnnouncements as $announcement): ?>
                                            <tr>
                                                <td><?php echo isset($announcement['created_at']) ? date('Y-m-d', strtotime($announcement['created_at'])) : 'N/A'; ?></td>
                                                <td><?php echo isset($announcement['title']) ? htmlspecialchars($announcement['title']) : 'N/A'; ?></td>
                                                <td>
                                                    <?php
                                                    if (isset($announcement['expiry_datetime'])) {
                                                        if ($announcement['expiry_datetime'] === null) {
                                                            echo '<span class="badge bg-success">Active</span>';
                                                        } else {
                                                            $expiry = strtotime($announcement['expiry_datetime']);
                                                            if ($expiry > time()) {
                                                                echo '<span class="badge bg-success">Active</span>';
                                                            } else {
                                                                echo '<span class="badge bg-danger">Expired</span>';
                                                            }
                                                        }
                                                    } else {
                                                        echo '<span class="badge bg-light">N/A</span>';
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Payment Type Distribution Chart
        const paymentTypeCtx = document.getElementById('paymentTypeChart').getContext('2d');
        new Chart(paymentTypeCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($paymentTypes, 'payment_type')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($paymentTypes, 'count')); ?>,
                    backgroundColor: [
                        getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#C95792',
                        getComputedStyle(document.documentElement).getPropertyValue('--highlight').trim() || '#F8B55F',
                        getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#3D365C',
                        getComputedStyle(document.documentElement).getPropertyValue('--secondary').trim() || '#7C4585'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Payment Status Distribution Chart
        const paymentStatusCtx = document.getElementById('paymentStatusChart').getContext('2d');
        new Chart(paymentStatusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($paymentStatus, 'payment_status')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($paymentStatus, 'count')); ?>,
                    backgroundColor: [
                        getComputedStyle(document.documentElement).getPropertyValue('--highlight').trim() || '#F8B55F', // pending
                        getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#C95792',   // failed
                        getComputedStyle(document.documentElement).getPropertyValue('--secondary').trim() || '#7C4585', // refunded
                        getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#3D365C', // under_review
                        '#b2b2b2' // fallback for extra
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Weekly Payment Trend Chart
        const paymentCtx = document.getElementById('paymentTrendChart').getContext('2d');
        new Chart(paymentCtx, {
            type: 'line',
            data: {
                labels: <?php 
                    $dates = array_map(function($payment) {
                        return date('D', strtotime($payment['date']));
                    }, $weeklyPayments);
                    echo json_encode(array_values(array_unique($dates))); 
                ?>,
                datasets: [{
                    label: 'Daily Payments',
                    data: <?php 
                        $amounts = array_map(function($payment) {
                            return $payment['amount'];
                        }, $weeklyPayments);
                        echo json_encode($amounts); 
                    ?>,
                    borderColor: getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#C95792',
                    backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#C95792',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount'
                        }
                    }
                }
            }
        });

        // Weekly Announcement Trend Chart
        const announcementCtx = document.getElementById('announcementTrendChart').getContext('2d');
        new Chart(announcementCtx, {
            type: 'bar',
            data: {
                labels: <?php 
                    $dates = array_map(function($announcement) {
                        return date('D', strtotime($announcement['created_at']));
                    }, $weeklyAnnouncements);
                    echo json_encode(array_values(array_unique($dates))); 
                ?>,
                datasets: [{
                    label: 'Daily Announcements',
                    data: <?php 
                        $announcementCounts = array_count_values(array_map(function($announcement) {
                            return date('D', strtotime($announcement['created_at']));
                        }, $weeklyAnnouncements));
                        echo json_encode(array_values($announcementCounts)); 
                    ?>,
                    backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--highlight').trim() || '#F8B55F',
                    borderColor: getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#C95792',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Announcements'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 