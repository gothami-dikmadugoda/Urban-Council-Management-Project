<?php
session_start();
require_once __DIR__ . '/../../controllers/AdminController.php';

$adminController = new AdminController();
$adminController->validateAdminAccess();

$dashboardData = $adminController->getDashboardData();
$staffList = $dashboardData['staff_list'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - Urban Council Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #FF69B4;
            --secondary-color: #9370DB;
            --success-color: #3CB371;
            --warning-color: #FFD700;
            --info-color: #4169E1;
            --dark-color: #202020;
            --light-color: #F1F1F1;
            --cream-color: #FFFDD0;
            --border-radius: 15px;
            --box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
            --gradient-primary: linear-gradient(135deg, #FF69B4, #9370DB);
            --gradient-success: linear-gradient(135deg, #3CB371, #4169E1);
            --gradient-warning: linear-gradient(135deg, #FFD700, #FFFDD0);
            --card-bg: #202020;
            --text-light: #F1F1F1;
            --text-gray: #7E909A;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #151921;
            color: var(--text-light);
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--dark-color);
            color: var(--text-light);
            padding: 1.5rem;
            transition: var(--transition);
            box-shadow: var(--box-shadow);
            z-index: 1000;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: var(--transition);
        }

        .card {
            background: linear-gradient(145deg, #1a1f2b, #202632);
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .card-header {
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
            color: var(--text-light);
        }

        .table {
            color: var(--text-light);
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--text-gray);
            border-color: rgba(255, 255, 255, 0.05);
        }

        .table td {
            border-color: rgba(255, 255, 255, 0.05);
            vertical-align: middle;
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            color: white;
            transition: var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 105, 180, 0.3);
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            border-radius: var(--border-radius);
            font-weight: 500;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-color);
            color: var(--text-light);
            box-shadow: none;
            font-weight: 600;
        }

        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        }

        .modal-content {
            background: linear-gradient(145deg, #1a1f2b, #202632);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: var(--border-radius);
            color: var(--text-light);
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            background: var(--gradient-primary);
        }

        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
        }

        .badge.bg-success {
            background: var(--success-color) !important;
        }

        .badge.bg-danger {
            background: var(--warning-color) !important;
            color: #000;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 6px;
        }

        .btn-danger {
            background: var(--gradient-warning);
            border: none;
            color: #000;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }

        .input-group .btn {
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-light);
        }

        .input-group .btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Enhanced Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card {
            animation: fadeIn 0.5s ease-out;
        }

        /* Form Labels */
        .form-label {
            color: var(--text-light);
            font-weight: 500;
        }

        /* Placeholder Colors */
        ::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        /* Table Hover Effect */
        .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        /* Search Bar Enhancements */
        .search-container {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .search-container .input-group {
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--border-radius);
            padding: 0.3rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .search-container .form-control {
            border: none;
            background: transparent;
            color: var(--text-light);
            padding-left: 1rem;
            font-size: 0.95rem;
        }

        .search-container .form-control:focus {
            box-shadow: none;
            background: transparent;
        }

        .search-container .btn-search {
            background: var(--gradient-primary);
            border: none;
            color: white;
            border-radius: var(--border-radius);
            padding: 0.6rem 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .search-container .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 105, 180, 0.3);
        }

        .search-container .btn-search i {
            font-size: 1.2rem;
        }

        /* Form Select Enhancement */
        .form-select-custom {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            color: var(--text-light);
            padding: 0.6rem 2.5rem 0.6rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            /* Remove default arrow */
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            /* Custom arrow */
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
            width: 100%;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }

        .form-select-custom:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(255, 105, 180, 0.25);
        }

        /* Dropdown menu styling */
        .form-select-custom option {
            background-color: #1a1f2b;
            color: var(--text-light);
            padding: 10px;
            white-space: normal;
        }

        /* Modal select and options for better visibility */
        .modal-content select.form-control, .modal-content select.form-select {
            background: #23283a;
            color: #fff;
            font-weight: 600;
        }
        .modal-content select.form-control option, .modal-content select.form-select option {
            background: #23283a;
            color: #fff;
            font-weight: 600;
        }
        .modal-content select.form-control option[value=""],
        .modal-content select.form-select option[value=""] {
            color: #fff;
            font-weight: bold;
            opacity: 1;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include __DIR__ . '/includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Staff Management</h2>

            <!-- Staff List -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Staff Members</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                        <i class='bx bx-plus'></i> Add New Staff
                    </button>
                </div>
                <div class="card-body">
                    <!-- Add search bar -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="search-container">
                                <div class="input-group">
                                    <input type="text" id="staffSearch" class="form-control" placeholder="Search by name, email, department...">
                                    <button class="btn btn-search" type="button">
                                        <i class='bx bx-search-alt-2'></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select id="departmentFilter" class="form-select-custom">
                                <option value="">All Departments</option>
                                <option value="health">Health</option>
                                <option value="engineering">Engineering</option>
                                <option value="administration">Administration</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="statusFilter" class="form-select-custom">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
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
                                    <td><?php echo $staff['email']; ?></td>
                                    <td><?php echo $staff['phone']; ?></td>
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
                    <form id="addStaffForm" method="POST" action="/urban2/views/admin/add_staff.php">
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
                                <option value="garbage_collector">Garbage Collector</option>
                                <option value="field_visitor">Field Visitor</option>
                                <option value="moh_officer">MOH Officer</option>
                                <option value="complaint_manager">Complaint Manager</option>
                                <option value="it_staff">IT Staff</option>
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
    <script src="/urban2/assets/js/staff-validation.js"></script>
    <script>
        function editStaff(id) {
            window.location.href = `/urban2/views/admin/update_staff.php?id=${id}`;
        }

        function deleteStaff(id) {
            if (confirm('Are you sure you want to delete this staff member?')) {
                window.location.href = `/urban2/views/admin/delete_staff.php?id=${id}`;
            }
        }

        // Staff search and filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('staffSearch');
            const departmentFilter = document.getElementById('departmentFilter');
            const statusFilter = document.getElementById('statusFilter');
            const tableRows = document.querySelectorAll('tbody tr');

            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const department = departmentFilter.value.toLowerCase();
                const status = statusFilter.value.toLowerCase();

                tableRows.forEach(row => {
                    const name = row.cells[0].textContent.toLowerCase();
                    const email = row.cells[1].textContent.toLowerCase();
                    const dept = row.cells[3].textContent.toLowerCase();
                    const rowStatus = row.cells[5].textContent.toLowerCase();

                    const matchesSearch = name.includes(searchTerm) || 
                                       email.includes(searchTerm) || 
                                       dept.includes(searchTerm);
                    const matchesDepartment = department === '' || dept.includes(department);
                    const matchesStatus = status === '' || rowStatus.includes(status);

                    row.style.display = matchesSearch && matchesDepartment && matchesStatus ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', filterTable);
            departmentFilter.addEventListener('change', filterTable);
            statusFilter.addEventListener('change', filterTable);
        });
    </script>
</body>
</html> 