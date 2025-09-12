<?php
session_start();
require_once '../../controllers/CitizenController.php';
require_once '../../controllers/CollectionController.php';

// Check if user is logged in and is a citizen
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'citizen') {
    header('Location: /urban2/login.php');
    exit();
}

$citizenController = new CitizenController();
$collectionController = new CollectionController();

// Get citizen information and collection requests
$citizenInfo = $citizenController->getCitizenInfo($_SESSION['user_id']);
$activeRequests = $collectionController->getUserCollectionRequests($_SESSION['user_id']);

// Debug the response
// error_log('Active Requests: ' . print_r($activeRequests, true));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Active Requests - Urban Council</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #22c55e;
            --secondary-color: #166534;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --light-bg: #f3f4f6;
            --card-bg: #16213e;
            --text-primary: #1f2937;
            --text-secondary: #4b5563;
            --sidebar-width: 250px;
            --main-bg: #0d112b;
            --sidebar-bg: #181e3a;
            --table-bg: #16213e;
            --table-header-bg: #181e3a;
            --table-header-text: #22c55e;
            --table-text: #e5e7eb;
            --heading: #fff;
            --stat-title: #22c55e;
            --stat-value: #fff;
            --btn-bg: #22c55e;
            --btn-hover: #166534;
            --btn-text: #fff;
        }

        body {
            background: var(--main-bg);
            min-height: 100vh;
            color: var(--table-text);
            font-family: 'Inter', 'Segoe UI', 'Roboto', Arial, sans-serif;
        }

        .dashboard-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 3rem 2rem 3rem 2rem;
        }

        .page-header {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2.5rem 2.5rem 2rem 2.5rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 6px 32px rgba(34,197,94,0.10), 0 2px 8px rgba(34,197,94,0.06);
        }

        .page-title {
            color: var(--heading);
            font-size: 2rem;
            font-weight: 500;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            letter-spacing: 0.5px;
        }

        .page-title i {
            color: #3B3B98;
            font-size: 2.2rem;
            font-weight: 400;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 2rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: 18px;
            padding: 2rem 1.5rem;
            box-shadow: 0 4px 24px rgba(59,59,152,0.10), 0 1.5px 4px rgba(59,59,152,0.08);
            transition: box-shadow 0.3s cubic-bezier(.4,2,.6,1), transform 0.22s cubic-bezier(.4,2,.6,1), border 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-7px) scale(1.025);
            box-shadow: 0 16px 40px rgba(59,59,152,0.18), 0 4px 16px rgba(59,59,152,0.12);
        }

        .stat-title {
            color: var(--stat-title);
            font-size: 1.05rem;
            font-weight: 400;
            margin-bottom: 0.5rem;
            letter-spacing: 0.1px;
        }

        .stat-value {
            color: var(--stat-value);
            font-size: 1.5rem;
            font-weight: 400;
        }

        .requests-card {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 6px 32px rgba(44,62,80,0.10), 0 2px 8px rgba(44,62,80,0.06);
            overflow: hidden;
            margin-bottom: 2.5rem;
        }

        .table thead th {
            background: var(--table-header-bg);
            color: var(--table-header-text);
            font-weight: 500;
            font-size: 1.13rem;
            letter-spacing: 0.5px;
            border-bottom: 2.5px solid #22325c;
            text-transform: uppercase;
            padding-top: 1.1rem;
            padding-bottom: 1.1rem;
            box-shadow: 0 2px 8px rgba(59,59,152,0.04);
            transition: background 0.18s;
        }
        .table thead th:hover {
            background: #e0e7ef;
            color: #F97F51;
        }

        .table tbody td {
            padding: 1.1rem 1rem;
            vertical-align: middle;
            color: var(--table-text);
            border-bottom: 1px solid #e2e8f0;
            font-size: 1.08rem;
        }

        .status-badge {
            padding: 0.5rem 1.1rem;
            border-radius: 2rem;
            font-weight: 400;
            font-size: 1.01rem;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            box-shadow: 0 1px 4px rgba(59,59,152,0.06);
            background: #22325c;
            color: var(--btn-text);
            border: 1.5px solid var(--primary-color);
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-completed {
            background-color: #e0e7ff;
            color: #3730a3;
        }

        .waste-type-badge {
            background-color: #22325c;
            color: var(--btn-text);
            padding: 0.45rem 1rem;
            border-radius: 2rem;
            font-size: 1.01rem;
            font-weight: 400;
            box-shadow: 0 1px 4px rgba(59,59,152,0.06);
            border: 1.5px solid var(--primary-color);
        }

        .action-btn {
            padding: 0.6rem 1.2rem;
            border-radius: 0.7rem;
            font-size: 1.08rem;
            font-weight: 400;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--btn-bg);
            color: var(--btn-text) !important;
            border: none;
            box-shadow: 0 1px 4px rgba(34,197,94,0.08);
        }

        .view-btn {
            background: var(--btn-bg);
            color: var(--btn-text) !important;
            border: none;
            box-shadow: 0 1px 4px rgba(34,197,94,0.08);
        }

        .view-btn:hover {
            background: var(--btn-hover);
            color: #fff !important;
            box-shadow: 0 4px 16px rgba(34,197,94,0.13);
        }

        .schedule-btn {
            background: var(--btn-bg);
            color: var(--btn-text) !important;
            padding: 0.85rem 1.7rem;
            border-radius: 0.7rem;
            font-weight: 400;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.08rem;
            box-shadow: 0 1px 4px rgba(34,197,94,0.08);
        }

        .schedule-btn:hover {
            background: var(--btn-hover);
            color: #fff !important;
            transform: translateY(-1px) scale(1.04);
            box-shadow: 0 4px 16px rgba(34,197,94,0.13);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 6px 32px rgba(44,62,80,0.10), 0 2px 8px rgba(44,62,80,0.06);
            margin-bottom: 2.5rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            color: var(--heading);
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: var(--table-text);
            margin-bottom: 2rem;
            font-size: 1.15rem;
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            .table-responsive {
                margin: 0 -1rem;
            }
            .dashboard-container {
                padding: 1rem;
            }
            .page-header, .requests-card, .empty-state {
                padding: 1.2rem 0.7rem 1.2rem 0.7rem;
            }
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
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
        hr {
            border-top: 2px solid var(--primary-color);
            opacity: 0.2;
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
                    <a class="nav-link" href="/urban2/views/citizen/dashboard.php">
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
                        <i class="fas fa-clipboard-list"></i> Active Requests
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/citizen/notifications.php">
                        <i class="fas fa-bell"></i> Notifications
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
            <div class="dashboard-container">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="page-title">
                            <i class="fas fa-clipboard-list"></i>
                            Your Active Requests
                        </h1>
                        <a href="/urban2/views/citizen/garbage-schedule.php" class="schedule-btn">
                            <i class="fas fa-plus"></i>
                            Schedule New Collection
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-title">Total Requests</div>
                        <div class="stat-value">
                            <i class="fas fa-clipboard-list text-primary"></i>
                            <?php echo count($activeRequests); ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Pending Requests</div>
                        <div class="stat-value">
                            <i class="fas fa-clock text-warning"></i>
                            <?php 
                            echo count(array_filter($activeRequests, function($request) {
                                return (!empty($request['status']) && strtolower($request['status']) === 'pending');
                            }));
                            ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Approved Requests</div>
                        <div class="stat-value">
                            <i class="fas fa-check-circle text-success"></i>
                            <?php 
                            echo count(array_filter($activeRequests, function($request) {
                                return (!empty($request['status']) && strtolower($request['status']) === 'approved');
                            }));
                            ?>
                        </div>
                    </div>
                </div>

                <?php if (empty($activeRequests)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No Active Requests</h3>
                        <p>You don't have any active garbage collection requests at the moment.</p>
                        <a href="/urban2/views/citizen/garbage-schedule.php" class="schedule-btn">
                            <i class="fas fa-plus"></i>
                            Schedule Your First Collection
                        </a>
                    </div>
                <?php else: ?>
                    <div class="requests-card">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Waste Type</th>
                                        <th>Volume</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activeRequests as $request): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                $date = isset($request['collection_date']) ? $request['collection_date'] : 
                                                       (isset($request['created_at']) ? $request['created_at'] : date('Y-m-d'));
                                                echo date('M d, Y', strtotime($date));
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $time = isset($request['collection_time']) ? $request['collection_time'] : 
                                                       (isset($request['created_at']) ? date('H:i:s', strtotime($request['created_at'])) : '00:00:00');
                                                echo date('h:i A', strtotime($time));
                                                ?>
                                            </td>
                                            <td>
                                                <span class="waste-type-badge">
                                                    <i class="fas fa-trash-alt me-1"></i>
                                                    <?php echo !empty($request['waste_type']) ? htmlspecialchars($request['waste_type']) : 'Not specified'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="fas fa-weight-hanging me-1 text-secondary"></i>
                                                <?php echo !empty($request['waste_volume']) ? htmlspecialchars($request['waste_volume']) : 'Not specified'; ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo strtolower(!empty($request['status']) ? $request['status'] : 'pending'); ?>">
                                                    <i class="fas fa-circle"></i>
                                                    <?php echo ucfirst(!empty($request['status']) ? $request['status'] : 'pending'); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="/urban2/views/citizen/view-request.php?id=<?php echo $request['id']; ?>" 
                                                   class="action-btn view-btn">
                                                    <i class="fas fa-eye"></i>
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 