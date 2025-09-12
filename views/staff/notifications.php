<?php
session_start();
require_once '../../controllers/NotificationController.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    $_SESSION['error'] = "Unauthorized access";
    header('Location: /urban2/login.php');
    exit();
}

$notificationController = new NotificationController();
$notifications = $notificationController->getUnreadNotifications($_SESSION['user_id']);

// Mark notifications as read if requested
if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
    $notificationController->markAsRead($_POST['notification_id']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Urban Council</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .notification-item {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            transition: background-color 0.2s;
        }
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        .notification-item h5 {
            margin-bottom: 0.5rem;
            color: #0d6efd;
        }
        .notification-item p {
            margin-bottom: 0.5rem;
            color: #6c757d;
        }
        .notification-item .text-muted {
            font-size: 0.875rem;
        }
        .unread {
            background-color: #e7f5ff;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Notifications</h2>
            <a href="/urban2/views/staff/dashboard.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="alert alert-info">
                <i class="fas fa-bell-slash"></i> No new notifications.
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body p-0">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5><?php echo htmlspecialchars($notification['title']); ?></h5>
                                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <?php if ($notification['type'] === 'collection_request'): ?>
                                        <a href="/urban2/views/staff/view_collection_request.php?id=<?php echo $notification['reference_id']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View Request
                                        </a>
                                    <?php endif; ?>
                                    <div class="mt-2">
                                        <small class="text-muted">
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
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
?> 