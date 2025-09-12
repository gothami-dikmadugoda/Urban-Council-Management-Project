<?php
session_start();
require_once __DIR__ . '/../../controllers/AdminController.php';

$adminController = new AdminController();
$adminController->validateAdminAccess();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_profile':
            $adminController->updateProfile($_POST);
            break;
        case 'update_password':
            $adminController->updatePassword($_POST);
            break;
        case 'update_system':
            $adminController->updateSystemSettings($_POST);
            break;
    }
}

// Get current settings
$settings = $adminController->getSettings();
$profile = $adminController->getProfile();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Urban Council Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #0091D5;
            --secondary-color: #1C4E80;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --info-color: #0091D5;
            --dark-color: #202020;
            --light-color: #F1F1F1;
            --gray-color: #7E909A;
            --border-radius: 15px;
            --box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
            --gradient-primary: linear-gradient(135deg, #1C4E80, #0091D5);
            --gradient-success: linear-gradient(135deg, #28a745, #20c997);
            --gradient-warning: linear-gradient(135deg, #ffc107, #fd7e14);
            --gradient-info: linear-gradient(135deg, #0091D5, #1C4E80);
            --card-bg: #202020;
            --text-light: #F1F1F1;
            --text-gray: #7E909A;
        }

        /* Enhanced Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { transform: translateX(-100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #151921;
            color: var(--text-light);
            animation: fadeIn 0.5s ease-out;
        }

        /* Enhanced Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 280px;
            background: #2c3e50;
            padding: 20px;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h3 {
            color: white;
            font-size: 1.2rem;
            margin: 0;
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #ecf0f1;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .nav-link.active {
            background: #3498db;
            color: #fff;
        }

        .nav-link i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .nav-text {
            font-size: 0.95rem;
        }

        .logout-link {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: var(--transition);
        }

        /* Enhanced Cards */
        .card {
            border: none;
            border-radius: var(--border-radius);
            background: linear-gradient(145deg, #1a1f2b, #202632);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(
                circle at top right,
                rgba(165, 216, 221, 0.05),
                rgba(0, 145, 213, 0.05),
                transparent 50%
            );
            pointer-events: none;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }

        .card-header {
            background: rgba(28, 78, 128, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .form-control {
            border-radius: 12px;
            padding: 0.8rem 1rem;
            border: 2px solid #eee;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: none;
        }
        .form-label {
            font-weight: 500;
            color: #666;
        }
        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: var(--gradient-info);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 145, 213, 0.3);
        }
        .alert {
            border-radius: 12px;
            border: none;
        }

        /* Form Styles */
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            border-radius: var(--border-radius);
            padding: 0.8rem 1.2rem;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 145, 213, 0.2);
            color: var(--text-light);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-label {
            font-weight: 500;
            color: var(--text-light);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .form-text {
            color: var(--text-gray);
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: var(--gradient-warning);
            transform: translateY(-2px);
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-label {
            color: var(--text-light);
            opacity: 0.9;
        }

        .form-section {
            background: rgba(255, 255, 255, 0.02);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
        }

        .card-header h5 {
            color: var(--text-light);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-header h5 i {
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        .alert {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            border-color: rgba(40, 167, 69, 0.2);
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border-color: rgba(220, 53, 69, 0.2);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Urban Council Admin</h3>
        </div>
        <ul class="nav-list">
            <li class="nav-item">
                <a href="/urban2/" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class='bx bxs-home'></i>
                    <span class="nav-text">Home</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/urban2/views/admin/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class='bx bxs-dashboard'></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/urban2/views/admin/staff.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'staff.php' ? 'active' : ''; ?>">
                    <i class='bx bxs-user-detail'></i>
                    <span class="nav-text">Manage Staff</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/urban2/views/admin/complaints.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'complaints.php' ? 'active' : ''; ?>">
                    <i class='bx bxs-message-square-detail'></i>
                    <span class="nav-text">Complaints</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/urban2/views/admin/track_staff.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'track_staff.php' ? 'active' : ''; ?>">
                    <i class='bx bxs-map'></i>
                    <span class="nav-text">Track Staff</span>
            </a>
            </li>
            <li class="nav-item">
                <a href="/urban2/views/admin/reports.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                    <i class='bx bxs-report'></i>
                    <span class="nav-text">Reports</span>
            </a>
            </li>
            <li class="nav-item">
                <a href="/urban2/views/admin/settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                    <i class='bx bxs-cog'></i>
                    <span class="nav-text">Settings</span>
                </a>
            </li>
        </ul>
        <div class="logout-link">
            <a href="/urban2/logout.php" class="nav-link">
                <i class='bx bxs-log-out'></i>
                <span class="nav-text">Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Settings</h2>
                <div class="d-flex align-items-center">
                    <span class="me-3">Welcome, <?php echo htmlspecialchars($profile['first_name']); ?></span>
                    <div class="dropdown">
                        <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class='bx bxs-user-circle'></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                            <li><a class="dropdown-item" href="/urban2/views/admin/profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="/urban2/views/admin/settings.php">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/urban2/logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Profile Settings -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class='bx bxs-user-circle'></i> Profile Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-section">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($profile['first_name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($profile['last_name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($profile['phone']); ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="3" required><?php echo htmlspecialchars($profile['address']); ?></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bxs-save'></i> Update Profile
                        </button>
                    </form>
                </div>
            </div>

            <!-- Password Settings -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class='bx bxs-lock-alt'></i> Password Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_password">
                        <div class="form-section">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control" name="current_password" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="new_password" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" name="confirm_password" required>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bxs-lock'></i> Update Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- System Settings -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class='bx bxs-cog'></i> System Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_system">
                        <div class="form-section">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" class="form-control" name="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Email</label>
                                    <input type="email" class="form-control" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email']); ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Site Description</label>
                                    <textarea class="form-control" name="site_description" rows="3" required><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Phone</label>
                                    <input type="tel" class="form-control" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="1" required><?php echo htmlspecialchars($settings['address']); ?></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="maintenance_mode" <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Maintenance Mode</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bxs-save'></i> Update System Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 