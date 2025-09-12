<?php
session_start();
require_once __DIR__ . '/../controllers/GarbageScheduleController.php';

// Validate staff access and department
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff' || 
    $_SESSION['department'] !== 'health' || $_SESSION['job_role'] !== 'garbage_manager') {
    header('Location: /urban2/login.php');
    exit;
}

$scheduleController = new GarbageScheduleController();

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid schedule ID";
    header('Location: /urban2/admin/staff_dashboard.php');
    exit;
}

$scheduleId = $_GET['id'];
$schedule = $scheduleController->getScheduleById($scheduleId);

if (!$schedule) {
    $_SESSION['error_message'] = "Schedule not found";
    header('Location: /urban2/admin/staff_dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $scheduleController->updateSchedule($scheduleId, $_POST);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header('Location: /urban2/admin/staff_dashboard.php');
        exit;
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Garbage Schedule - Urban Council Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: #2c3e50;
            color: white;
            padding: 1rem;
            transition: all 0.3s ease;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: all 0.3s ease;
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        .nav-link i {
            margin-right: 10px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            padding: 1.5rem;
            border-radius: 15px 15px 0 0 !important;
        }
        .btn-primary {
            background: #3498db;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h4 class="mb-4">Admin Panel</h4>
        <nav class="nav flex-column">
            <a class="nav-link" href="/urban2/admin/staff_dashboard.php">
                <i class='bx bxs-dashboard'></i> Dashboard
            </a>
            <a class="nav-link active" href="/urban2/admin/staff_dashboard.php">
                <i class='bx bxs-calendar'></i> Garbage Schedule
            </a>
            <a class="nav-link" href="/urban2/admin/notifications.php">
                <i class='bx bxs-bell'></i> Notifications
            </a>
            <a class="nav-link" href="/urban2/admin/user_feedback.php">
                <i class='bx bxs-message-dots'></i> User Feedback
            </a>
            <a class="nav-link" href="/urban2/admin/reports.php">
                <i class='bx bxs-file'></i> Reports
            </a>
            <a class="nav-link" href="/urban2/logout.php">
                <i class='bx bxs-log-out'></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Edit Garbage Schedule</h2>

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Area</label>
                            <input type="text" class="form-control" name="area" value="<?php echo $schedule['area']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="schedule_date" value="<?php echo $schedule['schedule_date']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Time</label>
                            <input type="time" class="form-control" name="schedule_time" value="<?php echo $schedule['schedule_time']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="active" <?php echo $schedule['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="completed" <?php echo $schedule['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $schedule['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/urban2/admin/staff_dashboard.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Schedule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 