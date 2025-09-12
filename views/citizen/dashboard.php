<?php
session_start();
require_once '../../controllers/CitizenController.php';
require_once '../../controllers/LayoutController.php';
require_once '../../controllers/ComplaintController.php';
require_once '../../controllers/DashboardController.php';

// Check if user is logged in and is a citizen
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'citizen') {
    header('Location: /urban2/login.php');
    exit();
}

$citizenController = new CitizenController();
$layoutController = new LayoutController();
$complaintController = new ComplaintController();
$dashboardController = new DashboardController();

// Get citizen information
$citizenInfo = $citizenController->getCitizenInfo($_SESSION['user_id']);

// Get analytics data
$complaintsAnalytics = $dashboardController->getComplaintsAnalytics($_SESSION['user_id']);
$resolvedComplaintsAnalytics = $dashboardController->getResolvedComplaintsAnalytics($_SESSION['user_id']);
$bookingsAnalytics = $dashboardController->getBookingsAnalytics($_SESSION['user_id']);
$paymentsAnalytics = $dashboardController->getPaymentsAnalytics($_SESSION['user_id']);

// Helper function to generate sparkline data
function generateSparklineData($trend) {
    $values = array_column($trend, 'count');
    return implode(',', $values);
}

// Check if profile is complete
if (!$citizenInfo || !$citizenInfo['profile_completed']) {
    $_SESSION['error'] = "Please complete your profile setup first.";
    header('Location: /urban2/views/citizen/profile.php');
    exit();
}

// Set default values for citizen info
$citizenInfo = array_merge([
    'name' => 'Citizen',
    'profile_image' => '/urban2/assets/images/default-avatar.png',
    'area' => '',
    'address' => '',
    'unread_notifications' => 0
], $citizenInfo);

