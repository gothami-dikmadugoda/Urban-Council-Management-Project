<?php
session_start();
require_once __DIR__ . '/../controllers/AnnouncementController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$announcementController = new AnnouncementController();
$announcements = $announcementController->getActiveAnnouncements();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - Urban Council</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0084ff;
            --secondary-color: #e4e6eb;
            --accent-color: #1877f2;
            --text-muted: #65676b;
            --border-color: #dee2e6;
            --hover-bg: #f8f9fa;
            --active-bg: #e9ecef;
        }

        body {
            background-color: #f0f2f5;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .announcement-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.2s ease;
        }

        .announcement-card:hover {
            transform: translateY(-2px);
        }

        .announcement-header {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .announcement-title {
            font-weight: 600;
            color: #1c1e21;
            margin-bottom: 5px;
        }

        .announcement-meta {
            font-size: 0.8em;
            color: var(--text-muted);
        }

        .announcement-content {
            padding: 15px;
            color: #1c1e21;
        }

        .announcement-footer {
            padding: 15px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .expiry-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 500;
        }

        .expiry-badge.active {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .expiry-badge.expired {
            background-color: #ffebee;
            color: #d32f2f;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-button {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            border: none;
            background-color: var(--hover-bg);
            color: var(--text-muted);
            transition: all 0.2s ease;
        }

        .action-button:hover {
            background-color: var(--active-bg);
            color: #1c1e21;
        }

        .action-button.edit {
            color: #1976d2;
        }

        .action-button.delete {
            color: #d32f2f;
        }

        .announcement-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease;
            z-index: 1000;
            cursor: pointer;
        }

        .announcement-btn:hover {
            transform: scale(1.1);
            color: white;
        }

        .announcement-btn i {
            font-size: 24px;
        }

        .announcement-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ff4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            border-radius: 12px;
            border: none;
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 3em;
            margin-bottom: 20px;
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <div class="announcement-btn" data-bs-toggle="modal" data-bs-target="#announcementsModal">
        <i class="fas fa-bullhorn"></i>
        <?php if (isset($announcements['success']) && $announcements['success'] && !empty($announcements['data'])): ?>
            <span class="announcement-badge"><?php echo count($announcements['data']); ?></span>
        <?php endif; ?>
    </div>

    <!-- Announcements Modal -->
    <div class="modal fade" id="announcementsModal" tabindex="-1" aria-labelledby="announcementsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="announcementsModalLabel">Announcements</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (isset($announcements['success']) && $announcements['success'] && !empty($announcements['data'])): ?>
                        <?php foreach ($announcements['data'] as $announcement): ?>
                            <div class="announcement-card">
                                <div class="announcement-header">
                                    <h5 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                                    <div class="announcement-meta">
                                        Posted by <?php echo htmlspecialchars($announcement['first_name'] . ' ' . $announcement['last_name']); ?> 
                                        on <?php echo date('F j, Y g:i A', strtotime($announcement['created_at'])); ?>
                                    </div>
                                </div>
                                <div class="announcement-content">
                                    <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                                </div>
                                <div class="announcement-footer">
                                    <div>
                                        <?php if ($announcement['expiry_datetime']): ?>
                                            <span class="expiry-badge <?php echo strtotime($announcement['expiry_datetime']) > time() ? 'active' : 'expired'; ?>">
                                                <?php echo strtotime($announcement['expiry_datetime']) > time() ? 'Active until ' . date('M j, Y g:i A', strtotime($announcement['expiry_datetime'])) : 'Expired'; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="expiry-badge active">No expiry</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                        <div class="action-buttons">
                                            <button class="action-button edit" onclick="editAnnouncement(<?php echo $announcement['announcement_id']; ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="action-button delete" onclick="deleteAnnouncement(<?php echo $announcement['announcement_id']; ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-bullhorn"></i>
                            <h4>No Announcements</h4>
                            <p>There are no active announcements at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <div class="modal-footer">
                        <a href="create_announcement.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create New Announcement
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editAnnouncement(id) {
            window.location.href = `edit_announcement.php?id=${id}`;
        }

        function deleteAnnouncement(id) {
            if (confirm('Are you sure you want to delete this announcement?')) {
                fetch(`../api/delete_announcement.php?id=${id}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to delete announcement');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the announcement');
                });
            }
        }
    </script>
</body>
</html> 