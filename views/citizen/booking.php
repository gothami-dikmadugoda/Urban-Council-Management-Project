<?php
session_start();
require_once '../../controllers/BookingController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to continue.";
    header('Location: /urban2/login.php');
    exit();
}

$bookingController = new BookingController();

// Get available areas
$areas = $bookingController->getAvailableAreas();
error_log("Areas in booking form: " . print_r($areas, true));

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    try {
        // Validate required fields
        $required_fields = ['area_id', 'booking_date', 'start_time', 'duration_hours'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields. Missing: " . $field);
            }
        }

        // Get receptionist ID
        $receptionist_id = $bookingController->getReceptionist();
        if (!$receptionist_id) {
            throw new Exception("No receptionist available at the moment");
        }

        // Validate date and time
        $booking_date = $_POST['booking_date'];
        $start_time = $_POST['start_time'];
        $start_datetime = $booking_date . ' ' . $start_time;
        
        if (strtotime($start_datetime) < time()) {
            throw new Exception("Booking date and time must be in the future");
        }

        // Validate duration
        $duration_hours = intval($_POST['duration_hours']);
        if ($duration_hours <= 0 || $duration_hours > 24) {
            throw new Exception("Duration must be between 1 and 24 hours");
        }

        // Calculate total amount
        $total_amount = $bookingController->calculateTotalAmount(
            $_POST['area_id'],
            $duration_hours
        );

        if ($total_amount <= 0) {
            throw new Exception("Invalid total amount calculated");
        }

        $data = [
            'user_id' => $_SESSION['user_id'],
            'area_id' => $_POST['area_id'],
            'start_datetime' => $start_datetime,
            'duration_hours' => $duration_hours,
            'description' => $_POST['description'] ?? '',
            'assigned_to' => $receptionist_id,
            'total_amount' => $total_amount
        ];

        // Debug log
        error_log("Booking data: " . print_r($data, true));

        $result = $bookingController->createBooking($data);
        
        if ($result['status'] === 'success') {
            $_SESSION['success'] = "Booking request submitted successfully! Booking ID: " . $result['booking_id'];
            header('Location: /urban2/views/citizen/booking.php');
            exit();
        } else {
            throw new Exception($result['message'] ?? "Failed to create booking");
        }
    } catch (Exception $e) {
        error_log("Booking error: " . $e->getMessage());
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Get user's upcoming bookings
$upcomingBookings = $bookingController->getUpcomingBookings($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Public Place - Urban Council</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --main-bg: #0d112b;
            --sidebar-bg: #181e3a;
            --card-bg: #16213e;
            --card-radius: 18px;
            --card-shadow: 0 6px 24px rgba(34,197,94,0.08), 0 1.5px 4px rgba(34,197,94,0.08);
            --input-bg: #22325c;
            --input-border: #22c55e;
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
            color: var(--text);
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
        .dashboard-card, .booking-card {
            background: var(--card-bg);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            padding: 2rem 1.5rem 1.5rem 1.5rem;
            margin-bottom: 2rem;
            border: none;
            color: var(--text);
        }
        .dashboard-card h4, .booking-card h4 {
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--accent);
            letter-spacing: 0.5px;
        }
        .booking-card {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem 1.2rem 1.2rem 1.2rem;
            margin-bottom: 1.5rem;
            border-left: 6px solid var(--card-accent);
            box-shadow: 0 4px 18px rgba(59,59,152,0.10), 0 1.5px 4px rgba(59,59,152,0.06);
            color: var(--primary);
            transition: box-shadow 0.2s, transform 0.2s;
            position: relative;
        }
        .booking-card:hover {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.2rem 1rem;
            margin-bottom: 1.2rem;
            border: 1px solid #e0e7ef;
            box-shadow: 0 2px 8px rgba(59,59,152,0.04);
            color: var(--primary);
        }
        .booking-status {
            padding: 4px 12px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.95em;
        }
        .status-pending { background: var(--status-pending-bg); color: var(--status-pending-text); }
        .status-approved { background: var(--status-approved-bg); color: var(--status-approved-text); }
        .status-rejected { background: var(--status-rejected-bg); color: var(--status-rejected-text); }
        .form-label {
            font-weight: 700;
            color: var(--accent);
            font-size: 1.08rem;
            letter-spacing: 0.1px;
        }
        .form-control, .form-select, textarea {
            border-radius: 12px;
            border: 1.5px solid var(--input-border);
            padding: 1rem 1.3rem;
            font-size: 1.08rem;
            background: var(--input-bg);
            color: var(--text);
            transition: border-color 0.18s, box-shadow 0.18s;
        }
        .form-control:focus, .form-select:focus, textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(34,197,94,0.18);
            background: #16213e;
            color: #fff;
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
        .alert-info {
            background: #eaf6fb;
            color: var(--accent-purple);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
        }
        h2.mb-4 {
            color: var(--heading);
            font-weight: 700;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: static;
                width: 100%;
                height: auto;
            }
            .main-content {
                margin-left: 0;
                padding: 1.2rem 0.5rem 1.2rem 0.5rem;
            }
            .dashboard-card {
                padding: 1.2rem 0.7rem 1rem 0.7rem;
            }
        }
        .dashboard-card .booking-card {
            background: #22325c;
            border-radius: 16px;
            padding: 1.5rem 1.2rem 1.2rem 1.2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 18px rgba(34,197,94,0.10), 0 1.5px 4px rgba(34,197,94,0.06);
            color: #fff;
            border-left: 4px solid #22c55e;
        }
        .dashboard-card .booking-card h5 {
            color: #22c55e;
            font-weight: 700;
        }
        .dashboard-card .booking-card strong {
            color: #e5e7eb;
        }
        .dashboard-card .booking-status {
            background: #16213e;
            color: #22c55e;
            padding: 4px 12px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.95em;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="profile-section">
                <img src="<?php echo isset($citizenInfo['profile_image']) ? $citizenInfo['profile_image'] : (isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : '/urban2/assets/images/default-avatar.png'); ?>" 
                     alt="Profile Image">
                <h5 class="text-white mb-1"><?php echo isset($citizenInfo['name']) ? htmlspecialchars($citizenInfo['name']) : (isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Citizen'); ?></h5>
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
                    <a class="nav-link" href="/urban2/views/citizen/recent-activities.php">
                        <i class="fas fa-history"></i> Recent Activities
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/citizen/notifications.php">
                        <i class="fas fa-bell"></i> Notifications
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/citizen/requests.php">
                        <i class="fas fa-cog"></i> Active Requests
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="/urban2/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
        <!-- Main Content -->
        <div class="main-content">
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

                <h2 class="mb-4">Book Public Place</h2>
                
                <div class="row">
                    <!-- Booking Form -->
                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <h4><i class="fas fa-calendar-plus"></i> New Booking Request</h4>
                            <form method="POST" action="" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="area_id" class="form-label">Select Public Place *</label>
                                    <select class="form-select" id="area_id" name="area_id" required>
                                        <option value="">Select a place</option>
                                        <?php foreach ($areas as $area): ?>
                                            <option value="<?php echo $area['id']; ?>" 
                                                    data-hourly-rate="<?php echo $area['hourly_rate']; ?>">
                                                <?php echo htmlspecialchars($area['name']); ?> 
                                                (Capacity: <?php echo $area['capacity']; ?> people) - 
                                                Rs. <?php echo number_format($area['hourly_rate'], 2); ?>/hour
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a public place.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="booking_date" class="form-label">Booking Date *</label>
                                    <input type="date" class="form-control" id="booking_date" name="booking_date" required 
                                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                    <div class="invalid-feedback">Please select a booking date.</div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="start_time" class="form-label">Start Time *</label>
                                        <input type="time" class="form-control" id="start_time" name="start_time" required>
                                        <div class="invalid-feedback">Please select start time.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="duration_hours" class="form-label">Duration (Hours) *</label>
                                        <input type="number" class="form-control" id="duration_hours" name="duration_hours" 
                                               min="1" max="24" required>
                                        <div class="invalid-feedback">Please enter duration in hours (1-24).</div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" 
                                              placeholder="Describe the purpose of your booking"></textarea>
                                </div>

                                <div class="mb-3">
                                    <div class="alert alert-info">
                                        <strong>Estimated Total Amount: </strong>
                                        <span id="total_amount">Rs. 0.00</span>
                                    </div>
                                </div>
                                
                                <button type="submit" name="submit_booking" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Submit Booking Request
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Upcoming Bookings -->
                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <h4><i class="fas fa-calendar-alt"></i> Your Upcoming Bookings</h4>
                            <?php if (empty($upcomingBookings)): ?>
                                <p class="text-muted">No upcoming bookings found.</p>
                            <?php else: ?>
                                <?php foreach ($upcomingBookings as $booking): ?>
                                    <div class="booking-card">
                                        <h5><?php echo htmlspecialchars($booking['area_name']); ?></h5>
                                        <p class="mb-1">
                                            <strong>Date & Time:</strong> 
                                            <?php echo date('F j, Y h:i A', strtotime($booking['start_datetime'])); ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Duration:</strong> <?php echo $booking['duration_hours']; ?> hours
                                        </p>
                                        <p class="mb-1">
                                            <strong>Total Amount:</strong> Rs. <?php echo number_format($booking['total_amount'], 2); ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Status:</strong> 
                                            <span class="booking-status status-<?php echo strtolower($booking['status']); ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </p>
                                        <?php if (!empty($booking['description'])): ?>
                                            <p class="mb-1">
                                                <strong>Description:</strong> <?php echo htmlspecialchars($booking['description']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Calculate total amount
        function calculateTotal() {
            const areaSelect = document.getElementById('area_id');
            const durationInput = document.getElementById('duration_hours');
            const totalAmountSpan = document.getElementById('total_amount');
            
            if (areaSelect.value && durationInput.value) {
                const hourlyRate = parseFloat(areaSelect.options[areaSelect.selectedIndex].dataset.hourlyRate);
                const duration = parseInt(durationInput.value);
                const total = hourlyRate * duration;
                totalAmountSpan.textContent = 'Rs. ' + total.toFixed(2);
            } else {
                totalAmountSpan.textContent = 'Rs. 0.00';
            }
        }

        document.getElementById('area_id').addEventListener('change', calculateTotal);
        document.getElementById('duration_hours').addEventListener('input', calculateTotal);
    </script>
</body>
</html> 