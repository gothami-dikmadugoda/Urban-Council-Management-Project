<?php
session_start();
require_once __DIR__ . '/../controllers/AdminController.php';

$adminController = new AdminController();
$adminController->validateAdminAccess();

$dashboardData = $adminController->getDashboardData();
$stats = $dashboardData['stats'];
$staffList = $dashboardData['staff_list'];
$recentActivities = $dashboardData['recent_activities'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Urban Council Management System</title>
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
        .table {
            margin-bottom: 0;
        }
        .table th {
            border-top: none;
            font-weight: 600;
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
        .stats-card {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .stats-card h3 {
            margin: 0;
            font-size: 2rem;
            font-weight: 600;
        }
        .stats-card p {
            margin: 0;
            opacity: 0.8;
        }
        .search-bar {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h4 class="mb-4">Admin Panel</h4>
        <nav class="nav flex-column">
            <a class="nav-link active" href="/urban2/admin/dashboard.php">
                <i class='bx bxs-dashboard'></i> Dashboard
            </a>
            <a class="nav-link" href="/urban2/admin/staff.php">
                <i class='bx bxs-user-detail'></i> Manage Staff
            </a>
            <a class="nav-link" href="/urban2/admin/complaints.php">
                <i class='bx bxs-report'></i> Complaints
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
            <h2 class="mb-4">Dashboard Overview</h2>

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stats-card">
                        <h3><?php echo $stats['total_staff']; ?></h3>
                        <p>Total Staff Members</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h3><?php echo $stats['total_citizens']; ?></h3>
                        <p>Total Citizens</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h3><?php echo $stats['total_companies']; ?></h3>
                        <p>Total Companies</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h3><?php echo $stats['total_staff'] + $stats['total_citizens'] + $stats['total_companies']; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
            </div>

            <!-- Staff List Search -->
            <div class="search-bar">
                <input type="text" class="form-control" placeholder="Search Staff..." id="staffSearch" onkeyup="searchStaff()">
            </div>

            <!-- Staff List -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Staff Members</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                        <i class='bx bx-plus'></i> Add New Staff
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="staffTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($staffList as $staff): ?>
                                <tr>
                                    <td><?php echo $staff['first_name'] . ' ' . $staff['last_name']; ?></td>
                                    <td><?php echo ucfirst($staff['department']); ?></td>
                                    <td><?php echo ucfirst($staff['job_role']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $staff['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($staff['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="editStaff(<?php echo $staff['id']; ?>)">
                                            <i class='bx bx-edit'></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteStaff(<?php echo $staff['id']; ?>)">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
            </div>
        </div>
    </div>
</div>

<!-- Predictive Analytics Section -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Predictive Analytics</h5>
            </div>
            <div class="card-body">
                <?php
                // Assuming $dashboardData is available and contains predictive analytics
                if (isset($dashboardData['predictive_analytics'])) {
                    foreach ($dashboardData['predictive_analytics'] as $data) {
                        echo "<p>Date: " . htmlspecialchars($data['complaint_date']) . " - Type: " . htmlspecialchars($data['complaint_type']) . " - Count: " . htmlspecialchars($data['count']) . "</p>";
                    }
                } else {
                    echo "<p>No predictive analytics data available.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</div>
    </div>

    <!-- Add Staff Modal -->
    <div class="modal fade" id="addStaffModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Staff Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addStaffForm" method="POST" action="/urban2/admin/add_staff.php">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select class="form-control" name="department" required>
                                <option value="">Select Department</option>
                                <option value="health">Health</option>
                                <option value="engineering">Engineering</option>
                                <option value="it">IT</option>
                                <option value="reception">Reception</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Job Role</label>
                            <select class="form-control" name="job_role" required>
                                <option value="">Select Role</option>
                                <option value="garbage_manager">Garbage Manager</option>
                                <option value="moh_officer">MOH Officer</option>
                                <option value="engineer">Engineer</option>
                                <option value="it_officer">IT Officer</option>
                                <option value="receptionist">Receptionist</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="addStaffForm" class="btn btn-primary">Add Staff</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editStaff(id) {
            window.location.href = `/urban2/admin/update_staff.php?id=${id}`;
        }

        function deleteStaff(id) {
            if (confirm('Are you sure you want to delete this staff member?')) {
                window.location.href = `/urban2/admin/delete_staff.php?id=${id}`;
            }
        }

        function searchStaff() {
            const input = document.getElementById('staffSearch').value.toLowerCase();
            const rows = document.querySelectorAll('#staffTable tbody tr');
            rows.forEach(row => {
                const name = row.cells[0].textContent.toLowerCase();
                row.style.display = name.includes(input) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
