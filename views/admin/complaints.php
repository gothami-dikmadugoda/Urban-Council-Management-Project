<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'staff'])) {
    header('Location: /urban2/login.php');
    exit;
}
require_once '../../controllers/ComplaintController.php';
require_once '../../controllers/DepartmentController.php';
require_once '../../controllers/CitizenController.php';
$complaintController = new ComplaintController();
$departmentController = new DepartmentController();
$citizenController = new CitizenController();
$complaints = $complaintController->getAllComplaints();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaints List - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .main-wrapper { max-width: 1100px; margin: 0 auto; padding: 2.5rem 1.5rem; }
        .card { border-radius: 16px; box-shadow: 0 4px 24px rgba(61,54,92,0.08), 0 1.5px 4px rgba(61,54,92,0.06); }
        .table thead th { background: #3D365C; color: #fff; }
        .table tbody tr:hover { background: #f1e6f7; }
        .btn-view { border-radius: 8px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container main-wrapper">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Complaints List</h2>
            <a href="/urban2/views/admin/staff_dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
        <div class="card p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Department</th>
                            <th>Submitted By</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($complaints)): ?>
                            <?php foreach ($complaints as $complaint): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($complaint['id']); ?></td>
                                    <td><?php echo htmlspecialchars($complaint['title']); ?></td>
                                    <td><span class="badge bg-<?php
                                        switch ($complaint['status']) {
                                            case 'pending': echo 'warning'; break;
                                            case 'in_progress': echo 'info'; break;
                                            case 'resolved': echo 'success'; break;
                                            case 'closed': echo 'secondary'; break;
                                            default: echo 'light';
                                        }
                                    ?>"><?php echo ucfirst($complaint['status']); ?></span></td>
                                    <td><?php 
                                        $dept = $departmentController->getDepartmentById($complaint['department_id']);
                                        echo htmlspecialchars($dept ? $dept['name'] : '');
                                    ?></td>
                                    <td><?php 
                                        $citizen = $citizenController->getCitizenInfo($complaint['user_id']);
                                        echo htmlspecialchars($citizen ? ($citizen['first_name'] . ' ' . $citizen['last_name']) : '');
                                    ?></td>
                                    <td><?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></td>
                                    <td>
                                        <a href="/urban2/views/admin/view_complaint.php?id=<?php echo $complaint['id']; ?>" class="btn btn-sm btn-primary btn-view">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted">No complaints found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 