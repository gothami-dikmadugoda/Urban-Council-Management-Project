<?php
session_start();
require_once __DIR__ . '/../../controllers/AdminController.php';
require_once __DIR__ . '/../../controllers/AnalyticsController.php';

$adminController = new AdminController();
$analyticsController = new AnalyticsController();

$adminController->validateAdminAccess();

$dashboardData = $adminController->getDashboardData();
$analyticsData = $analyticsController->getDashboardData();

$stats = $dashboardData['stats'];
$staffList = $dashboardData['staff_list'];
$recentActivities = $dashboardData['recent_activities'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Urban Council Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #0091D5;
            --secondary-color: #1C4E80;
            --success-color: #A5D8DD;
            --warning-color:rgb(253, 51, 165);
            --info-color: #0091D5;
            --dark-color: #202020;
            --light-color: #F1F1F1;
            --gray-color: #7E909A;
            --border-radius: 15px;
            --box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
            --gradient-primary: linear-gradient(135deg, #1C4E80, #0091D5);
            --gradient-success: linear-gradient(135deg,rgba(165, 216, 221, 0.32), #0091D5);
            --gradient-warning: linear-gradient(135deg,rgb(255, 53, 208),rgb(255, 44, 213));
            --gradient-info: linear-gradient(135deg, #0091D5, #1C4E80);
            --card-bg: #202020;
            --text-light: #F1F1F1;
            --text-gray: #7E909A;
        }

        /* Enhanced Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { transform: translateX(-100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        /* Enhanced Body Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #151921;
            color: var(--text-light);
            animation: fadeIn 0.5s ease-out;
        }

        /* Enhanced Sidebar */
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
            animation: slideIn 0.5s ease-out;
        }

        .sidebar-header {
            padding: 1rem 0;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            animation: fadeIn 0.8s ease-out;
        }

        .sidebar-header h3 {
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9);
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 0.5rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(-100%);
            transition: var(--transition);
        }

        .nav-link:hover::before {
            transform: translateX(0);
        }

        .nav-link i {
            margin-right: 15px;
            font-size: 1.2rem;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: var(--transition);
        }

        /* Enhanced Cards */
        .card {
            border: none;
            border-radius: var(--border-radius);
            background: linear-gradient(145deg, #1a1f2b, #202632);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(
                circle at top right,
                rgba(165, 216, 221, 0.05),
                rgba(0, 145, 213, 0.05),
                transparent 50%
            );
            pointer-events: none;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }

        .card-header {
            background: rgba(28, 78, 128, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h5 {
            font-weight: 600;
            color: var(--text-light);
            margin: 0;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
        }

        .card-body {
            background: transparent;
            padding: 1.5rem;
            position: relative;
            z-index: 1;
        }

        /* Enhanced Stats Cards */
        .stats-card {
            background: linear-gradient(145deg, #1a1f2b, #202632);
            color: var(--text-light);
            border-radius: var(--border-radius);
            padding: 1.8rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(
                circle at top right,
                rgba(0, 145, 213, 0.1),
                transparent 70%
            );
            pointer-events: none;
        }

        .stats-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                45deg,
                rgba(165, 216, 221, 0.05),
                rgba(0, 145, 213, 0.05)
            );
            pointer-events: none;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }

        .stats-card h3 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            position: relative;
            z-index: 1;
            text-shadow: 0 0 20px rgba(0, 145, 213, 0.3);
            margin-bottom: 0.5rem;
        }

        .stats-card p {
            margin: 0;
            color: var(--text-gray);
            font-size: 1.1rem;
            position: relative;
            z-index: 1;
            opacity: 0.9;
            letter-spacing: 0.5px;
        }

        .stats-card i {
            position: absolute;
            right: 1.5rem;
            bottom: 1.5rem;
            font-size: 3rem;
            opacity: 0.15;
            color: var(--text-light);
            animation: float 3s ease-in-out infinite;
        }

        /* Enhanced Buttons */
        .btn {
            border-radius: var(--border-radius);
            padding: 0.8rem 1.5rem;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(-100%);
            transition: var(--transition);
        }

        .btn:hover::before {
            transform: translateX(0);
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
        }

        /* Enhanced Activity Items */
        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            transition: var(--transition);
            animation: fadeIn 0.5s ease-out;
        }

        .activity-item:hover {
            background: rgba(0, 0, 0, 0.02);
            transform: translateX(5px);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--gradient-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            animation: pulse 2s infinite;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--dark-color);
        }

        .activity-time {
            font-size: 0.875rem;
            color: var(--gray-color);
        }

        .search-bar {
            margin-bottom: 1.5rem;
        }

        .search-bar input {
            border-radius: var(--border-radius);
            padding: 1rem 1.5rem;
            border: 2px solid #eee;
            transition: var(--transition);
        }

        .search-bar input:focus {
            border-color: var(--primary-color);
            box-shadow: none;
        }

        /* Enhanced Message Icon */
        .message-icon-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }

        .message-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--gradient-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--box-shadow);
            animation: pulse 2s infinite;
        }

        .message-icon:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
        }

        .message-icon i {
            font-size: 24px;
        }

        /* Enhanced Form Controls */
        .form-control {
            border-radius: var(--border-radius);
            padding: 0.8rem 1rem;
            border: 2px solid #eee;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
            transform: translateY(-2px);
        }

        .form-label {
            font-weight: 500;
            color: var(--gray-color);
        }

        /* Enhanced Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gradient-primary);
            border-radius: 10px;
            transition: var(--transition);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }

        /* Responsive Enhancements */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                animation: slideIn 0.5s ease-out;
            }
            .main-content {
                margin-left: 0;
                animation: fadeIn 0.5s ease-out;
            }
            .stats-card {
                margin-bottom: 1rem;
                animation: fadeIn 0.5s ease-out;
            }
        }

        /* Chart Container */
        .chart-container {
            background: linear-gradient(145deg, #1a1f2b, #202632);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            position: relative;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.2);
            min-height: 450px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Specific Chart Sizes */
        #complaintTrendsChart {
            height: 400px !important;
            width: 100% !important;
        }

        #categoryDistributionChart {
            height: 550px !important;
            width: 100% !important;
        }

        #statusDistributionChart {
            height: 300px !important;
            width: 100% !important;
        }

        /* Button Group in Cards */
        .btn-group .btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-group .btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .btn-group .btn.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        /* Chart Customization */
        canvas {
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }

        /* Modal Enhancements */
        .modal-content {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--box-shadow);
            animation: fadeIn 0.3s ease-out;
        }

        .modal-header {
            background: var(--gradient-primary);
            color: white;
            border-bottom: none;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #eee;
            padding: 1.5rem;
            background: var(--light-color);
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
                <h2>Dashboard Overview</h2>
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

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stats-card">
                        <h3><?php echo $stats['total_staff']; ?></h3>
                        <p>Total Staff Members</p>
                        <i class='bx bxs-user-detail'></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h3><?php echo $stats['total_citizens']; ?></h3>
                        <p>Total Citizens</p>
                        <i class='bx bxs-user'></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h3><?php echo $stats['total_companies']; ?></h3>
                        <p>Total Companies</p>
                        <i class='bx bxs-building'></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h3><?php echo $stats['total_staff'] + $stats['total_citizens'] + $stats['total_companies']; ?></h3>
                        <p>Total Users</p>
                        <i class='bx bxs-group'></i>
                    </div>
                </div>
            </div>

            <!-- Analytics Charts -->
            <div class="row">
                <!-- Complaint Trends Chart -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Complaint Trends & Predictions</h5>
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
                
                <!-- Category Distribution Chart -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Category Distribution</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="min-height: 600px;">
                                <canvas id="categoryDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Distribution Chart -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Status Distribution</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="statusDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Icon -->
            <div class="message-icon-container">
                <div id="message-icon" class="message-icon" onclick="window.location.href='/urban2/views/chat.php'">
                    <i class='bx bxs-message-dots' style="font-size: 24px;"></i>
                    <span id="message-count" class="message-count" style="display: none;">0</span>
                </div>
            </div>

            </div>
        </div>
    </div>

    <!-- Add Staff Modal -->
    <div class="modal fade" id="addStaffModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Staff Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addStaffForm" method="POST" action="/urban2/views/admin/add_staff.php">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select class="form-control" name="department" required>
                                <option value="">Select Department</option>
                                <option value="health">Health</option>
                                <option value="engineering">Engineering</option>
                                <option value="it">IT</option>
                                <option value="reception">Reception</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Job Role</label>
                            <select class="form-control" name="job_role" required>
                                <option value="">Select Role</option>
                                <option value="garbage_manager">Garbage Manager</option>
                                <option value="garbage_collector">Garbage Collector</option>
                                <option value="field_visitor">Field visitor</option>
                                <option value="moh_officer">MOH Officer</option>
                                <option value="engineer">Engineer</option>
                                <option value="it_officer">IT Officer</option>
                                <option value="receptionist">Receptionist</option>
                            
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="addStaffForm" class="btn btn-primary">Add Staff</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/urban2/assets/js/message-notifications.js"></script>
    <script>
        // Prepare data for charts
        const historicalData = <?php echo json_encode($analyticsData['historical']); ?>;
        const categoryData = <?php echo json_encode($analyticsData['categories']); ?>;
        const statusData = <?php echo json_encode($analyticsData['status']); ?>;

        // Debug logs
        console.log('Category Data:', categoryData);

        // Chart instances
        let complaintTrendsChart;
        let categoryDistributionChart;
        let statusDistributionChart;

        // Initialize charts
        function initializeCharts() {
            // Complaint Trends Chart
            const complaintTrendsCtx = document.getElementById('complaintTrendsChart').getContext('2d');
            complaintTrendsChart = new Chart(complaintTrendsCtx, {
                type: 'line',
                data: {
                    labels: historicalData.map(item => item.time_period),
                    datasets: [{
                        label: 'Total Complaints',
                        data: historicalData.map(item => item.total_complaints),
                        borderColor: '#0ea5e9',
                        backgroundColor: 'rgba(14, 165, 233, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Resolved Complaints',
                        data: historicalData.map(item => item.resolved_complaints),
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Predicted Complaints',
                        data: historicalData.map(item => item.is_prediction ? item.total_complaints : null),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderDash: [5, 5],
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 14,
                                    weight: '600'
                                },
                                color: '#F1F1F1'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            },
                            ticks: {
                                color: '#F1F1F1'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#F1F1F1'
                            }
                        }
                    }
                }
            });

            // Category Distribution Chart
            const categoryCtx = document.getElementById('categoryDistributionChart').getContext('2d');
            categoryDistributionChart = new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: categoryData.map(item => item.category),
                    datasets: [{
                        data: categoryData.map(item => item.total),
                        backgroundColor: [
                            '#FF69B4', // Pink
                            '#9370DB', // Purple
                            '#4169E1', // Blue
                            '#3CB371', // Green
                            '#FFFDD0', // Cream
                            '#FFD700', // Yellow
                            '#FF1493', // Deep Pink
                            '#8A2BE2', // Blue Violet
                            '#00BFFF'  // Deep Sky Blue
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: '600',
                                    color: '#F1F1F1'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#000',
                            bodyColor: '#666',
                            bodyFont: {
                                size: 14
                            },
                            titleFont: {
                                size: 16,
                                weight: '600'
                            },
                            padding: 12,
                            borderColor: 'rgba(0, 0, 0, 0.1)',
                            borderWidth: 1
                        }
                    }
                }
            });

            // Status Distribution Chart
            const statusCtx = document.getElementById('statusDistributionChart').getContext('2d');
            statusDistributionChart = new Chart(statusCtx, {
                type: 'bar',
                data: {
                    labels: statusData.map(item => item.status),
                    datasets: [{
                        data: statusData.map(item => item.count),
                        backgroundColor: [
                            '#0091D5',
                            '#1C4E80',
                            '#EA6A47',
                            '#A5D8DD'
                        ],
                        borderWidth: 0,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#000',
                            bodyColor: '#666',
                            bodyFont: {
                                size: 14
                            },
                            titleFont: {
                                size: 16,
                                weight: '600'
                            },
                            padding: 12,
                            borderColor: 'rgba(0, 0, 0, 0.1)',
                            borderWidth: 1
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 12
                                },
                                color: '#666'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 12
                                },
                                color: '#666'
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
                    complaintTrendsChart.data.datasets[2].data = data.map(item => item.is_prediction ? item.total_complaints : null);
                    complaintTrendsChart.update();
                })
                .catch(error => console.error('Error:', error));
        }

        // Staff management functions
        function editStaff(id) {
            window.location.href = `/urban2/views/admin/update_staff.php?id=${id}`;
        }

        function deleteStaff(id) {
            if (confirm('Are you sure you want to delete this staff member?')) {
                fetch(`/urban2/views/admin/delete_staff.php?id=${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting staff member');
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        function searchStaff() {
            const input = document.getElementById('staffSearch').value.toLowerCase();
            const rows = document.querySelectorAll('#staffTable tbody tr');
            rows.forEach(row => {
                const name = row.cells[0].textContent.toLowerCase();
                row.style.display = name.includes(input) ? '' : 'none';
            });
        }

        // Initialize charts when the page loads
        document.addEventListener('DOMContentLoaded', initializeCharts);
    </script>
</body>
</html>