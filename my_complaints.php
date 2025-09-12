<?php
session_start();
require_once __DIR__ . '/controllers/ComplaintController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /urban2/login.php');
    exit;
}

$complaintController = new ComplaintController();

// Get user's complaints
$complaints = $complaintController->getComplaintsByUserId($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints - Urban Council Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            background: #2c3e50;
            padding: 1rem;
        }
        .navbar-brand {
            color: white !important;
            font-weight: bold;
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            color: white !important;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
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
            padding: 0.5rem 1.5rem;
        }
        .btn-primary:hover {
            background: #2980b9;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .status-pending {
            background: #ffeeba;
            color: #856404;
        }
        .status-in-progress {
            background: #cce5ff;
            color: #004085;
        }
        .status-resolved {
            background: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="/urban2">Urban Council</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/urban2">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/urban2/my_complaints.php">My Complaints</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/urban2/citizen/complaints.php">Submit Complaint</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/urban2/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">My Complaints</h5>
                <a href="/urban2/citizen/complaints.php" class="btn btn-primary">
                    <i class='bx bx-plus'></i> Submit New Complaint
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($complaints)): ?>
                    <div class="text-center py-5">
                        <i class='bx bx-message-square-detail' style="font-size: 4rem; color: #ddd;"></i>
                        <p class="mt-3">You haven't submitted any complaints yet.</p>
                        <a href="/urban2/citizen/complaints.php" class="btn btn-primary">
                            Submit Your First Complaint
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                                        <td><?php echo htmlspecialchars($complaint['title']); ?></td>
                                        <td><?php echo ucfirst(htmlspecialchars($complaint['category'])); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $complaint['status']; ?>">
                                                <?php echo ucfirst(htmlspecialchars($complaint['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></td>
                                        <td>
                                            <a href="/urban2/view_complaint.php?id=<?php echo $complaint['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class='bx bx-show'></i> View
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 