<?php
require_once '../../config/database.php';
require_once '../../controllers/NotificationController.php';
require_once '../../controllers/ComplaintController.php';
require_once '../../controllers/AdminController.php';

session_start();

// Check if user is logged in and is a staff member
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    header('Location: /urban2/login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$notificationController = new NotificationController();
$complaintController = new ComplaintController();
$adminController = new AdminController();

// Get staff details
$staffDetails = $adminController->getStaffDetails($_SESSION['user_id']);
$staff = $staffDetails['data'];

// Get unread notifications for the logged-in staff
$notifications = $notificationController->getUnreadNotifications($_SESSION['user_id']);

// Handle marking notification as read
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $notificationController->markAsRead($_GET['mark_read']);
    header('Location: notifications.php');
    exit;
}

// Get user profile image path
$profileImagePath = isset($_SESSION['profile_picture']) && !empty($_SESSION['profile_picture']) 
    ? '/urban2/uploads/profile_pictures/' . $_SESSION['profile_picture']
    : '/urban2/assets/images/default-avatar.png';

// Verify if the profile image exists, if not use default
if (!file_exists($profileImagePath)) {
    $profileImagePath = '/urban2/assets/images/default-avatar.png';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Urban Council Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
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
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
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
            padding: 2.5rem;
            transition: var(--transition);
            flex: 1;
            background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
            min-height: 100vh;
        }
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem 2.5rem 1.5rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(61, 54, 92, 0.10);
        }
        .dashboard-header {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 2.5rem;
        }
        .dashboard-header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.3rem;
        }
        .dashboard-header .subtitle {
            color: var(--gray);
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
        .dashboard-header .divider {
            width: 60px;
            height: 4px;
            background: var(--gradient-accent);
            border-radius: 2px;
            margin-bottom: 0.5rem;
        }
        .notification-list {
            margin-top: 1.5rem;
        }
        .notification-item {
            background: linear-gradient(120deg, #f8f9fa 60%, #f3e8ff 100%);
            border-radius: 18px;
            padding: 2rem 2rem 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 6px 24px rgba(61, 54, 92, 0.08);
            transition: var(--transition);
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
            position: relative;
        }
        .notification-item:hover {
            box-shadow: 0 12px 32px rgba(61, 54, 92, 0.16);
            transform: translateY(-2px) scale(1.01);
        }
        .notification-icon {
            min-width: 60px;
            min-height: 60px;
            max-width: 60px;
            max-height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            background: var(--highlight);
            color: #fff;
            box-shadow: 0 2px 8px rgba(249, 181, 95, 0.15);
            margin-right: 1.5rem;
        }
        .notification-content {
            flex: 1;
        }
        .notification-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.3rem;
        }
        .notification-message {
            color: var(--dark);
            margin-bottom: 1.1rem;
            line-height: 1.7;
        }
        .notification-time {
            font-size: 0.98rem;
            color: var(--gray);
            margin-bottom: 0.7rem;
            display: block;
        }
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        .btn-mark-read {
            background: var(--gradient-primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 0.7rem 2.2rem;
            font-weight: 600;
            font-size: 1.05rem;
            box-shadow: 0 2px 8px rgba(61, 54, 92, 0.08);
            transition: var(--transition);
        }
        .btn-mark-read:hover {
            background: var(--gradient-accent);
            color: #fff;
            transform: translateY(-1px) scale(1.03);
        }
        .btn-view-complaint {
            background: var(--gradient-highlight);
            color: var(--dark);
            border: none;
            border-radius: 10px;
            padding: 0.7rem 1.7rem;
            font-weight: 600;
            font-size: 1.05rem;
            margin-right: 0.5rem;
            box-shadow: 0 2px 8px rgba(249, 181, 95, 0.10);
            transition: var(--transition);
        }
        .btn-view-complaint:hover {
            background: var(--gradient-accent);
            color: #fff;
        }
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 0;
        }
        .empty-state-icon {
            font-size: 4rem;
            color: var(--accent);
            margin-bottom: 1.2rem;
        }
        .empty-state-text {
            color: var(--gray);
            font-size: 1.2rem;
            font-weight: 500;
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
        @media (max-width: 768px) {
            .main-content {
                padding: 1.5rem;
            }
            .dashboard-container {
                padding: 1rem;
            }
            .notification-item {
                flex-direction: column;
                align-items: stretch;
                padding: 1.2rem 1rem 1rem 1rem;
                gap: 1rem;
            }
            .notification-icon {
                margin-right: 0;
                margin-bottom: 0.7rem;
                align-self: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="profile-section">
                <img src="<?php echo $profileImagePath; ?>" alt="Profile Image" class="profile-picture">
                <h5 class="text-white mb-1">
                    <?php 
                    $firstName = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : '';
                    $lastName = isset($_SESSION['last_name']) ? $_SESSION['last_name'] : '';
                    echo htmlspecialchars($firstName . ' ' . $lastName);
                    ?>
                </h5>
                <p class="text-muted mb-0"><?php echo ucfirst($staff['department']); ?> Department</p>
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
                    <?php if (strtolower($staff['department']) === 'it' && strtolower($staff['job_role']) === 'it_staff'): ?>
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
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="/urban2/views/admin/notifications.php">
                            <i class='bx bxs-bell'></i> Notifications
                            <?php if (isset($notifications) && count($notifications) > 0): ?>
                                <span class="badge bg-danger ms-2"><?php echo count($notifications); ?></span>
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
            <div class="dashboard-container">
                <div class="dashboard-header">
                    <h1>Notifications</h1>
                    <div class="subtitle">Stay updated with the latest actions and alerts.</div>
                    <div class="divider"></div>
        </div>

        <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <span class="empty-state-icon"><i class="bx bx-bell-off"></i></span>
                        <div class="empty-state-text">No new notifications. You're all caught up!</div>
            </div>
        <?php else: ?>
                    <div class="notification-list">
                <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item">
                                <div class="notification-icon" style="background:
                                    <?php
                                        echo $notification['type'] === 'complaint' ? 'linear-gradient(135deg, #7C4585, #C95792)' :
                                            ($notification['type'] === 'schedule' ? 'linear-gradient(135deg, #3D365C, #7C4585)' :
                                            'linear-gradient(135deg, #F8B55F, #C95792)');
                                    ?>;">
                                    <i class="bx <?php
                                        echo $notification['type'] === 'complaint' ? 'bx-error-circle' :
                                            ($notification['type'] === 'schedule' ? 'bx-calendar' : 'bx-bell');
                                ?>"></i>
                            </div>
                            <div class="notification-content">
                                    <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                                    <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                                    <span class="notification-time">
                                    <?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?>
                                    </span>
                                    <div class="btn-group">
                            <?php if ($notification['type'] === 'complaint' && isset($notification['complaint_id'])): ?>
                                    <a href="view_complaint.php?id=<?php echo $notification['complaint_id']; ?>" 
                                               class="btn btn-view-complaint">
                                        View Complaint
                                    </a>
                                        <?php endif; ?>
                                    <a href="?mark_read=<?php echo $notification['id']; ?>" 
                                           class="btn btn-mark-read">
                                        Mark as Read
                                    </a>
                                </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 