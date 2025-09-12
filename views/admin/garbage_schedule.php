<?php
session_start();
require_once __DIR__ . '/../../controllers/AdminController.php';

// Validate staff access and department
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    header('Location: /urban2/login.php');
    exit;
}

$adminController = new AdminController();
$staffId = $_SESSION['user_id'];
$staffDetails = $adminController->getStaffDetails($staffId);
$staff = $staffDetails['data'];

// Validate department and job role
if ($staff['department'] !== 'health' || $staff['job_role'] !== 'garbage_manager') {
    header('Location: /urban2/views/admin/staff_dashboard.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $data = array(
                    'area' => $_POST['area'],
                    'schedule_date' => $_POST['schedule_date'],
                    'schedule_time' => $_POST['schedule_time'],
                    'waste_type' => $_POST['waste_type'],
                    'created_by' => $_SESSION['user_id']
                );
                $result = $adminController->addGarbageSchedule($data);
                break;

            case 'edit':
                $data = array(
                    'area' => $_POST['area'],
                    'schedule_date' => $_POST['schedule_date'],
                    'schedule_time' => $_POST['schedule_time'],
                    'waste_type' => $_POST['waste_type']
                );
                $result = $adminController->updateGarbageSchedule($_POST['id'], $data);
                break;

            case 'delete':
                $result = $adminController->deleteGarbageSchedule($_POST['id']);
                break;
        }

        if (isset($result)) {
            $_SESSION['message'] = $result['message'];
            $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
        }
    }
}

// Get all schedules
$schedules = $adminController->getGarbageSchedules();
$schedules = isset($schedules['success']) && $schedules['success'] ? $schedules['data'] : [];

