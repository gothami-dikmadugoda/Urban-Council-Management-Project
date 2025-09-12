<?php
session_start();
require_once __DIR__ . '/../../controllers/ComplaintController.php';
require_once __DIR__ . '/../../controllers/AdminController.php';

// Validate staff access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'staff'])) {
    header('Location: /urban2/login.php');
    exit;
}

$complaintController = new ComplaintController();
$adminController = new AdminController();
$complaintId = $_GET['id'] ?? null;

if (!$complaintId) {
    $_SESSION['error_message'] = "Invalid complaint ID";
    header('Location: /urban2/views/admin/assigned_complaints.php');
    exit;
}

// Get complaint details
$complaint = $complaintController->getComplaintById($complaintId);
$notes = $complaintController->getComplaintNotes($complaintId);

// Handle note submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['note'])) {
    $result = $complaintController->addComplaintNote($complaintId, $_POST['note']);
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header("Location: /urban2/views/admin/view_complaint.php?id=$complaintId");
        exit;
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $result = $complaintController->updateComplaintStatus($complaintId, $_POST['status']);
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header("Location: /urban2/views/admin/view_complaint.php?id=$complaintId");
        exit;
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
}

$staffDetails = $adminController->getStaffDetails($_SESSION['user_id']);
$staff = isset($staffDetails['data']) ? $staffDetails['data'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Complaint - Urban Council Management System</title>
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
            --sidebar-width: 250px;
        }
        body {
            background: linear-gradient(135deg, #f6f8fd 0%, #f1f4f9 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark);
            line-height: 1.6;
        }
        .main-content {
            padding: 3.5rem 2.5rem 3.5rem 2.5rem;
            background: transparent;
            min-height: 100vh;
            margin-left: 0;
        }
        .container-fluid {
            max-width: 900px;
            margin: 0 auto;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(61,54,92,0.10), 0 1.5px 4px rgba(61,54,92,0.06);
            margin-bottom: 2.5rem;
            background: #fff;
            padding: 2.2rem 2rem 2rem 2rem;
        }
        .card-header {
            background: var(--primary);
            color: #fff;
            border-bottom: 1px solid #eee;
            padding: 1.5rem 2rem;
            border-radius: 20px 20px 0 0 !important;
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 1.2rem;
        }
        .card-body {
            padding: 0 0.5rem 0.5rem 0.5rem;
        }
        .row {
            margin-bottom: 1.5rem;
        }
        .note-item {
            border-left: 4px solid var(--accent);
            background: #f8f9fa;
            padding: 1.2rem 1.2rem 1.2rem 1.5rem;
            margin-bottom: 1.2rem;
            border-radius: 10px;
        }
        .note-meta {
            font-size: 0.95rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
        }
        .form-label {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.4rem;
        }
        textarea.form-control {
            min-height: 90px;
            border-radius: 10px;
            border: 1.5px solid #e0e0e0;
            background: #f8fafc;
            color: var(--dark);
            font-size: 1.08rem;
            margin-bottom: 1rem;
        }
        .btn-primary {
            background: var(--accent);
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.08rem;
            padding: 0.7rem 1.7rem;
            box-shadow: 0 2px 8px rgba(201,87,146,0.10);
            margin-top: 0.5rem;
            letter-spacing: 0.1px;
            transition: background 0.18s, box-shadow 0.18s;
            color: #fff;
        }
        .btn-primary:hover {
            background: var(--secondary);
            color: #fff;
            box-shadow: 0 4px 16px rgba(201,87,146,0.13);
        }
        @media (max-width: 900px) {
            .main-content {
                padding: 1.2rem 0.5rem 1.2rem 0.5rem;
            }
            .container-fluid {
                padding: 0 0.5rem;
            }
            .card {
                padding: 1.2rem 0.7rem 1.2rem 0.7rem;
            }
            .card-header {
                padding: 1rem 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="mb-4">
                <?php
                    $dashboardUrl = "/urban2/views/admin/staff_dashboard.php";
                    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                        $dashboardUrl = "/urban2/views/admin/dashboard.php";
                    }
                ?>
                <a href="<?php echo $dashboardUrl; ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
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

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Complaint Details</h5>
                    <form method="POST" class="d-flex align-items-center">
                        <select name="status" class="form-select me-2">
                            <option value="pending" <?php echo $complaint['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="in_progress" <?php echo $complaint['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo $complaint['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-primary">
                            Update Status
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6>Title</h6>
                            <p><?php echo htmlspecialchars($complaint['title']); ?></p>
                            
                            <h6>Description</h6>
                            <p><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></p>
                            
                            <h6>Category</h6>
                            <p><?php echo ucfirst($complaint['category']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <h6>Status</h6>
                            <span class="badge bg-<?php echo $complaint['status'] === 'pending' ? 'warning' : 
                                ($complaint['status'] === 'in_progress' ? 'info' : 'success'); ?>">
                                <?php echo ucfirst($complaint['status']); ?>
                            </span>
                            
                            <h6 class="mt-3">Submitted By</h6>
                            <p><?php echo htmlspecialchars($complaint['user_name'] ?? 'Unknown User'); ?></p>
                            
                            <h6>Date</h6>
                            <p><?php echo date('M d, Y H:i', strtotime($complaint['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Complaint Notes</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($notes)): ?>
                    <p class="text-muted">No notes yet</p>
                    <?php else: ?>
                    <?php foreach ($notes as $note): ?>
                    <div class="note-item">
                        <div class="note-meta mb-2">
                            <strong><?php echo htmlspecialchars($note['user_name']); ?></strong>
                            <span class="ms-2"><?php echo date('M d, Y H:i', strtotime($note['created_at'])); ?></span>
                        </div>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($note['note'])); ?></p>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <form method="POST" class="mt-4">
                        <div class="mb-3">
                            <label class="form-label">Add Note</label>
                            <textarea name="note" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Note</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 