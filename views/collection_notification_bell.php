<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /urban2/login.php');
    exit();
}

// Include required files
require_once __DIR__ . '/../controllers/NotificationController.php';

// Get collection notifications
$notificationController = new NotificationController();
$collectionNotifications = $notificationController->getNotificationsByType($_SESSION['user_id'], ['collection_update', 'collection_reply', 'collection_request']);

// Count unread notifications
$unreadCount = count($collectionNotifications);
?>

<div class="collection-notification-bell">
    <div class="dropdown">
        <button class="btn btn-link dropdown-toggle" type="button" id="collectionNotificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-trash-alt"></i>
            <?php if ($unreadCount > 0): ?>
                <span class="badge bg-danger notification-badge">
                    <?php echo $unreadCount; ?>
                </span>
            <?php endif; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="collectionNotificationDropdown">
            <?php if (empty($collectionNotifications)): ?>
                <li><a class="dropdown-item" href="#">No new collection notifications</a></li>
            <?php else: ?>
                <?php foreach ($collectionNotifications as $notification): ?>
                    <li>
                        <a class="dropdown-item notification-item" 
                           href="#" 
                           onclick="handleCollectionNotificationClick('<?php echo $notification['id']; ?>', '<?php echo $notification['type']; ?>', <?php echo isset($notification['reference_id']) ? $notification['reference_id'] : 'null'; ?>)">
                            <div class="notification-content">
                                <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                                <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                                <div class="notification-time"><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></div>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>

<style>
.collection-notification-bell {
    position: relative;
    margin-right: 15px;
}

.collection-notification-bell .badge {
    position: absolute;
    top: -5px;
    right: -5px;
}

.notification-item {
    padding: 10px 15px;
    white-space: normal;
    cursor: pointer;
}

.notification-content {
    max-width: 300px;
}

.notification-title {
    font-weight: bold;
    margin-bottom: 5px;
}

.notification-message {
    color: #666;
    margin-bottom: 5px;
}

.notification-time {
    font-size: 0.8em;
    color: #999;
}

.notification-dropdown {
    min-width: 300px;
    max-height: 400px;
    overflow-y: auto;
}
</style>

<script>
function handleCollectionNotificationClick(notificationId, type, referenceId) {
    // Mark notification as read first
    fetch('/urban2/api/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            notification_id: notificationId
        })
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update notification badge
            const badge = document.querySelector('.collection-notification-bell .badge');
            if (badge) {
                const currentCount = parseInt(badge.textContent);
                const newCount = currentCount - 1;
                if (newCount <= 0) {
                    badge.remove();
                } else {
                    badge.textContent = newCount;
                }
            }
            
            // Redirect based on user role
            if (referenceId) {
                if (<?php echo $_SESSION['role'] === 'garbage_manager' ? 'true' : 'false'; ?>) {
                    window.location.href = '/urban2/views/staff/view_collection_request.php?id=' + referenceId;
                } else {
                    window.location.href = '/urban2/views/citizen/view-request.php?id=' + referenceId;
                }
            } else {
                if (<?php echo $_SESSION['role'] === 'garbage_manager' ? 'true' : 'false'; ?>) {
                    window.location.href = '/urban2/views/staff/staff_dashboard.php';
                } else {
                    window.location.href = '/urban2/views/citizen/garbage-schedule.php';
                }
            }
        }
    }).catch(error => {
        console.error('Error:', error);
    });
}

// Check for new collection notifications every 30 seconds
setInterval(() => {
    fetch('/urban2/api/get_collection_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = document.querySelector('.collection-notification-bell .badge');
                if (data.count > 0) {
                    if (!badge) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'badge bg-danger notification-badge';
                        newBadge.textContent = data.count;
                        document.querySelector('.collection-notification-bell .btn').appendChild(newBadge);
                    } else {
                        badge.textContent = data.count;
                    }
                } else if (badge) {
                    badge.remove();
                }
            }
        }).catch(error => {
            console.error('Error fetching collection notifications:', error);
        });
}, 30000);
</script> 