<?php
session_start();
require_once '../controllers/ComplaintController.php';
require_once '../controllers/CitizenController.php';
require_once '../controllers/DepartmentController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /urban2/login.php');
    exit;
}

// Check if complaint ID is provided
if (!isset($_GET['id'])) {
    header('Location: /urban2/views/citizen/complaints.php');
    exit;
}

$complaintController = new ComplaintController();
$citizenController = new CitizenController();
$departmentController = new DepartmentController();

// Get complaint details
$complaint = $complaintController->getComplaintById($_GET['id']);

// Check if complaint exists and user has access
if (!$complaint || ($complaint['user_id'] !== $_SESSION['user_id'] && !in_array($_SESSION['user_role'], ['admin', 'staff']))) {
    $_SESSION['error_message'] = "You don't have permission to view this complaint.";
    header('Location: /urban2/views/citizen/complaints.php');
    exit;
}

// Get department name
$department = $departmentController->getDepartmentById($complaint['department_id']);

// Get complaint notes/updates
$notes = $complaintController->getComplaintNotes($complaint['id']);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && in_array($_SESSION['user_role'], ['admin', 'staff'])) {
    if (!empty($_POST['status'])) {
        $result = $complaintController->updateComplaintStatus($complaint['id'], $_POST['status']);
        if ($result['success']) {
            header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $complaint['id']);
        exit;
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
}

// Handle note submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note']) && in_array($_SESSION['user_role'], ['admin', 'staff'])) {
    if (!empty($_POST['note'])) {
        $result = $complaintController->addComplaintNote($complaint['id'], $_POST['note']);
        if ($result['success']) {
            header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $complaint['id']);
        exit;
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
}

function getValue($array, $key, $default = '') {
    return isset($array[$key]) ? $array[$key] : $default;
}

// Get citizen information
$citizenInfo = $citizenController->getCitizenInfo($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Complaint - Urban Council</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        /* ====== Base & Typography ====== */
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Inter', 'Segoe UI', 'Roboto', Arial, sans-serif;
            color: #23223A;
        }
        /* ====== Layout Containers ====== */
        .main-wrapper {
            max-width: 900px;
            margin: 0 auto;
            padding: 3rem 2rem 3rem 2rem;
            font-size: 1.08rem;
        }
        /* ====== Card Styles ====== */
        .complaint-header, .complaint-details {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 6px 32px rgba(61,54,92,0.10), 0 2px 8px rgba(61,54,92,0.06);
            margin-bottom: 2.5rem;
        }
        .complaint-header {
            padding: 2.5rem 2.5rem 2rem 2.5rem;
        }
        .complaint-details {
            padding: 2.5rem 2.5rem 2rem 2.5rem;
            font-size: 1.07rem;
        }
        /* ====== Headings ====== */
        .complaint-header h2 {
            font-size: 2.2rem;
            font-weight: 900;
            color: #6B2872;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }
        .complaint-details h4 {
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 1.2rem;
            color: #6B2872;
            letter-spacing: 0.2px;
        }
        /* ====== Status Badge ====== */
        .status-badge {
            padding: 10px 22px;
            border-radius: 22px;
            font-weight: 700;
            font-size: 1.08rem;
            font-family: inherit;
            letter-spacing: 0.5px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            transition: background 0.18s, color 0.18s;
        }
        .status-badge:hover, .status-badge:focus {
            filter: brightness(0.97) saturate(1.2);
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-in_progress { background: #cce5ff; color: #004085; }
        .status-resolved { background: #d4edda; color: #155724; }
        .status-closed { background: #e2e3e5; color: #383d41; }
        /* ====== Timeline ====== */
        .timeline {
            position: relative;
            padding: 0.5rem 0 0.5rem 0.5rem;
            font-size: 1.01rem;
        }
        .timeline-item {
            padding: 1.2rem 1.5rem 1.2rem 2.5rem;
            border-left: 3px solid #dee2e6;
            position: relative;
            margin-left: 0.5rem;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 1.3rem;
            box-shadow: 0 2px 8px rgba(61,54,92,0.06);
            font-size: 1.01rem;
            transition: box-shadow 0.18s, background 0.18s;
        }
        .timeline-item:hover {
            background: #f0eaff;
            box-shadow: 0 4px 16px rgba(107,40,114,0.08);
        }
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -13px;
            top: 1.2rem;
            width: 16px;
            height: 16px;
            background: linear-gradient(135deg, #3D365C, #7C4585);
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 2px 6px rgba(61,54,92,0.10);
        }
        /* ====== Image Styles ====== */
        .complaint-image {
            max-width: 260px;
            height: auto;
            border-radius: 16px;
            margin-top: 1.5rem;
            box-shadow: 0 2px 12px rgba(61,54,92,0.12);
        }
        /* ====== Form Elements ====== */
        .form-label {
            font-weight: 800;
            color: #6B2872;
            font-size: 1.08rem;
            letter-spacing: 0.1px;
        }
        .form-control {
            border-radius: 12px;
            padding: 1rem 1.3rem;
            border: 1.5px solid #e0e0e0;
            font-size: 1.08rem;
            transition: border-color 0.18s, box-shadow 0.18s;
        }
        .form-control:focus {
            border-color: #7C4585;
            box-shadow: 0 0 0 0.2rem rgba(124,69,133,0.10);
        }
        /* ====== Buttons ====== */
        .btn-primary {
            background: linear-gradient(135deg, #7C4585, #C95792);
            border: none;
            border-radius: 10px;
            font-weight: 700;
            padding: 0.7rem 1.7rem;
            font-size: 1.08rem;
            box-shadow: 0 2px 8px rgba(201,87,146,0.10);
            margin-top: 0.5rem;
            letter-spacing: 0.1px;
            transition: background 0.18s, box-shadow 0.18s;
        }
        .btn-primary:hover, .btn-primary:focus {
            background: linear-gradient(135deg, #C95792, #7C4585);
            box-shadow: 0 4px 16px rgba(201,87,146,0.13);
        }
        .btn-outline-primary {
            border-radius: 10px;
            font-weight: 800;
            padding: 0.7rem 1.7rem;
            font-size: 1.08rem;
            margin-bottom: 1.5rem;
            letter-spacing: 0.1px;
            transition: border-color 0.18s, color 0.18s, background 0.18s;
        }
        .btn-outline-primary:hover, .btn-outline-primary:focus {
            background: #f4eaff;
            color: #6B2872;
            border-color: #7C4585;
        }
        /* ====== Utility Spacing ====== */
        .mt-3, .mt-4 {
            margin-top: 2rem !important;
        }
        .mb-4 {
            margin-bottom: 2.5rem !important;
        }
        .mb-3 {
            margin-bottom: 1.5rem !important;
        }
        .alert {
            border-radius: 12px;
            font-size: 1.08rem;
            font-family: inherit;
        }
        /* ====== Responsive ====== */
        @media (max-width: 900px) {
            .main-wrapper, .complaint-header, .complaint-details {
                padding: 1.5rem 0.7rem 1.5rem 0.7rem;
            }
            .timeline-item {
                padding: 0.9rem 0.7rem 0.9rem 1.5rem;
            }
        }
        @media (max-width: 600px) {
            .main-wrapper, .complaint-header, .complaint-details {
                padding: 1.1rem 0.3rem 1.1rem 0.3rem;
            }
            .timeline-item {
                padding: 0.7rem 0.3rem 0.7rem 1.1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4 main-wrapper">
        <div class="mb-4">
            <?php
            $backUrl = '/urban2/views/citizen/complaints.php';
            if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'staff'])) {
                $backUrl = '/urban2/views/admin/complaints.php';
            }
            ?>
            <a href="<?php echo $backUrl; ?>" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Complaints
            </a>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($citizenInfo && !$citizenInfo['profile_completed']): ?>
            <div class="alert alert-warning">
                Please complete your profile setup first.
            </div>
        <?php endif; ?>

        <div class="complaint-header">
                <div class="d-flex justify-content-between align-items-center">
                <h2><?php echo htmlspecialchars($complaint['title']); ?></h2>
                <div>
                    <span class="status-badge status-<?php echo $complaint['status']; ?>">
                        <?php echo ucfirst(htmlspecialchars($complaint['status'])); ?>
                    </span>
                    
                    <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'staff'])): ?>
                        <form method="POST" class="d-inline-block ms-2">
                            <select name="status" class="form-select form-select-sm d-inline-block w-auto">
                                <option value="pending" <?php echo $complaint['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="in_progress" <?php echo $complaint['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="resolved" <?php echo $complaint['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="closed" <?php echo $complaint['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update Status</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-muted mb-1">
                    <i class="fas fa-calendar-alt"></i> 
                    Submitted on: <?php echo date('F j, Y, g:i a', strtotime($complaint['created_at'])); ?>
                </p>
                <p class="text-muted mb-1">
                    <i class="fas fa-tag"></i>
                    Category: <?php echo ucfirst(htmlspecialchars($complaint['category'])); ?>
                        </p>
                <p class="text-muted mb-1">
                    <i class="fas fa-building"></i>
                    Department: <?php echo htmlspecialchars($department['name']); ?>
                </p>
                <p class="text-muted">
                    <i class="fas fa-flag"></i>
                    Priority: <?php echo ucfirst(htmlspecialchars($complaint['priority'])); ?>
                </p>
                        </div>
                    </div>

        <div class="complaint-details">
            <h4>Description</h4>
            <p><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></p>

            <?php if (!empty($complaint['image'])): ?>
                <h4>Attached Image</h4>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($complaint['image']); ?>" 
                     alt="Complaint Image" class="complaint-image" style="max-width: 200px; height: auto; border-radius: 50%;">
                        <?php endif; ?>
                </div>

        <div class="complaint-details">
            <h4>Updates & Notes</h4>
            <div class="timeline">
                            <?php if (empty($notes)): ?>
                    <p class="text-muted">No updates yet.</p>
                            <?php else: ?>
                                <?php foreach ($notes as $note): ?>
                        <div class="timeline-item">
                            <p class="mb-1"><?php echo nl2br(htmlspecialchars($note['note'])); ?></p>
                            <small class="text-muted">
                                Added by <?php echo htmlspecialchars($note['user_name']); ?> 
                                on <?php echo date('F j, Y, g:i a', strtotime($note['created_at'])); ?>
                            </small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

            <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'staff'])): ?>
                <form method="POST" class="mt-4">
                    <div class="mb-3">
                        <label for="note" class="form-label">Add Note</label>
                        <textarea class="form-control" id="note" name="note" rows="3" required></textarea>
                    </div>
                    <button type="submit" name="add_note" class="btn btn-primary">Add Note</button>
                                    </form>
                        <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 