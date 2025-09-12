<?php
session_start();
require_once '../../controllers/NotificationController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to continue.";
    header('Location: /urban2/login.php');
    exit();
}

$notificationController = new NotificationController();

// Handle marking notifications as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $notificationId = $_POST['notification_id'];
    $notificationController->markAsRead($notificationId);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Get all notifications for the user
$notifications = $notificationController->getNotificationsByUserId($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Urban Council</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --main-bg: #0d112b;
            --sidebar-bg: #181e3a;
            --notif-unread-bg: #22325c;
            --notif-read-bg: #16213e;
            --notif-border: #22c55e;
            --notif-title: #fff;
            --notif-text: #e5e7eb;
            --notif-date: #22c55e;
            --notif-btn: #22c55e;
            --notif-btn-hover: #166534;
            --notif-btn-text: #fff;
        }
        body {
            background: var(--main-bg);
            min-height: 100vh;
            font-family: 'Inter', 'Segoe UI', 'Roboto', Arial, sans-serif;
            color: var(--notif-text);
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
        h2, .main-content h2 {
            font-weight: 900;
            color: #fff;
            letter-spacing: 0.5px;
        }
        .notification-item {
            border-left: 5px solid var(--notif-border);
            margin-bottom: 1.5rem;
            padding: 1.5rem 1.7rem 1.2rem 2.2rem;
            background-color: var(--notif-read-bg);
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(34,197,94,0.08);
            transition: box-shadow 0.18s, background 0.18s;
            position: relative;
            color: var(--notif-title);
        }
        .notification-item.unread {
            background-color: var(--notif-unread-bg);
            box-shadow: 0 6px 32px rgba(34,197,94,0.13);
            border: 2px solid var(--notif-border);
        }
        .notification-item:hover {
            box-shadow: 0 8px 36px rgba(34,197,94,0.13);
            background: #22325c;
        }
        .notification-item h5,
        .notification-item p,
        .notification-item .timestamp {
            color: #fff;
        }
        .notification-item h5 {
            font-size: 1.18rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        .notification-item p {
            font-size: 1.05rem;
            margin-bottom: 0.7rem;
        }
        .notification-item .timestamp {
            font-size: 0.95rem;
            font-weight: 500;
        }
        .btn-primary.btn-sm, .btn-outline-secondary.btn-sm {
            background: var(--notif-btn);
            color: var(--notif-btn-text);
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            padding: 0.45rem 1.1rem;
            box-shadow: 0 1px 4px rgba(34,197,94,0.08);
            transition: background 0.18s, box-shadow 0.18s;
            border: none;
        }
        .btn-primary.btn-sm:hover, .btn-outline-secondary.btn-sm:hover {
            background: var(--notif-btn-hover);
            color: #fff;
            box-shadow: 0 2px 8px rgba(22,101,52,0.10);
        }
        .alert {
            border-radius: 12px;
            font-size: 1.08rem;
            font-family: inherit;
            color: #fff;
            background: #22c55e;
            border: none;
            box-shadow: 0 2px 8px rgba(34,197,94,0.10);
        }
        @media (max-width: 600px) {
            .container {
                padding: 0 0.3rem;
            }
            .notification-item {
                padding: 1rem 0.7rem 1rem 1.1rem;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="profile-section">
                <img src="<?php echo $_SESSION['profile_image'] ?? '/urban2/assets/images/default-avatar.png'; ?>" 
                     alt="Profile Image">
                <h5 class="text-white mb-1"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Citizen'); ?></h5>
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
                        <i class="fas fa-history"></i> Recent Activities
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/urban2/views/citizen/notifications.php">
                        <i class="fas fa-bell"></i> Notifications
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
            <div class="container mt-4">
                <div class="row mb-4">
                    <div class="col">
                        <a href="/urban2/views/citizen/dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>

                <h2 class="mb-4">Your Notifications</h2>

                <?php if (empty($notifications)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You don't have any notifications.
                    </div>
                <?php else: ?>
                    <div class="notifications-list">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5><?php echo htmlspecialchars($notification['title']); ?></h5>
                                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                        <?php if ($notification['type'] === 'collection_request'): ?>
                                            <a href="/urban2/views/citizen/view-request.php?id=<?php echo $notification['reference_id']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i> View Request
                                            </a>
                                        <?php endif; ?>
                                        <div class="mt-2">
                                            <small class="timestamp">
                                                <i class="far fa-clock"></i> 
                                                <?php echo date('F j, Y g:i A', strtotime($notification['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <?php if (!$notification['is_read']): ?>
                                        <form method="POST" class="ms-3">
                                            <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                            <button type="submit" name="mark_read" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-check"></i> Mark as Read
                                            </button>
                                        </form>
                                    <?php endif; ?>
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