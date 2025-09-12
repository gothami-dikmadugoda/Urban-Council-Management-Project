<?php
session_start();
require_once __DIR__ . '/../../controllers/AdminController.php';

$adminController = new AdminController();
$adminController->validateAdminAccess();

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid staff ID";
    header('Location: /urban2/views/admin/staff.php');
    exit;
}

$staffId = $_GET['id'];
$staffDetails = $adminController->getStaffDetails($staffId);

if (!$staffDetails['success']) {
    $_SESSION['error_message'] = $staffDetails['message'];
    header('Location: /urban2/views/admin/staff.php');
    exit;
}

$staff = $staffDetails['data'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $adminController->updateStaff($staffId, $_POST);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header('Location: /urban2/views/admin/staff.php');
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
    <title>Update Staff - Urban Council Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 280px;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #151921;
            color: #F1F1F1;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: #2c3e50;
            color: #ecf0f1;
            padding: 20px;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2.5rem 2rem 2rem 2rem;
            min-height: 100vh;
            background: linear-gradient(135deg, #151921 0%, #23283a 100%);
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
            background: linear-gradient(145deg, #1a1f2b, #202632);
            border: none;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(61, 54, 92, 0.10);
            margin-bottom: 2rem;
            padding: 2.5rem 2rem 2rem 2rem;
        }
        .card-header {
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1.5rem 1.5rem 1rem 1.5rem;
            border-radius: 18px 18px 0 0 !important;
            color: #F1F1F1;
        }
        .form-label {
            color: #F1F1F1;
            font-weight: 500;
        }
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #F1F1F1;
            border-radius: 15px;
            font-weight: 500;
            padding: 0.75rem 1.2rem;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #FF69B4;
            color: #F1F1F1;
            box-shadow: none;
            font-weight: 600;
        }
        .btn-primary {
            background: linear-gradient(135deg, #FF69B4, #9370DB);
            border: none;
            padding: 0.7rem 2rem;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #9370DB, #FF69B4);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 105, 180, 0.3);
        }
        .btn-secondary {
            background: #23283a;
            color: #fff;
            border: none;
            padding: 0.7rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background: #151921;
            color: #fff;
        }
        .mb-3 {
            margin-bottom: 1.5rem !important;
        }
        .d-flex.justify-content-between {
            gap: 1rem;
        }
        .text-muted, .form-text, .invalid-feedback, .valid-feedback {
            color: #FFD700 !important; /* Bright gold for visibility */
            font-weight: 500;
            letter-spacing: 0.02em;
        }
        ::placeholder {
            color: #bfc2c7 !important;
            opacity: 1;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Update Staff Member</h2>

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="id" value="<?php echo $staff['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" value="<?php echo $staff['first_name']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" value="<?php echo $staff['last_name']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo $staff['email']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current password">
                            <small class="text-muted">Only fill this if you want to change the password</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" value="<?php echo $staff['phone']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" required><?php echo $staff['address']; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select class="form-control" name="department" required>
                                <option value="">Select Department</option>
                                <option value="health" <?php echo $staff['department'] === 'health' ? 'selected' : ''; ?>>Health</option>
                                <option value="engineering" <?php echo $staff['department'] === 'engineering' ? 'selected' : ''; ?>>Engineering</option>
                                <option value="it" <?php echo $staff['department'] === 'it' ? 'selected' : ''; ?>>IT</option>
                                <option value="reception" <?php echo $staff['department'] === 'reception' ? 'selected' : ''; ?>>Reception</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Job Role</label>
                            <select class="form-control" name="job_role" required>
                                <option value="">Select Role</option>
                                <option value="garbage_manager" <?php echo $staff['job_role'] === 'garbage_manager' ? 'selected' : ''; ?>>Garbage Manager</option>
                                <option value="garbage_collector" <?php echo $staff['job_role'] === 'garbage_collector' ? 'selected' : ''; ?>>Garbage Collector</option>
                                <option value="field_visitor" <?php echo $staff['job_role'] === 'field_visitor' ? 'selected' : ''; ?>>Field Visitor</option>
                                <option value="moh_officer" <?php echo $staff['job_role'] === 'moh_officer' ? 'selected' : ''; ?>>MOH Officer</option>
                                <option value="complaint_manager" <?php echo $staff['job_role'] === 'complaint_manager' ? 'selected' : ''; ?>>Complaint Manager</option>
                                <option value="it_staff" <?php echo $staff['job_role'] === 'it_staff' ? 'selected' : ''; ?>>IT Staff</option>
                                <option value="receptionist" <?php echo $staff['job_role'] === 'receptionist' ? 'selected' : ''; ?>>Receptionist</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="active" <?php echo $staff['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $staff['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/urban2/views/admin/staff.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Staff</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 