// Get all health department staff for assignment
$staffList = $adminController->getStaffByDepartment('health');
$staffList = isset($staffList['success']) && $staffList['success'] ? $staffList['data'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Garbage Schedule - Urban Council Management System</title>
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: var(--gradient-primary);
            position: sticky;
            top: 0;
            z-index: 2;
            text-align: center;
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
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2.5rem 2.5rem 2rem 2.5rem;
            background: #f8f9fa;
            min-height: 100vh;
            transition: all 0.3s ease;
        }
        .card {
            border: none;
            border-radius: 18px;
            box-shadow: var(--box-shadow);
            margin-bottom: 2.5rem;
            background: #fff;
        }
        .card-header {
            background: var(--gradient-primary);
            color: #fff;
            border-bottom: none;
            padding: 1.5rem 2rem;
            border-radius: 18px 18px 0 0 !important;
        }
        .card-body {
            padding: 2rem 2.2rem 2.2rem 2.2rem;
        }
        .form-label {
            font-weight: 600;
            color: var(--primary);
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.9rem 1.2rem;
            border: 2px solid #eee;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(201, 87, 146, 0.10);
        }
        .btn-primary {
            background: var(--primary);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }
        .table {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
        }
        .table thead th {
            background: var(--gradient-primary);
            color: #fff;
            border: none;
            font-weight: 600;
            padding: 1rem 0.75rem;
        }
        .table tbody tr:nth-child(even) {
            background: #f3f3fa;
        }
        .table tbody tr:hover {
            background: #f8e6f2;
            transition: background 0.2s;
        }
        .table td, .table th {
            vertical-align: middle;
            padding: 0.9rem 0.75rem;
        }
        .modal-content {
            border-radius: 16px;
        }
        .modal-header {
            background: var(--gradient-primary);
            color: #fff;
            border-radius: 16px 16px 0 0;
        }
        .modal-title {
            font-weight: 600;
        }
        .btn-danger {
            border-radius: 10px;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            .card-body {
                padding: 1.2rem 1rem 1.5rem 1rem;
            }
        }
        .schedule-status {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        .status-pending {
            background: #ffeeba;
            color: #856404;
        }
        .status-in_progress {
            background: #cce5ff;
            color: #004085;
        }
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .waste-type {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        .waste-perishable {
            background: #e2e3e5;
            color: #383d41;
        }
        .waste-non_perishable {
            background: #cce5ff;
            color: #004085;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="profile-section">
            <img src="<?php echo $staff['profile_picture'] ?? '/urban2/assets/images/default-profile.png'; ?>" 
                 alt="Profile Picture" class="profile-picture">
            <h5 class="text-white mb-1"><?php echo $staff['first_name'] . ' ' . $staff['last_name']; ?></h5>
            <p class="text-muted mb-0"><?php echo ucfirst($staff['department']); ?> Department</p>
            <p class="text-muted mb-0"><?php echo ucfirst($staff['job_role']); ?></p>
        </div>
        <div class="nav-container">
            <ul class="nav flex-column">
                <li class="nav-item">
            <a class="nav-link" href="/urban2/views/admin/staff_dashboard.php">
                <i class='bx bxs-dashboard'></i> Dashboard
            </a>
                </li>
                <li class="nav-item">
            <a class="nav-link" href="/urban2/views/admin/staff_profile.php">
                <i class='bx bxs-user'></i> My Profile
            </a>
                </li>
                <li class="nav-item">
            <a class="nav-link active" href="/urban2/views/admin/garbage_schedule.php">
                <i class='bx bxs-calendar'></i> Garbage Schedule
            </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/staff/collection_requests.php">
                        <i class='bx bxs-truck'></i> Collection Requests
                    </a>
                </li>
                <li class="nav-item">
            <a class="nav-link" href="/urban2/views/admin/notifications.php">
                <i class='bx bxs-bell'></i> Notifications
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
            <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Add New Schedule</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="add">
                                <div class="mb-3">
                                    <label class="form-label">Area</label>
                                    <input type="text" class="form-control" name="area" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Schedule Date</label>
                                    <input type="date" class="form-control" name="schedule_date" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Schedule Time</label>
                                    <input type="time" class="form-control" name="schedule_time" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Waste Type</label>
                                    <select class="form-select" name="waste_type" required>
                                        <option value="perishable">Perishable</option>
                                        <option value="non_perishable">Non-Perishable</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Add Schedule</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Garbage Collection Schedules</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Area</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Waste Type</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($schedules as $schedule): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($schedule['area']); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($schedule['schedule_date'])); ?></td>
                                            <td><?php echo date('H:i', strtotime($schedule['schedule_time'])); ?></td>
                                            <td>
                                                <span class="waste-type waste-<?php echo $schedule['waste_type']; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $schedule['waste_type'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-outline-primary btn-sm me-1" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal<?php echo $schedule['id']; ?>"
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $schedule['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                            onclick="return confirm('Are you sure you want to delete this schedule?')"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?php echo $schedule['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Schedule</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="POST" action="">
                                                            <input type="hidden" name="action" value="edit">
                                                            <input type="hidden" name="id" value="<?php echo $schedule['id']; ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label">Area</label>
                                                                <input type="text" class="form-control" name="area" 
                                                                       value="<?php echo htmlspecialchars($schedule['area']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Schedule Date</label>
                                                                <input type="date" class="form-control" name="schedule_date" 
                                                                       value="<?php echo $schedule['schedule_date']; ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Schedule Time</label>
                                                                <input type="time" class="form-control" name="schedule_time" 
                                                                       value="<?php echo $schedule['schedule_time']; ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Waste Type</label>
                                                                <select class="form-select" name="waste_type" required>
                                                                    <option value="perishable" <?php echo $schedule['waste_type'] === 'perishable' ? 'selected' : ''; ?>>Perishable</option>
                                                                    <option value="non_perishable" <?php echo $schedule['waste_type'] === 'non_perishable' ? 'selected' : ''; ?>>Non-Perishable</option>
                                                                </select>
                                                            </div>
                                                            <div class="text-end">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary">Update Schedule</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 