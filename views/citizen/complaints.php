<?php
session_start();
require_once '../../controllers/ComplaintController.php';
require_once '../../controllers/CitizenController.php';
require_once '../../controllers/DepartmentController.php';
require_once '../../controllers/AdminController.php';

// Check if user is logged in and is a citizen
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'citizen') {
    header('Location: /urban2/login.php');
    exit();
}

$complaintController = new ComplaintController();
$citizenController = new CitizenController();
$departmentController = new DepartmentController();
$adminController = new AdminController();

// Get citizen information
$citizenInfo = $citizenController->getCitizenInfo($_SESSION['user_id']);

// Define base URL
$baseUrl = '/urban2';

// Get all departments
$departments = $departmentController->getAllDepartments();

// Get all staff members
$staffMembers = $adminController->getAllStaff();

// Handle new complaint submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_complaint'])) {
    $data = [
        'user_id' => $_SESSION['user_id'],
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'category' => $_POST['category'],
        'priority' => $_POST['priority'],
        'department_id' => $_POST['department_id'],
        'assigned_to' => $_POST['assigned_to'],
        'status' => 'pending'
    ];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $data['image'] = file_get_contents($_FILES['image']['tmp_name']);
    }

    $result = $complaintController->createComplaint($data);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header('Location: /urban2/views/citizen/complaints.php');
        exit();
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
}

