<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include required files
require_once __DIR__ . '/../models/Chat.php';
require_once __DIR__ . '/../controllers/AdminController.php';

// Get notifications using existing AdminController
$adminController = new AdminController();
$recentNotifications = $adminController->getRecentNotifications();

// Count unread notifications
$unreadCount = count($recentNotifications);

// Get unread messages count
$chat = new Chat();
$unreadMessages = $chat->getUnreadCount($_SESSION['user_id']);

// Set default profile image path
$defaultProfileImage = '/urban2/assets/images/profiles/default-profile.png';
?>

<div class="notification-bell">
    <div class="dropdown">
        <button class="btn btn-link dropdown-toggle" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-bell"></i>
            <?php if ($unreadCount > 0 || $unreadMessages > 0): ?>
                <span class="badge bg-danger notification-badge">
                    <?php echo $unreadCount + $unreadMessages; ?>
                </span>
            <?php endif; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
            <?php if (empty($recentNotifications) && $unreadMessages == 0): ?>
                <li><a class="dropdown-item" href="#">No new notifications</a></li>
            <?php else: ?>
                <?php if ($unreadMessages > 0): ?>
                    <li>
                        <a class="dropdown-item notification-item" href="chat.php">
                            <div class="notification-content">
                                <div class="notification-title">New Messages</div>
                                <div class="notification-message">You have <?php echo $unreadMessages; ?> unread message<?php echo $unreadMessages > 1 ? 's' : ''; ?></div>
                                <div class="notification-time">Click to view</div>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>
                <?php foreach ($recentNotifications as $notification): ?>
                    <li>
                        <a class="dropdown-item notification-item" 
                           href="#" 
                           onclick="handleNotificationClick('<?php echo $notification['id']; ?>', '<?php echo $notification['type']; ?>', <?php echo isset($notification['reference_id']) ? $notification['reference_id'] : 'null'; ?>)">
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
.notification-bell {
    position: relative;
    margin-right: 15px;
}

.notification-bell .badge {
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
function handleNotificationClick(notificationId, type, referenceId) {
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
            const badge = document.querySelector('.notification-bell .badge');
            if (badge) {
                const currentCount = parseInt(badge.textContent);
                const newCount = currentCount - 1;
                if (newCount <= 0) {
                    badge.remove();
                } else {
                    badge.textContent = newCount;
                }
            }
            
            // Redirect based on notification type
            switch(type) {
                case 'complaint':
                    if (referenceId) {
                        window.location.href = '/urban2/views/view_complaint.php?id=' + referenceId;
                    } else {
                        window.location.href = '/urban2/views/admin/staff_dashboard.php#complaints';
                    }
                    break;
                case 'complaint_note':
                    window.location.href = '/urban2/views/view_complaint.php?id=' + referenceId;
                    break;
                case 'schedule':
                    window.location.href = '/urban2/views/admin/staff_dashboard.php#schedule';
                    break;
                case 'system':
                    window.location.href = '/urban2/views/admin/staff_dashboard.php#notifications';
                    break;
                case 'collection_update':
                case 'collection_reply':
                case 'collection_request':
                    if (referenceId) {
                        window.location.href = '/urban2/views/staff/view_collection_request.php?id=' + referenceId;
                    } else {
                        window.location.href = '/urban2/views/staff/staff_dashboard.php';
                    }
                    break;
                case 'payment':
                    if (referenceId) {
                        // Check if user is IT staff
                        if (<?php echo isset($_SESSION['department']) && $_SESSION['department'] === 'it' ? 'true' : 'false'; ?>) {
                            window.location.href = '/urban2/views/staff/payment_details.php?id=' + referenceId;
                        } else {
                            window.location.href = '/urban2/views/staff/staff_dashboard.php';
                        }
                    } else {
                        window.location.href = '/urban2/views/staff/staff_dashboard.php';
                    }
                    break;
                default:
                    window.location.href = '/urban2/views/admin/staff_dashboard.php';
            }
        }
    }).catch(error => {
        console.error('Error:', error);
    });
}

// Check for new messages and notifications every 30 seconds
setInterval(() => {
    fetch('/urban2/api/get_unread_message_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = document.querySelector('.notification-bell .badge');
                if (data.count > 0) {
                    if (!badge) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'badge bg-danger notification-badge';
                        newBadge.textContent = data.count;
                        document.querySelector('.notification-bell .btn').appendChild(newBadge);
                    } else {
                        badge.textContent = data.count;
                    }
                } else if (badge) {
                    badge.remove();
                }
            }
        }).catch(error => {
            console.error('Error fetching message count:', error);
        });
}, 30000);
</script> 