// Get complaints for the citizen
$complaints = $complaintController->getComplaintsByUserId($_SESSION['user_id']);
$unreadNotifications = $citizenInfo['unread_notifications'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizen Dashboard - Urban Council</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-sparklines/2.1.2/jquery.sparkline.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    :root {
        --sidebar-width: 250px;
        --main-bg: #0d112b;
        --card-bg: #181e3a;
        --card-pink: #f472b6;
        --card-purple: #7c3aed;
        --card-blue: #3b82f6;
        --card-orange: #f59e42;
        --card-text: #fff;
        --trend-up: #3b82f6;
        --trend-down: #f472b6;
        --trend-neutral: #334155;
        --analytics-shadow: 0 6px 24px rgba(59,59,152,0.08), 0 1.5px 4px rgba(59,59,152,0.08);
        --analytics-radius: 18px;
        --analytics-gradient: linear-gradient(135deg, #181e3a 60%, #232946 100%);
        --analytics-divider: #232946;
        --quick-action-gradient1: linear-gradient(135deg, #3b82f6 0%, #7c3aed 100%);
        --quick-action-gradient2: linear-gradient(135deg, #3b82f6 0%, #f472b6 100%);
        --quick-action-gradient3: linear-gradient(135deg, #7c3aed 0%, #f59e42 100%);
        --quick-action-gradient4: linear-gradient(135deg, #f59e42 0%, #3b82f6 100%);
    }

    body {
        background: var(--main-bg);
        min-height: 100vh;
        color: #cbd5e1;
    }

    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: var(--sidebar-width);
        background: #181e3a;
        color: white;
        padding: 1rem;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #888 #2c3e50;
        z-index: 1000;
    }

    .sidebar::-webkit-scrollbar {
        width: 8px;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .main-content {
        margin-left: var(--sidebar-width);
        padding: 2rem;
        background: transparent;
    }

    .sidebar .nav-link {
        color: rgba(255, 255, 255, 0.8);
        padding: 0.75rem 1rem;
        margin: 0.2rem 0;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .sidebar .nav-link:hover {
        color: white;
        background: rgba(255, 255, 255, 0.1);
    }

    .sidebar .nav-link.active {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }

    .sidebar .nav-link i {
        margin-right: 0.75rem;
        width: 20px;
        text-align: center;
    }

    .profile-section {
        text-align: center;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .profile-section img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        margin-bottom: 1rem;
        border: 3px solid rgba(255, 255, 255, 0.2);
    }

    .dashboard-card {
        background: var(--card-bg);
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: transform 0.2s;
        color: var(--card-text);
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
    }

    /* Quick Action Buttons Styles */
    .quick-actions {
        margin-bottom: 2rem;
    }
    
    .action-button {
        background: rgba(255,255,255,0.18);
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(34,48,86,0.12), 0 2px 8px rgba(34,48,86,0.10);
        padding: 2rem 1.5rem 1.5rem 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(.4,2,.6,1);
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        backdrop-filter: blur(8px) saturate(120%);
        -webkit-backdrop-filter: blur(8px) saturate(120%);
        border: 1.5px solid rgba(59,59,152,0.08);
        overflow: hidden;
        color: #fff;
    }
    
    .action-button:nth-child(1) { background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); color: #fff; }
    .action-button:nth-child(2) { background: var(--quick-action-gradient2); color: #fff; }
    .action-button:nth-child(3) { background: var(--quick-action-gradient3); color: #fff; }
    .action-button:nth-child(4) { background: var(--quick-action-gradient4); color: #fff; }
    .action-button:hover {
        transform: translateY(-7px) scale(1.04);
        box-shadow: 0 16px 40px rgba(59,59,152,0.18), 0 4px 16px rgba(59,59,152,0.12);
        border-color: rgba(59,59,152,0.18);
        z-index: 2;
    }
    
    .action-button i {
        font-size: 2.3rem;
        margin-bottom: 0.7rem;
        color: inherit;
        text-shadow: 0 2px 8px rgba(59,59,152,0.10);
        transition: color 0.2s;
    }
    
    .action-button .title {
        font-weight: 600;
        color: #fff;
        font-size: 1.15rem;
        letter-spacing: 0.5px;
        text-shadow: 0 1px 2px rgba(59,59,152,0.08);
    }
    
    .notification-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .sidebar {
            position: static;
            width: 100%;
            height: auto;
        }
        
        .main-content {
            margin-left: 0;
        }
        .action-button {
            padding: 1.2rem 0.7rem 1rem 0.7rem;
        }
    }

    /* Improved Dashboard Metric Cards */
    .metric-card {
        border-radius: 18px;
        box-shadow: 0 4px 24px rgba(59,59,152,0.10), 0 1.5px 4px rgba(59,59,152,0.08);
        transition: box-shadow 0.3s cubic-bezier(.4,2,.6,1), transform 0.22s cubic-bezier(.4,2,.6,1), border 0.3s;
        position: relative;
        overflow: hidden;
        border: 1.5px solid rgba(59,59,152,0.08);
        background: inherit;
        backdrop-filter: blur(6px) saturate(120%);
        -webkit-backdrop-filter: blur(6px) saturate(120%);
        animation: metricFadeIn 0.7s cubic-bezier(.4,2,.6,1);
    }
    .metric-card::before {
        content: '';
        position: absolute;
        top: -2px; left: -2px; right: -2px; bottom: -2px;
        border-radius: inherit;
        pointer-events: none;
        z-index: 2;
        border: 2px solid transparent;
        transition: border 0.4s cubic-bezier(.4,2,.6,1);
    }
    .metric-card:hover {
        box-shadow: 0 16px 40px rgba(59,59,152,0.18), 0 4px 16px rgba(59,59,152,0.12);
        transform: translateY(-7px) scale(1.025);
        border-color: rgba(59,59,152,0.18);
    }
    .metric-card:hover::before {
        border: 2.5px solid #F97F51;
        box-shadow: 0 0 16px 2px rgba(249,127,81,0.10);
    }
    .metric-card .metric-icon {
        font-size: 2.5rem;
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        background: rgba(255,255,255,0.22);
        color: inherit;
        box-shadow: 0 2px 8px rgba(59,59,152,0.10);
        margin-bottom: 0.5rem;
        transition: background 0.2s, box-shadow 0.2s;
    }
    .metric-card:hover .metric-icon {
        background: rgba(249,127,81,0.18);
        box-shadow: 0 4px 16px rgba(249,127,81,0.10);
    }
    .metric-card .card-title, .metric-card .h2, .metric-card .trend-indicator {
        animation: metricFadeIn 0.8s cubic-bezier(.4,2,.6,1);
    }
    @keyframes metricFadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .metric-card.card-pink { background: linear-gradient(135deg, #f472b6, #fda4af); }
    .metric-card.card-purple { background: linear-gradient(135deg, #7c3aed, #818cf8); }
    .metric-card.card-blue { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
    .metric-card.card-orange { background: linear-gradient(135deg, #f59e42, #fbbf24); color: #fff; }
    .card-yellow .metric-icon { background: rgba(255,255,255,0.25); color: #5F6366; }
    .trend-indicator { font-size: 0.875rem; padding: 0.25rem 0.5rem; border-radius: 12px; background: var(--trend-neutral); color: #5F6366; }
    .trend-indicator.trend-up { background: #d4edda; color: var(--trend-up); }
    .trend-indicator.trend-down { background: #f8d7da; color: var(--trend-down); }
    .trend-indicator.trend-neutral { background: var(--trend-neutral); color: #5F6366; }
    .sparkline-container {
        height: 50px;
        margin-top: 1rem;
    }
    
    /* Theme colors */
    .metallic-theme { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .modern-theme { background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%); }
    .calm-theme { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .energetic-theme { background: linear-gradient(135deg, #f83600 0%, #f9d423 100%); }
    .quick-actions-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #fff;
        letter-spacing: 0.5px;
    }

    /* Enhanced Analytics Card Styles */
    .analytics-card {
        box-shadow: none;
        border-radius: 18px;
        background: #232946;
        border: 2px solid #3b82f6;
        padding: 1.5rem 1.5rem 1.5rem 1.5rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    .analytics-card .card-header {
        background: #3b82f6;
        color: #fff;
        border-radius: 14px 14px 0 0;
        border: none;
        padding: 1rem 1.5rem;
        box-shadow: none;
    }
    .analytics-card .card-title {
        color: #fff;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-shadow: none;
    }
    .analytics-card .card-body {
        background: #181e3a;
        border-radius: 0 0 14px 14px;
        padding: 2rem 1rem 1rem 1rem;
        box-shadow: none;
    }
    /* Chart color accents */
    #complaintStatusChart, #complaintsChart, #paymentTypeChart {
        background: #e0e7ef;
        border-radius: 12px;
        box-shadow: none;
    }
    /* Divider between side-by-side analytics cards */
    @media (min-width: 992px) {
        .dashboard-analytics-row {
            display: flex;
            flex-direction: row;
            gap: 2.5rem;
            flex-wrap: nowrap;
            overflow-x: auto;
            margin-left: 0;
            margin-right: 0;
        }
        .dashboard-analytics-row > .col-md-6 {
            min-width: 400px;
            flex: 1 1 0;
            max-width: 50%;
        }
        @media (max-width: 900px) {
            .dashboard-analytics-row > .col-md-6 {
                min-width: 350px;
            }
        }
        @media (max-width: 700px) {
            .dashboard-analytics-row > .col-md-6 {
                min-width: 300px;
            }
        }
        @media (max-width: 600px) {
            .dashboard-analytics-row > .col-md-6 {
                min-width: 260px;
            }
        }
    }
    /* Chart.js legend and tooltip improvements */
    .chartjs-render-monitor + .chartjs-legend {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        margin-top: 1.2rem;
        gap: 1.2rem;
    }
    .chartjs-legend li {
        display: flex;
        align-items: center;
        font-size: 1rem;
        color: #3B3B98;
        font-weight: 500;
        margin: 0 0.5rem;
        transition: color 0.2s;
    }
    .chartjs-legend li:hover {
        color: #F97F51;
    }
    .chartjs-legend span {
        display: inline-block;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        margin-right: 0.5rem;
        border: 2px solid #e0e7ef;
        box-shadow: 0 1px 2px rgba(59,59,152,0.08);
        transition: border 0.2s;
    }
    .chartjs-legend li:hover span {
        border: 2px solid #F97F51;
    }
    /* Chart.js tooltip custom style (if using external tooltips) */
    .chartjs-tooltip {
        background: #fff;
        color: #3B3B98;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(59,59,152,0.10);
        padding: 0.75rem 1.25rem;
        font-size: 1rem;
        font-weight: 500;
        pointer-events: none;
        z-index: 100;
        animation: fadeIn 0.25s cubic-bezier(.4,2,.6,1);
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @media (max-width: 768px) {
        .analytics-card {
            padding: 1rem 0.5rem 1rem 0.5rem;
        }
        .analytics-card .card-header {
            padding: 0.75rem 1rem;
        }
        .analytics-card .card-body {
            padding: 1rem 0.5rem 0.5rem 0.5rem;
        }
        .dashboard-analytics-row {
            flex-direction: column;
            gap: 1.5rem;
        }
        .dashboard-analytics-row > .col-md-6:first-child {
            border-right: none;
        }
    }
    .welcome-card {
        background: linear-gradient(90deg, #f8fafc 60%, #e0e7ef 100%);
        border-radius: 18px;
        box-shadow: 0 4px 18px rgba(59,59,152,0.10), 0 1.5px 4px rgba(59,59,152,0.06);
        padding: 2.2rem 2rem 1.5rem 2rem;
        display: flex;
        align-items: center;
        margin-bottom: 2rem;
        border: none;
        position: relative;
        overflow: hidden;
        animation: fadeInWelcome 0.7s cubic-bezier(.4,2,.6,1);
    }
    .welcome-content {
        display: flex;
        align-items: center;
        gap: 1.2rem;
    }
    .wave-emoji {
        font-size: 2.5rem;
        animation: waveHand 1.5s infinite;
        display: inline-block;
    }
    .welcome-text {
        font-size: 2.1rem;
        font-weight: 700;
        color: #223056;
        letter-spacing: 0.5px;
        text-shadow: 0 1px 2px rgba(59,59,152,0.08);
    }
    @keyframes waveHand {
        0% { transform: rotate(0deg); }
        10% { transform: rotate(14deg); }
        20% { transform: rotate(-8deg); }
        30% { transform: rotate(14deg); }
        40% { transform: rotate(-4deg); }
        50% { transform: rotate(10deg); }
        60% { transform: rotate(0deg); }
        100% { transform: rotate(0deg); }
    }
    @keyframes fadeInWelcome {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @media (max-width: 768px) {
        .welcome-card { padding: 1.2rem 0.7rem 1rem 0.7rem; }
        .welcome-text { font-size: 1.3rem; }
        .wave-emoji { font-size: 1.5rem; }
    }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="profile-section">
            <img src="<?php echo $citizenInfo['profile_image'] ?? '/urban2/assets/images/default-avatar.png'; ?>" 
                 alt="Profile Image">
            <h5 class="text-white mb-1"><?php echo htmlspecialchars($citizenInfo['name']); ?></h5>
            <p class="text-muted mb-0">Citizen</p>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="/urban2/index.php">
                    <i class="fas fa-home"></i> Home
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="/urban2/views/citizen/dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/urban2/views/citizen/profile.php">
                    <i class="fas fa-user"></i> Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/urban2/views/citizen/recent-activities.php">
                    <i class="fas fa-history"></i> Recent Activities
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="/urban2/views/citizen/notifications.php">
                    <i class="fas fa-bell"></i> Notifications
                    <?php if (isset($citizenInfo['unread_notifications']) && $citizenInfo['unread_notifications'] > 0): ?>
                        <span class="badge bg-danger ms-2"><?php echo $citizenInfo['unread_notifications']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/urban2/views/citizen/requests.php">
                    <i class="fas fa-cog"></i> Active Requests
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link text-danger" href="/urban2/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Modern Welcome Message -->
            <div class="welcome-card mb-4">
                <div class="welcome-content">
                    <span class="wave-emoji">👋</span>
                    <span class="welcome-text">Welcome, <strong><?php echo htmlspecialchars($citizenInfo['name']); ?></strong>!</span>
                </div>
            </div>
            
            <!-- Quick Action Buttons -->
            <h4 class="quick-actions-title">Quick Actions</h4>
            <div class="row quick-actions">
                <div class="col-md-3 mb-3">
                    <div class="action-button" onclick="location.href='/urban2/views/citizen/complaints.php?action=new'">
                        <i class="fas fa-exclamation-circle"></i>
                        <div class="title">Submit Complaint</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="action-button" onclick="location.href='/urban2/views/citizen/garbage-schedule.php'">
                        <i class="fas fa-truck"></i>
                        <div class="title">Schedule Collection</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="action-button" onclick="location.href='/urban2/views/citizen/booking.php'">
                        <i class="fas fa-calendar-plus"></i>
                        <div class="title">Schedule Booking</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="action-button" onclick="location.href='/urban2/views/citizen/payments.php'">
                        <i class="fas fa-money-bill-wave"></i>
                        <div class="title">Make Payment</div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Recent Complaints -->
                <!-- REMOVE THIS CARD -->
                <!-- <div class="col-md-6 col-lg-4">
                    <div class="dashboard-card">
                        <h4><i class="fas fa-history"></i> Recent Complaints</h4>
                        ...
                                        </div>
                </div> -->

                <!-- Upcoming Collections -->
                <!-- REMOVE THIS CARD -->
                <!-- <div class="col-md-6 col-lg-4">
                    <div class="dashboard-card">
                        <h4><i class="fas fa-truck"></i> Upcoming Collections</h4>
                        ...
                                        </div>
                </div> -->
            </div>

            <!-- Analytics Cards Row -->
            <div class="row g-4 mb-4">
                <!-- Total Complaints Card -->
                <div class="col-md-3">
                    <div class="card metric-card card-pink h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="metric-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="trend-indicator <?php echo $complaintsAnalytics['percentage_change'] > 0 ? 'trend-up' : ($complaintsAnalytics['percentage_change'] < 0 ? 'trend-down' : 'trend-neutral'); ?>">
                                    <?php echo $complaintsAnalytics['percentage_change'] > 0 ? '+' : ''; ?><?php echo $complaintsAnalytics['percentage_change']; ?>%
                                </div>
                            </div>
                            <h3 class="card-title h5 mb-0">Total Complaints</h3>
                            <div class="h2 mb-0"><?php echo $complaintsAnalytics['total']; ?></div>
                            <div class="sparkline-container">
                                <div class="complaints-sparkline" values="<?php echo generateSparklineData($complaintsAnalytics['trend']); ?>"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resolved Complaints Card -->
                <div class="col-md-3">
                    <div class="card metric-card card-purple h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="metric-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="trend-indicator <?php echo $resolvedComplaintsAnalytics['resolution_rate'] >= 70 ? 'trend-up' : ($resolvedComplaintsAnalytics['resolution_rate'] >= 40 ? 'trend-neutral' : 'trend-down'); ?>">
                                    <?php echo $resolvedComplaintsAnalytics['resolution_rate']; ?>%
                                </div>
                            </div>
                            <h3 class="card-title h5 mb-0">Resolved Complaints</h3>
                            <div class="h2 mb-0"><?php echo $resolvedComplaintsAnalytics['total']; ?></div>
                            <div class="sparkline-container">
                                <div class="resolved-sparkline" values="<?php echo generateSparklineData($resolvedComplaintsAnalytics['trend']); ?>"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Bookings Card -->
                <div class="col-md-3">
                    <div class="card metric-card card-blue h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="metric-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="trend-indicator <?php echo $bookingsAnalytics['percentage_change'] > 0 ? 'trend-up' : ($bookingsAnalytics['percentage_change'] < 0 ? 'trend-down' : 'trend-neutral'); ?>">
                                    <?php echo $bookingsAnalytics['percentage_change'] > 0 ? '+' : ''; ?><?php echo $bookingsAnalytics['percentage_change']; ?>%
                                </div>
                            </div>
                            <h3 class="card-title h5 mb-0">Total Bookings</h3>
                            <div class="h2 mb-0"><?php echo $bookingsAnalytics['total']; ?></div>
                            <div class="sparkline-container">
                                <div class="bookings-sparkline" values="<?php echo generateSparklineData($bookingsAnalytics['trend']); ?>"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Payments Card -->
                <div class="col-md-3">
                    <div class="card metric-card card-orange h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="metric-icon">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div class="trend-indicator <?php echo $paymentsAnalytics['percentage_change'] > 0 ? 'trend-up' : ($paymentsAnalytics['percentage_change'] < 0 ? 'trend-down' : 'trend-neutral'); ?>">
                                    <?php echo $paymentsAnalytics['percentage_change'] > 0 ? '+' : ''; ?><?php echo $paymentsAnalytics['percentage_change']; ?>%
                                </div>
                            </div>
                            <h3 class="card-title h5 mb-0">Total Payments</h3>
                            <div class="h2 mb-0"><?php echo $paymentsAnalytics['total']; ?></div>
                            <div class="sparkline-container">
                                <div class="payments-sparkline" values="<?php echo generateSparklineData($paymentsAnalytics['trend']); ?>"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="analytics-card card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Complaints Analytics</h5>
                            <div class="d-flex align-items-center">
                                <select id="timeframeSelect" class="form-select form-select-sm me-2" style="width: auto;">
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly" selected>Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="chartLoading" class="text-center d-none">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div id="chartError" class="alert alert-danger d-none">
                                Failed to load chart data. Please try again.
                            </div>
                            <canvas id="complaintsChart" style="width: 100%; height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Complaint Status Distribution & Payment Type Breakdown Side by Side -->
            <div class="row mb-4 dashboard-analytics-row">
                <div class="col-md-6">
                    <div class="analytics-card card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Complaint Status Distribution</h5>
                        </div>
                        <div class="card-body">
                            <div style="position:relative; min-height:320px;">
                                <canvas id="complaintStatusChart" style="max-width: 400px; max-height: 320px;"></canvas>
                                <div id="complaintStatusCenterText" style="position:absolute; left:0; top:0; width:100%; height:100%; display:flex; align-items:center; justify-content:center; pointer-events:none; font-size:1.5rem; font-weight:600; color:#223A5E;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="analytics-card card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Payment Type Breakdown</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="paymentTypeChart" style="width:100%; min-height:320px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        // Wait for sparkline plugin to load
        setTimeout(function() {
            // Initialize sparklines
            $('.complaints-sparkline').sparkline('html', {
                type: 'line',
                width: '100%',
                height: '50px',
                lineColor: '#3b82f6',
                fillColor: 'rgba(59, 130, 246, 0.2)',
                spotColor: undefined,
                minSpotColor: undefined,
                maxSpotColor: undefined,
                highlightSpotColor: undefined,
                highlightLineColor: undefined
            });

            $('.resolved-sparkline').sparkline('html', {
                type: 'line',
                width: '100%',
                height: '50px',
                lineColor: '#7c3aed',
                fillColor: 'rgba(124, 58, 237, 0.2)',
                spotColor: undefined,
                minSpotColor: undefined,
                maxSpotColor: undefined,
                highlightSpotColor: undefined,
                highlightLineColor: undefined
            });

            $('.bookings-sparkline').sparkline('html', {
                type: 'line',
                width: '100%',
                height: '50px',
                lineColor: '#f472b6',
                fillColor: 'rgba(244, 114, 182, 0.2)',
                spotColor: undefined,
                minSpotColor: undefined,
                maxSpotColor: undefined,
                highlightSpotColor: undefined,
                highlightLineColor: undefined
            });

            $('.payments-sparkline').sparkline('html', {
                type: 'line',
                width: '100%',
                height: '50px',
                lineColor: '#f59e42',
                fillColor: 'rgba(245, 158, 66, 0.2)',
                spotColor: undefined,
                minSpotColor: undefined,
                maxSpotColor: undefined,
                highlightSpotColor: undefined,
                highlightLineColor: undefined
            });
        }, 1000);
    });

    document.addEventListener('DOMContentLoaded', function() {
        let complaintsChart;
        const ctx = document.getElementById('complaintsChart').getContext('2d');
        
        // Function to format dates based on timeframe
        function formatDate(date, timeframe) {
            const d = new Date(date);
            switch(timeframe) {
                case 'weekly':
                    return `Week of ${d.toLocaleDateString()}`;
                case 'yearly':
                    return d.getFullYear();
                default: // monthly
                    return d.toLocaleDateString('default', { month: 'long', year: 'numeric' });
            }
        }

        // Function to create gradient
        function createGradient(ctx) {
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)'); // blue
            gradient.addColorStop(1, 'rgba(24, 30, 58, 0)'); // card background
            return gradient;
        }

        // Function to update chart
        function updateChart(timeframe) {
            fetch(`/urban2/api/get_complaint_statistics.php?timeframe=${timeframe}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Chart data:', data);
                    
                    if (data.status === 'success' && data.data && data.data.length > 0) {
                        const chartData = {
                            labels: data.data.map(item => formatDate(item.date, timeframe)),
                            datasets: [{
                                label: 'Complaints',
                                data: data.data.map(item => item.count),
                                borderColor: '#3b82f6', // blue
                                backgroundColor: createGradient(ctx),
                                pointBackgroundColor: '#f472b6', // pink
                                pointBorderColor: '#3b82f6',
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                tension: 0.4,
                                fill: true
                            }]
                        };

                        const config = {
                            type: 'line',
                            data: chartData,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const dataPoint = data.data[context.dataIndex];
                                                let label = `Complaints: ${context.parsed.y}`;
                                                if (dataPoint.percentChange !== null) {
                                                    const changeSymbol = dataPoint.percentChange >= 0 ? '↑' : '↓';
                                                    const changeColor = dataPoint.percentChange >= 0 ? '🔴' : '🟢';
                                                    label += `\n${changeColor} ${Math.abs(dataPoint.percentChange)}% ${changeSymbol}`;
                                                }
                                                return label;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                }
                            }
                        };

                        if (complaintsChart) {
                            complaintsChart.destroy();
                        }
                        complaintsChart = new Chart(ctx, config);
                    } else {
                        document.getElementById('chartError').classList.remove('d-none');
                    }
                })
                .catch(error => {
                    console.error('Error fetching complaint statistics:', error);
                    document.getElementById('chartError').classList.remove('d-none');
                });
        }

        // Initialize chart with monthly data
        updateChart('monthly');

        // Handle timeframe changes
        document.getElementById('timeframeSelect').addEventListener('change', function(e) {
            updateChart(e.target.value);
        });

        // Complaint Status Distribution Doughnut Chart
        const statusColors = {
            'Resolved': '#3b82f6', // Blue
            'In Process': '#7c3aed', // Purple
            'Pending': '#f472b6', // Pink
            'Closed': '#f59e42' // Orange
        };
        // Fetch status distribution data from API
        fetch('/urban2/api/get_complaint_status_distribution.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.data) {
                    const statusLabels = Object.keys(data.data);
                    const statusCounts = statusLabels.map(label => data.data[label]);
                    const total = statusCounts.reduce((a, b) => a + b, 0);
                    const statusColorsArr = statusLabels.map(label => statusColors[label] || '#ccc');
                    // Center text update
                    function updateCenterText(chart, total) {
                        const centerText = document.getElementById('complaintStatusCenterText');
                        centerText.textContent = total + ' Total';
                    }
                    // Chart.js Doughnut Chart
                    const ctxStatus = document.getElementById('complaintStatusChart').getContext('2d');
                    const complaintStatusChart = new Chart(ctxStatus, {
                        type: 'doughnut',
                        data: {
                            labels: statusLabels,
                            datasets: [{
                                data: statusCounts,
                                backgroundColor: statusColorsArr,
                                borderWidth: 2,
                                borderColor: '#fff',
                                hoverOffset: 16
                            }]
                        },
                        options: {
                            responsive: true,
                            cutout: '70%',
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20,
                                        font: { size: 15 }
                                    },
                                    onClick: function(e, legendItem, legend) {
                                        const index = legendItem.index;
                                        const ci = legend.chart;
                                        const meta = ci.getDatasetMeta(0);
                                        meta.data[index].hidden = !meta.data[index].hidden;
                                        ci.update();
                                        // Update center text
                                        const visibleCounts = meta.data.map((d, i) => d.hidden ? 0 : statusCounts[i]);
                                        updateCenterText(ci, visibleCounts.reduce((a, b) => a + b, 0));
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const count = context.parsed;
                                            const percent = ((count / total) * 100).toFixed(1);
                                            return `${context.label}: ${count} (${percent}%)`;
                                        }
                                    }
                                }
                            },
                            animation: {
                                animateScale: true,
                                animateRotate: true
                            },
                            onHover: function(e, activeEls) {
                                e.native.target.style.cursor = activeEls.length ? 'pointer' : 'default';
                            }
                        },
                        plugins: [{
                            id: 'centerText',
                            afterDraw: function(chart) {
                                updateCenterText(chart, total);
                            }
                        }]
                    });
                }
            });

        // Payment Type Breakdown Stacked Bar Chart
        const paymentTypeColors = {
            'schedule booking': '#3b82f6', // Blue
            'schedule collection': '#f59e42', // Orange
            'others': '#7c3aed' // Purple
        };
        fetch('/urban2/api/get_payment_type_breakdown.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.data) {
                    // data.data example: { labels: ['Jan', 'Feb'], datasets: { 'schedule booking': [10,20], ... } }
                    const labels = data.data.labels;
                    const datasetKeys = Object.keys(data.data.datasets);
                    const datasets = datasetKeys.map(key => ({
                        label: key,
                        data: data.data.datasets[key],
                        backgroundColor: paymentTypeColors[key] || '#ccc',
                        stack: 'Stack 0',
                        borderWidth: 1
                    }));
                    // Calculate totals for %
                    const totals = labels.map((_, i) => datasetKeys.reduce((sum, key) => sum + data.data.datasets[key][i], 0));
                    const ctxPay = document.getElementById('paymentTypeChart').getContext('2d');
                    new Chart(ctxPay, {
                        type: 'bar',
                        data: { labels, datasets },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    onClick: Chart.defaults.plugins.legend.onClick
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const value = context.parsed.x;
                                            const total = totals[context.dataIndex];
                                            const percent = total ? ((value / total) * 100).toFixed(1) : 0;
                                            return `${context.dataset.label}: ${value} (${percent}%)`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    min: 0,
                                    max: 100,
                                    title: { display: true, text: '% Usage' },
                                    ticks: {
                                        callback: function(value) { return value + '%'; }
                                    }
                                },
                                y: {
                                    stacked: true,
                                    title: { display: true, text: 'Type' }
                                }
                            },
                            animation: {
                                animateScale: true,
                                animateRotate: true
                            }
                        }
                    });
                }
        });
    });
</script>
</body>
</html>