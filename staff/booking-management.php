<?php
session_start();
require_once '../controllers/BookingController.php';

// Check if user is logged in and is a receptionist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff' || $_SESSION['job_role'] !== 'receptionist') {
    $_SESSION['error'] = "Access denied. Please login as a receptionist.";
    header('Location: /urban2/login.php');
    exit();
}

$bookingController = new BookingController();

// Handle booking status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $booking_id = $_POST['booking_id'];
        $status = $_POST['status'];
        $message = $_POST['message'] ?? '';

        $result = $bookingController->updateBookingStatus($booking_id, $status, $message);
        
        if ($result['status'] === 'success') {
            $_SESSION['success'] = "Booking status updated successfully!";
        } else {
            $_SESSION['error'] = $result['message'];
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "An error occurred while updating the booking status.";
    }
    header('Location: /urban2/staff/booking-management.php');
    exit();
}

// Get pending bookings
$pendingBookings = $bookingController->getPendingBookings();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management - Urban Council</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .booking-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .booking-status {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/urban2/staff/dashboard.php">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/urban2/staff/booking-management.php">
                                <i class="fas fa-calendar-check"></i> Booking Management
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link text-danger" href="/urban2/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="container-fluid py-4">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php 
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <h2 class="mb-4">Booking Management</h2>
                    
                    <div class="row">
                        <!-- Pending Bookings -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="mb-0"><i class="fas fa-clock"></i> Pending Bookings</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($pendingBookings)): ?>
                                        <p class="text-muted">No pending bookings found.</p>
                                    <?php else: ?>
                                        <?php foreach ($pendingBookings as $booking): ?>
                                            <div class="booking-card">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <h5><?php echo htmlspecialchars($booking['facility_name']); ?></h5>
                                                        <p class="mb-1">
                                                            <strong>Date:</strong> <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?>
                                                        </p>
                                                        <p class="mb-1">
                                                            <strong>Time:</strong> <?php echo date('h:i A', strtotime($booking['start_time'])); ?> - 
                                                            <?php echo date('h:i A', strtotime($booking['end_time'])); ?>
                                                        </p>
                                                        <p class="mb-1">
                                                            <strong>Purpose:</strong> <?php echo htmlspecialchars($booking['purpose']); ?>
                                                        </p>
                                                        <p class="mb-1">
                                                            <strong>Status:</strong> 
                                                            <span class="booking-status status-<?php echo strtolower($booking['status']); ?>">
                                                                <?php echo ucfirst($booking['status']); ?>
                                                            </span>
                                                        </p>
                                                        <?php if (!empty($booking['notes'])): ?>
                                                            <p class="mb-1">
                                                                <strong>Notes:</strong> <?php echo htmlspecialchars($booking['notes']); ?>
                                                            </p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <form method="POST" action="">
                                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                            <input type="hidden" name="action" value="update_status">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Update Status</label>
                                                                <select class="form-select" name="status" required>
                                                                    <option value="approved">Approve</option>
                                                                    <option value="rejected">Reject</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Message to User</label>
                                                                <textarea class="form-control" name="message" rows="3" 
                                                                          placeholder="Optional message to the user"></textarea>
                                                            </div>
                                                            
                                                            <button type="submit" class="btn btn-primary w-100">
                                                                <i class="fas fa-save"></i> Update Status
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 