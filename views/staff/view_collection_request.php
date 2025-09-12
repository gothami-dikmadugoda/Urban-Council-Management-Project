<?php
session_start();
require_once '../../controllers/CollectionController.php';
require_once '../../controllers/NotificationController.php';
require_once '../../controllers/AdminController.php';

// Check if user is logged in and is a garbage manager from health department
if (!isset($_SESSION['user_id']) || 
    !isset($_SESSION['department']) || 
    !isset($_SESSION['job_role']) || 
    $_SESSION['department'] !== 'health' || 
    $_SESSION['job_role'] !== 'garbage_manager') {
    header('Location: /urban2/login.php');
    exit();
}

$collectionController = new CollectionController();
$notificationController = new NotificationController();
$adminController = new AdminController();
$staffId = $_SESSION['user_id'];
$staffDetails = $adminController->getStaffDetails($staffId);
$staff = $staffDetails['data'];

// Get request ID from URL
$requestId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$requestId) {
    $_SESSION['error'] = 'Invalid request ID';
    header('Location: /urban2/views/staff/staff_dashboard.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $result = $collectionController->updateCollectionStatus($requestId, $_POST['status'], $_SESSION['user_id']);
        if ($result['success']) {
            // Create notification for the citizen
            $request = $collectionController->getCollectionRequestById($requestId);
            if ($request) {
                $notificationData = [
                    'user_id' => $request['user_id'],
                    'title' => 'Collection Request Update',
                    'message' => "Your collection request status has been updated to: " . $_POST['status'],
                    'type' => 'collection_update',
                    'reference_id' => $requestId
                ];
                if ($notificationController->createNotification($notificationData)) {
                    $_SESSION['success'] = 'Status updated successfully';
                } else {
                    $_SESSION['error'] = 'Failed to create notification';
                }
            } else {
                $_SESSION['error'] = 'Failed to get request details';
            }
        } else {
            $_SESSION['error'] = $result['message'];
        }
    } elseif (isset($_POST['send_reply'])) {
        // Handle reply submission
        $replyMessage = trim($_POST['reply_message']);
        if (!empty($replyMessage)) {
            $request = $collectionController->getCollectionRequestById($requestId);
            if ($request) {
                $notificationData = [
                    'user_id' => $request['user_id'],
                    'title' => 'Reply to Your Collection Request',
                    'message' => $replyMessage,
                    'type' => 'collection_reply',
                    'reference_id' => $requestId
                ];
                if ($notificationController->createNotification($notificationData)) {
                    $_SESSION['success'] = 'Reply sent successfully';
                } else {
                    $_SESSION['error'] = 'Failed to send reply';
                }
            } else {
                $_SESSION['error'] = 'Failed to get request details';
            }
        } else {
            $_SESSION['error'] = 'Reply message cannot be empty';
        }
    } elseif (isset($_POST['add_note'])) {
        $result = $collectionController->addCollectionNote($requestId, $_SESSION['user_id'], $_POST['note']);
        if ($result['success']) {
            $_SESSION['success'] = 'Note added successfully';
        } else {
            $_SESSION['error'] = $result['message'];
        }
    }
    header("Location: /urban2/views/staff/view_collection_request.php?id=" . $requestId);
    exit();
}

// Get collection request details
$request = $collectionController->getCollectionRequestById($requestId);

if (!$request) {
    $_SESSION['error'] = 'Collection request not found';
    header('Location: /urban2/views/staff/staff_dashboard.php');
    exit();
}

// Get collection notes
$notes = $collectionController->getCollectionNotes($requestId);