// Get all complaints for the citizen
$complaints = $complaintController->getComplaintsByUserId($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints - Urban Council</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --main-bg: #0d112b;
            --sidebar-bg: #181e3a;
            --card-bg: #16213e;
            --card-border: #22c55e;
            --accent: #22c55e;
            --btn-main: #22c55e;
            --btn-hover: #166534;
            --heading: #fff;
            --text: #e5e7eb;
        }
        body {
            background: var(--main-bg);
            font-family: 'Inter', 'Segoe UI', 'Roboto', Arial, sans-serif;
            color: var(--text);
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
            padding: 2.5rem 2rem 2.5rem 2rem;
            background: var(--main-bg);
            min-height: 100vh;
        }
        .complaints-title {
            color: var(--heading);
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        .complaints-card {
            background: var(--card-bg);
            border: 1.5px solid var(--card-border);
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(34,197,94,0.08);
            padding: 2.5rem 2rem 2rem 2rem;
            margin-bottom: 2rem;
            color: var(--text);
        }
        .complaints-table {
            background: var(--card-bg);
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(34,197,94,0.06);
            overflow: hidden;
        }
        .table thead th {
            background: var(--sidebar-bg);
            color: var(--accent);
            font-weight: 700;
            font-size: 1.13rem;
            letter-spacing: 0.5px;
            border-bottom: 2.5px solid var(--card-border);
            text-transform: uppercase;
            padding-top: 1.1rem;
            padding-bottom: 1.1rem;
            box-shadow: 0 2px 8px rgba(34,197,94,0.04);
            transition: background 0.18s;
            border-left: 5px solid var(--accent);
        }
        .table thead th:hover {
            background: #22325c;
            color: var(--btn-hover);
        }
        .table tbody tr {
            border-bottom: 2px solid var(--card-border);
            transition: background 0.18s, box-shadow 0.18s, transform 0.18s;
        }
        .table tbody tr:hover {
            background: #22325c;
            box-shadow: 0 2px 12px rgba(34,197,94,0.10);
            transform: scale(1.01);
        }
        .table tbody tr:nth-child(even) {
            background: #181e3a;
        }
        .table tbody td:last-child {
            background: #181e3a;
            border-radius: 0 10px 10px 0;
        }
        .complaint-title {
            font-weight: 700;
            font-size: 1.08rem;
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            color: var(--heading);
        }
        .category-badge {
            display: inline-block;
            background: #22325c;
            color: var(--accent);
            font-weight: 600;
            border-radius: 12px;
            padding: 0.35rem 0.9rem;
            font-size: 0.98rem;
            letter-spacing: 0.2px;
            box-shadow: 0 1px 4px rgba(34,197,94,0.06);
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4em;
            padding: 0.35rem 0.8rem;
            border-radius: 12px;
            font-size: 0.98rem;
            font-weight: 600;
            box-shadow: 0 1px 4px rgba(34,197,94,0.06);
            background: #22325c;
            color: var(--accent);
        }
        .status-badge i {
            font-size: 1em;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-in_progress {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-resolved {
            background-color: #d4edda;
            color: #155724;
        }
        .date-cell {
            color: #6c757d;
            font-size: 1.01rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.4em;
        }
        .view-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4em;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            padding: 0.45rem 1.1rem;
            box-shadow: 0 1px 4px rgba(34,197,94,0.08);
            transition: background 0.18s, box-shadow 0.18s;
        }
        .view-btn:hover {
            background: var(--btn-hover);
            color: #fff;
            box-shadow: 0 2px 8px rgba(34,197,94,0.10);
        }
        .btn-primary, .btn-primary:focus {
            background: var(--btn-main);
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.08rem;
            padding: 0.7rem 1.7rem;
            box-shadow: 0 2px 8px rgba(34,197,94,0.10);
            margin-top: 0.5rem;
            letter-spacing: 0.1px;
            transition: background 0.18s, box-shadow 0.18s;
            color: #fff;
        }
        .btn-primary:hover {
            background: var(--btn-hover);
            color: #fff;
            box-shadow: 0 4px 16px rgba(34,197,94,0.13);
        }
        .modal-content {
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(34,197,94,0.10);
        }
        .modal-header {
            border-radius: 18px 18px 0 0;
            background: #f4f6fb;
        }
        .modal-title {
            color: var(--accent);
            font-weight: 700;
        }
        .form-label {
            font-weight: 700;
            color: var(--accent);
            font-size: 1.08rem;
            letter-spacing: 0.1px;
        }
        .form-control, .form-select {
            border-radius: 12px;
            border: 1.5px solid var(--card-border);
            padding: 1rem 1.3rem;
            font-size: 1.08rem;
            background: #22325c;
            color: var(--text);
            transition: border-color 0.18s, box-shadow 0.18s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(34,197,94,0.18);
            background: #16213e;
            color: #fff;
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
        @media (max-width: 768px) {
            .sidebar {
                position: static;
                width: 100%;
                height: auto;
            }
            .main-content {
                margin-left: 0;
            }
            .main-content {
                padding: 1.2rem 0.5rem 1.2rem 0.5rem;
            }
            .complaints-card {
                padding: 1.2rem 0.7rem 1.2rem 0.7rem;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="profile-section">
                <img src="<?php echo $citizenInfo['profile_image'] ?? $baseUrl . '/assets/images/profiles/67ebd858c1d36_tech1-student-management.jpg'; ?>" 
                     alt="Profile Image">
                <h5 class="text-white mb-1"><?php echo htmlspecialchars($citizenInfo['name']); ?></h5>
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
                    <a class="nav-link" href="/urban2/views/citizen/notifications.php">
                        <i class="fas fa-bell"></i> Notifications
                        <?php if (isset($citizenInfo['unread_notifications']) && $citizenInfo['unread_notifications'] > 0): ?>
                            <span class="badge bg-danger ms-2"><?php echo $citizenInfo['unread_notifications']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/citizen/settings.php">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="<?php echo $baseUrl; ?>/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="complaints-title">My Complaints</h2>
                    </div>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-4 complaints-card">
                    <div>
                        <span class="fw-bold" style="font-size:1.2rem; color:var(--accent);">All your submitted complaints are listed below.</span>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newComplaintModal">
                        <i class="fas fa-plus"></i> New Complaint
                    </button>
                </div>

                <?php if (empty($complaints)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-exclamation-circle" style="font-size: 4rem; color: #ddd;"></i>
                        <p class="mt-3">You haven't submitted any complaints yet.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newComplaintModal">
                            Submit Your First Complaint
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive complaints-table mb-4">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Submitted Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($complaints as $complaint): ?>
                                    <tr>
                                        <td>
                                            <span class="complaint-title" title="<?php echo htmlspecialchars($complaint['title']); ?>">
                                                <?php echo htmlspecialchars($complaint['title']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="category-badge" title="<?php echo ucfirst(htmlspecialchars($complaint['category'])); ?>">
                                                <?php echo ucfirst(htmlspecialchars($complaint['category'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $complaint['status']; ?>">
                                                <?php if ($complaint['status'] === 'pending'): ?>
                                                    <i class="fas fa-hourglass-half"></i>
                                                <?php elseif ($complaint['status'] === 'in_progress'): ?>
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                <?php elseif ($complaint['status'] === 'resolved'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-info-circle"></i>
                                                <?php endif; ?>
                                                <?php echo ucfirst(htmlspecialchars($complaint['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="date-cell">
                                                <i class="far fa-calendar-alt"></i>
                                                <?php echo date('M d, Y', strtotime($complaint['created_at'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="/urban2/views/view_complaint.php?id=<?php echo $complaint['id']; ?>" 
                                               class="view-btn">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- New Complaint Modal -->
    <div class="modal fade" id="newComplaintModal" tabindex="-1" aria-labelledby="newComplaintModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newComplaintModalLabel">Submit New Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-select" id="department_id" name="department_id" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $department): ?>
                                    <option value="<?php echo $department['id']; ?>">
                                        <?php echo htmlspecialchars($department['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required disabled>
                                <option value="">Select Department First</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Assign To (Optional)</label>
                            <select class="form-select" id="assigned_to" name="assigned_to" disabled>
                                <option value="">Select Department First</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Upload Image (Optional)</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">Maximum file size: 5MB</small>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="submit_complaint" class="btn btn-primary">Submit Complaint</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const departmentSelect = document.getElementById('department_id');
        const categorySelect = document.getElementById('category');
        const staffSelect = document.getElementById('assigned_to');

        departmentSelect.addEventListener('change', function() {
            const departmentId = this.value;
            
            // Reset and disable dependent dropdowns if no department is selected
            if (!departmentId) {
                categorySelect.innerHTML = '<option value="">Select Department First</option>';
                staffSelect.innerHTML = '<option value="">Select Department First</option>';
                categorySelect.disabled = true;
                staffSelect.disabled = true;
                return;
            }

            // Fetch department-specific data
            fetch(`/urban2/api/get-department-data.php?department_id=${departmentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update categories dropdown
                        categorySelect.innerHTML = '<option value="">Select Category</option>';
                        data.categories.forEach(category => {
                            const option = new Option(category.name, category.id);
                            categorySelect.add(option);
                        });
                        categorySelect.disabled = false;

                        // Update staff dropdown
                        staffSelect.innerHTML = '<option value="">Select Staff Member</option>';
                        data.staff.forEach(staff => {
                            const option = new Option(staff.name, staff.id);
                            staffSelect.add(option);
                        });
                        staffSelect.disabled = false;
                    } else {
                        console.error('Error loading department data:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    });
    </script>
</body>
</html> 