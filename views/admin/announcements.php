<?php
session_start();
require_once __DIR__ . '/../../controllers/AdminController.php';
require_once __DIR__ . '/../../controllers/AnnouncementController.php';

// Check if user is logged in and is an IT team member
if (!isset($_SESSION['user_id']) || $_SESSION['department'] !== 'it' || $_SESSION['job_role'] !== 'it_staff') {
    header('Location: /urban2/login.php');
    exit;
}

$adminController = new AdminController();
$announcementController = new AnnouncementController();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'publish':
                if (isset($_POST['title']) && isset($_POST['content'])) {
                    $expiry_datetime = !empty($_POST['expiry_datetime']) ? $_POST['expiry_datetime'] : null;
                    $result = $announcementController->createAnnouncement(
                        $_POST['title'],
                        $_POST['content'],
                        $_SESSION['user_id'],
                        $expiry_datetime
                    );
                    if ($result['success']) {
                        $_SESSION['success_message'] = "Announcement published successfully!";
                    } else {
                        $_SESSION['error_message'] = $result['message'];
                    }
                }
                break;

            case 'edit':
                if (isset($_POST['announcement_id']) && isset($_POST['title']) && isset($_POST['content'])) {
                    $expiry_datetime = !empty($_POST['expiry_datetime']) ? $_POST['expiry_datetime'] : null;
                    $result = $announcementController->updateAnnouncement(
                        $_POST['announcement_id'],
                        $_POST['title'],
                        $_POST['content'],
                        $expiry_datetime
                    );
                    if ($result['success']) {
                        $_SESSION['success_message'] = "Announcement updated successfully!";
                    } else {
                        $_SESSION['error_message'] = $result['message'];
                    }
                }
                break;

            case 'delete':
                if (isset($_POST['announcement_id'])) {
                    $result = $announcementController->deleteAnnouncement($_POST['announcement_id']);
                    if ($result['success']) {
                        $_SESSION['success_message'] = "Announcement deleted successfully!";
                    } else {
                        $_SESSION['error_message'] = $result['message'];
                    }
                }
                break;
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Get all announcements
$announcements = $announcementController->getAllAnnouncements();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Announcements - Urban Council Management System</title>
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
            padding: 2rem;
            transition: var(--transition);
            flex: 1;
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
        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid var(--accent);
            padding: 3px;
            margin-bottom: 1rem;
            object-fit: cover;
        }
        .card {
            background: white;
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        .card-header {
            background: var(--gradient-primary);
            color: white;
            border-bottom: none;
            padding: 1.2rem;
            font-weight: 500;
        }
        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            color: white;
            transition: var(--transition);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(61, 54, 92, 0.3);
        }
        .btn-warning {
            background: var(--gradient-accent);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            color: white;
            transition: var(--transition);
        }
        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(124, 69, 133, 0.3);
        }
        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            color: white;
            transition: var(--transition);
        }
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }
        .form-control {
            border-radius: var(--border-radius);
            padding: 0.8rem 1.2rem;
            border: 1px solid #eee;
            transition: var(--transition);
        }
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(201, 87, 146, 0.25);
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            border-bottom: 2px solid var(--gray);
            color: var(--dark);
            font-weight: 600;
        }
        .table td {
            vertical-align: middle;
        }
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
        }
        .badge.bg-success {
            background: var(--gradient-primary) !important;
        }
        .badge.bg-danger {
            background: linear-gradient(135deg, #dc3545, #c82333) !important;
        }
        .alert {
            border: none;
            border-radius: var(--border-radius);
        }
        .alert-success {
            background: linear-gradient(135deg, #28a745, #218838);
            color: white;
        }
        .alert-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
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
                    $firstName = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : '';
                    $lastName = isset($_SESSION['last_name']) ? $_SESSION['last_name'] : '';
                    echo htmlspecialchars($firstName . ' ' . $lastName);
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
                        <a class="nav-link active" href="/urban2/views/admin/announcements.php">
                            <i class='bx bxs-megaphone'></i> Announcements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/urban2/views/admin/notifications.php">
                            <i class='bx bxs-bell'></i> Notifications
                            <?php if (isset($unreadNotifications) && $unreadNotifications > 0): ?>
                                <span class="badge bg-danger ms-2"><?php echo $unreadNotifications; ?></span>
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
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">Manage Announcements</h1>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Publish Announcement Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Publish New Announcement</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="publish">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="content" class="form-label">Content</label>
                                <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="expiry_datetime" class="form-label">Expiry Date & Time</label>
                                <input type="datetime-local" class="form-control" id="expiry_datetime" name="expiry_datetime">
                                <small class="text-muted">Leave empty if the announcement should not expire</small>
                            </div>
                            <button type="submit" class="btn btn-primary">Publish Announcement</button>
                        </form>
                    </div>
                </div>

                <!-- Edit Announcement Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Edit Announcement</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="edit">
                            <div class="mb-3">
                                <label for="edit_id" class="form-label">Announcement ID</label>
                                <input type="number" class="form-control" id="edit_id" name="announcement_id" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="edit_title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_content" class="form-label">Content</label>
                                <textarea class="form-control" id="edit_content" name="content" rows="4" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="edit_expiry_datetime" class="form-label">Expiry Date & Time</label>
                                <input type="datetime-local" class="form-control" id="edit_expiry_datetime" name="expiry_datetime">
                                <small class="text-muted">Leave empty if the announcement should not expire</small>
                            </div>
                            <button type="submit" class="btn btn-warning">Update Announcement</button>
                        </form>
                    </div>
                </div>

                <!-- Delete Announcement Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Delete Announcement</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="delete">
                            <div class="mb-3">
                                <label for="delete_id" class="form-label">Announcement ID</label>
                                <input type="number" class="form-control" id="delete_id" name="announcement_id" required>
                            </div>
                            <button type="submit" class="btn btn-danger">Delete Announcement</button>
                        </form>
                    </div>
                </div>

                <!-- List of Announcements -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">All Announcements</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Content</th>
                                        <th>Posted By</th>
                                        <th>Created At</th>
                                        <th>Expires At</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($announcements as $announcement): ?>
                                    <tr>
                                        <td><?php echo $announcement['announcement_id']; ?></td>
                                        <td><?php echo htmlspecialchars($announcement['title']); ?></td>
                                        <td><?php echo htmlspecialchars($announcement['content']); ?></td>
                                        <td><?php echo htmlspecialchars($announcement['first_name'] . ' ' . $announcement['last_name']); ?></td>
                                        <td><?php echo date('Y-m-d H:i:s', strtotime($announcement['created_at'])); ?></td>
                                        <td><?php echo $announcement['formatted_expiry']; ?></td>
                                        <td>
                                            <?php
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-fill edit form when announcement ID is entered
        document.getElementById('edit_id').addEventListener('change', function() {
            const id = this.value;
            const announcement = <?php echo json_encode($announcements); ?>.find(a => a.announcement_id == id);
            if (announcement) {
                document.getElementById('edit_title').value = announcement.title;
                document.getElementById('edit_content').value = announcement.content;
                if (announcement.expiry_datetime) {
                    document.getElementById('edit_expiry_datetime').value = announcement.expiry_datetime.substring(0, 16);
                } else {
                    document.getElementById('edit_expiry_datetime').value = '';
                }
            }
        });
    </script>
</body>
</html> 