// Get notifications for this request
$notifications = $notificationController->getNotificationsByReference($requestId, 'collection_request');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Collection Request - Urban Council</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            color: var(--dark);
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
            padding: 2rem;
            transition: all 0.3s ease;
        }
        .page-header {
            background: var(--gradient-primary);
            color: white;
            padding: 2rem 0 1.5rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 2rem;
            background: white;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .card-header {
            background: var(--gradient-primary);
            color: white;
            border-bottom: none;
            padding: 1.2rem 1.5rem;
            border-radius: 15px 15px 0 0 !important;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-badge {
            font-size: 1rem;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .bg-warning { background-color: var(--highlight) !important; color: #000; }
        .bg-success { background-color: #1cc88a !important; }
        .bg-danger { background-color: #e74c3c !important; }
        .bg-info { background-color: #3498db !important; }
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        .timeline-item {
            padding: 20px;
            background: white;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary);
            position: relative;
            transition: transform 0.3s ease;
        }
        .timeline-item:hover {
            transform: translateX(10px);
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -12px;
            top: 50%;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--primary);
            transform: translateY(-50%);
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 2px solid #eee;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(201, 87, 146, 0.15);
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: var(--primary);
            border: none;
        }
        .btn-primary:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }
        .reply-form {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 15px;
            margin-top: 1rem;
        }
        .detail-label {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        .detail-value {
            color: var(--dark);
            margin-bottom: 1rem;
        }
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
        .notification-item {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary);
            transition: all 0.3s ease;
        }
        .notification-item:hover {
            transform: translateX(10px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .main-container {
            padding-bottom: 2rem;
        }
        .footer {
            width: 100%;
            min-height: 60px;
            background-color: #fff;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 2rem;
        }
        .notification-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        .card:last-child {
            margin-bottom: 3rem;
        }
        @media (max-width: 768px) {
            body {
                padding-bottom: 80px;
            }
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            .container {
                padding-left: 15px;
                padding-right: 15px;
            }
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
                    <a class="nav-link" href="/urban2/views/admin/garbage_schedule.php">
                        <i class='bx bxs-calendar'></i> Garbage Schedule
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/urban2/views/staff/collection_requests.php">
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
    <div class="main-content">
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-auto">
                        <a href="/urban2/views/admin/staff_dashboard.php" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                <div class="col">
                    <h2 class="mb-0">Collection Request Details</h2>
                </div>
            </div>
        </div>
    </div>
    <div class="container main-container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success fade-in" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger fade-in" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        <div class="card fade-in">
            <div class="card-header">
                <h4 class="mb-0">Request Information</h4>
                <span class="badge rounded-pill <?php
                    switch($request['status']) {
                        case 'pending': echo 'bg-warning'; break;
                        case 'approved': echo 'bg-success'; break;
                        case 'rejected': echo 'bg-danger'; break;
                        case 'completed': echo 'bg-info'; break;
                    }
                ?> status-badge">
                    <?php echo ucfirst($request['status']); ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-label">Citizen Name</div>
                        <div class="detail-value"><?php echo htmlspecialchars($request['user_name']); ?></div>

                        <div class="detail-label">Phone</div>
                        <div class="detail-value"><?php echo htmlspecialchars($request['user_phone']); ?></div>

                        <div class="detail-label">Area</div>
                        <div class="detail-value"><?php echo htmlspecialchars($request['area']); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Collection Date</div>
                        <div class="detail-value"><?php echo date('F j, Y', strtotime($request['collection_date'])); ?></div>

                        <div class="detail-label">Collection Time</div>
                        <div class="detail-value"><?php echo date('g:i A', strtotime($request['collection_time'])); ?></div>

                        <div class="detail-label">Waste Type</div>
                        <div class="detail-value"><?php echo ucfirst(htmlspecialchars($request['waste_type'])); ?></div>

                        <div class="detail-label">Waste Volume</div>
                        <div class="detail-value"><?php echo ucfirst(htmlspecialchars($request['waste_volume'])); ?></div>
                    </div>
                </div>

                <?php if ($request['special_instructions']): ?>
                    <div class="mt-3">
                        <div class="detail-label">Special Instructions</div>
                        <div class="detail-value"><?php echo nl2br(htmlspecialchars($request['special_instructions'])); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($request['status'] !== 'completed'): ?>
                <hr>
                <form action="" method="POST" class="mt-4">
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label for="status" class="form-label">Update Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="">Select Status</option>
                                <option value="pending" <?php echo $request['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $request['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $request['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                <option value="completed" <?php echo $request['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" name="update_status" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Update Status
                            </button>
                        </div>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-comment-dots me-2"></i>Add Collection Note</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" class="reply-form">
                    <div class="form-group">
                        <label for="note" class="form-label">Note Details</label>
                        <textarea class="form-control" id="note" name="note" rows="3" required 
                                placeholder="Enter your note here..."></textarea>
                    </div>
                    <button type="submit" name="add_note" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-2"></i>Add Note
                    </button>
                </form>
            </div>
        </div>

        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-reply me-2"></i>Send Reply to Citizen</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" class="reply-form">
                    <div class="form-group">
                        <label for="reply_message" class="form-label">Your Reply</label>
                        <textarea class="form-control" id="reply_message" name="reply_message" rows="3" required
                                placeholder="Type your message to the citizen..."></textarea>
                    </div>
                    <button type="submit" name="send_reply" class="btn btn-primary mt-3">
                        <i class="fas fa-paper-plane me-2"></i>Send Reply
                    </button>
                </form>
            </div>
        </div>

        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Communication History</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No communication history available</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item">
                                <h6 class="mb-1">
                                    <i class="fas fa-bell me-2"></i>
                                    <?php echo htmlspecialchars($notification['title']); ?>
                                </h6>
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo date('F j, Y g:i A', strtotime($notification['created_at'])); ?>
                                </p>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($notification['message'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </div>
    <footer class="footer">
        <div class="container">
            <div class="text-center text-muted">
                © <?php echo date('Y'); ?> Urban Council - Garbage Collection Management
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth scrolling to all links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add animation to cards on scroll
        const observerOptions = {
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.card').forEach(card => {
            observer.observe(card);
        });

        // Add scroll to top button functionality
        window.onscroll = function() {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                document.getElementById("scrollToTop").style.display = "block";
            } else {
                document.getElementById("scrollToTop").style.display = "none";
            }
        };

        // Smooth scroll to top
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>

    <!-- Scroll to top button -->
    <button onclick="scrollToTop()" id="scrollToTop" class="btn btn-primary rounded-circle position-fixed" 
            style="bottom: 20px; left: 20px; display: none; z-index: 1000;">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Single include of notification bell -->
    <div class="notification-container">
        <?php include_once __DIR__ . '/../notification_bell.php'; ?>
    </div>
</body>
</html> 