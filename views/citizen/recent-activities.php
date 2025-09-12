<?php
session_start();
require_once '../../controllers/CitizenController.php';
require_once '../../controllers/ActivityController.php';
require_once '../../controllers/ComplaintController.php';
require_once '../../controllers/CollectionController.php';
require_once '../../controllers/BookingController.php';
require_once '../../controllers/PaymentController.php';

// Check if user is logged in and is a citizen
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'citizen') {
    header('Location: /urban2/login.php');
    exit();
}

$citizenController = new CitizenController();
$complaintController = new ComplaintController();
$collectionController = new CollectionController();
$bookingController = new BookingController();
$paymentController = new PaymentController();

// Get citizen information
$citizenInfo = $citizenController->getCitizenInfo($_SESSION['user_id']);

// Get all recent activities (5 items each)
$recentComplaints = array_slice($complaintController->getComplaintsByUserId($_SESSION['user_id']), 0, 5);
$upcomingCollections = isset($citizenInfo['area']) ? array_slice($collectionController->getUserCollectionRequests($_SESSION['user_id']), 0, 5) : [];
$upcomingBookings = array_slice($bookingController->getUpcomingBookings($_SESSION['user_id']), 0, 5);
$recentPayments = array_slice($paymentController->getUserPayments($_SESSION['user_id']), 0, 5);

// Combine all activities into a single array
$allActivities = [];

// Add complaints
if (!empty($recentComplaints)) {
    foreach ($recentComplaints as $complaint) {
        $status = isset($complaint['status']) ? $complaint['status'] : 'N/A';
        $allActivities[] = [
            'type' => 'complaint',
            'title' => 'Complaint: ' . (isset($complaint['title']) ? $complaint['title'] : 'N/A'),
            'description' => 'Status: ' . $status,
            'date' => isset($complaint['created_at']) ? $complaint['created_at'] : '',
            'icon' => 'fa-exclamation-circle',
            'color' => '#e74c3c',
            'link' => '/urban2/views/citizen/complaints.php?id=' . (isset($complaint['id']) ? $complaint['id'] : '')
        ];
    }
}

// Add collections
if (!empty($upcomingCollections)) {
    foreach ($upcomingCollections as $collection) {
        $status = isset($collection['status']) ? $collection['status'] : 'N/A';
        $allActivities[] = [
            'type' => 'collection',
            'title' => 'Collection Request',
            'description' => (isset($collection['waste_type']) ? $collection['waste_type'] : 'N/A') . ' collection - Status: ' . $status,
            'date' => isset($collection['created_at']) ? $collection['created_at'] : '',
            'icon' => 'fa-truck',
            'color' => '#2ecc71',
            'link' => '/urban2/views/citizen/requests.php'
        ];
    }
}

// Add bookings
if (!empty($upcomingBookings)) {
    foreach ($upcomingBookings as $booking) {
        $serviceType = isset($booking['service_type']) ? $booking['service_type'] : 'N/A';
        $bookingDate = isset($booking['booking_date']) ? $booking['booking_date'] : null;
        $bookingTime = isset($booking['booking_time']) ? $booking['booking_time'] : null;
        $createdAt = isset($booking['created_at']) ? $booking['created_at'] : null;
        $title = 'Service Booking: ' . $serviceType;
        $description = 'Scheduled for ' .
            ($bookingDate ? date('F j, Y', strtotime($bookingDate)) : 'Unknown Date') .
            ' at ' . ($bookingTime ? date('g:i A', strtotime($bookingTime)) : 'Unknown Time');
        $allActivities[] = [
            'type' => 'booking',
            'title' => $title,
            'description' => $description,
            'date' => $createdAt,
            'icon' => 'fa-calendar-check',
            'color' => '#f1c40f',
            'link' => '/urban2/views/citizen/booking.php'
        ];
    }
}

// Add payments
if (!empty($recentPayments)) {
    foreach ($recentPayments as $payment) {
        $status = isset($payment['status']) ? $payment['status'] : 'N/A';
        $allActivities[] = [
            'type' => 'payment',
            'title' => 'Payment: ' . (isset($payment['payment_type']) ? $payment['payment_type'] : 'N/A'),
            'description' => 'Amount: Rs. ' . number_format(isset($payment['amount']) ? $payment['amount'] : 0, 2) .
                            ' - Status: ' . $status,
            'date' => isset($payment['created_at']) ? $payment['created_at'] : '',
            'icon' => 'fa-money-bill-wave',
            'color' => '#9b59b6',
            'link' => '/urban2/views/citizen/payments.php'
        ];
    }
}

// Sort all activities by date (newest first)
usort($allActivities, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recent Activities - Urban Council</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --main-bg: #0d112b;
            --card-bg: #fff;
            --card-radius: 16px;
            --card-shadow: 0 4px 18px rgba(34,48,86,0.10), 0 1.5px 4px rgba(34,48,86,0.06);
            --primary-dark-blue: #223056;
            --accent-purple: #5F3A8A;
            --accent-green: #217a4a;
            --complaint-bg: #ede7f6;
            --complaint-text: #5F3A8A;
            --collection-bg: #e6f4ea;
            --collection-text: #217a4a;
            --booking-bg: #e3e8fa;
            --booking-text: #223056;
            --payment-bg: #f3e8ff;
            --payment-text: #5F3A8A;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2.5rem 2rem 2.5rem 2rem;
            background: transparent;
            min-height: 100vh;
        }
        .timeline {
            position: relative;
            padding: 1.5rem 0.5rem 1.5rem 1.5rem;
        }
        .activity-item {
            position: relative;
            padding-left: 2.2rem;
            margin-bottom: 2.2rem;
        }
        .activity-card {
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            background: var(--card-bg);
            padding: 1.5rem 1.2rem 1.2rem 1.2rem;
            margin-bottom: 1.2rem;
            border: none;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .activity-card:hover {
            box-shadow: 0 8px 32px rgba(34,48,86,0.18), 0 2px 8px rgba(34,48,86,0.10);
            transform: translateY(-2px) scale(1.01);
        }
        .activity-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: #fff;
            font-size: 1.3rem;
            box-shadow: 0 2px 8px rgba(34,48,86,0.10);
        }
        .activity-icon.complaint { background: var(--accent-purple); }
        .activity-icon.collection { background: var(--accent-green); }
        .activity-icon.booking { background: var(--primary-dark-blue); }
        .activity-icon.payment { background: var(--accent-purple); }
        .card-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--primary-dark-blue);
            margin-bottom: 0.3rem;
        }
        .text-muted {
            color: #6c757d !important;
        }
        .date-badge {
            background-color: #e9ecef;
            color: var(--primary-dark-blue);
            padding: 0.35rem 0.7rem;
            border-radius: 0.35rem;
            font-size: 0.85rem;
            margin-right: 0.5rem;
        }
        .activity-type-badge {
            padding: 0.35rem 0.7rem;
            border-radius: 0.35rem;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: capitalize;
            box-shadow: 0 1px 4px rgba(34,48,86,0.08);
        }
        .complaint-badge { background-color: var(--complaint-bg); color: var(--complaint-text); }
        .collection-badge { background-color: var(--collection-bg); color: var(--collection-text); }
        .booking-badge { background-color: var(--booking-bg); color: var(--booking-text); }
        .payment-badge { background-color: var(--payment-bg); color: var(--payment-text); }
        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 2.2rem 0 1.2rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
            color: #22c55e;
            letter-spacing: 0.5px;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: #181e3a;
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
                padding: 1.2rem 0.5rem 1.2rem 0.5rem;
            }
            .timeline {
                padding: 0.7rem 0.2rem 0.7rem 0.7rem;
            }
            .activity-card {
                padding: 1rem 0.7rem 0.8rem 0.7rem;
            }
        }
        body {
            background: var(--main-bg);
            min-height: 100vh;
        }
        .container.mt-4 {
            margin-left: var(--sidebar-width);
            max-width: 900px;
        }
        @media (max-width: 900px) {
            .container.mt-4 {
                margin-left: 0;
                max-width: 100%;
            }
        }
        .container.mt-4 h2 {
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-history text-primary"></i> 
                Recent Activities
            </h2>
        </div>

        <!-- Sidebar Start -->
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
                    <a class="nav-link active" href="/urban2/views/citizen/recent-activities.php">
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
        <!-- Sidebar End -->

        <div class="main-content">
        <?php if (empty($allActivities)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No recent activities found.
            </div>
        <?php else: ?>
            <!-- Recent Complaints Section -->
            <?php if (!empty($recentComplaints)): ?>
                <h3 class="section-title">
                    <i class="fas fa-exclamation-circle text-danger"></i> Recent Complaints
                </h3>
                <div class="timeline">
                    <?php foreach ($recentComplaints as $complaint): ?>
                        <div class="activity-item">
                            <a href="/urban2/views/citizen/complaints.php?id=<?php echo $complaint['id']; ?>" class="text-decoration-none">
                                <div class="card activity-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="activity-icon complaint">
                                                <i class="fas fa-exclamation-circle"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-0 text-dark"><?php echo htmlspecialchars($complaint['title']); ?></h5>
                                                <p class="text-muted mb-0">
                                                    Status: <?php echo htmlspecialchars($complaint['status']); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="date-badge">
                                                <i class="far fa-clock"></i> 
                                                <?php echo date('F j, Y g:i A', strtotime($complaint['created_at'])); ?>
                                            </span>
                                            <span class="activity-type-badge complaint-badge">
                                                Complaint
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Upcoming Collections Section -->
            <?php if (!empty($upcomingCollections)): ?>
                <h3 class="section-title">
                    <i class="fas fa-truck text-success"></i> Upcoming Collections
                </h3>
                <div class="timeline">
                    <?php foreach ($upcomingCollections as $collection): ?>
                        <div class="activity-item">
                            <a href="/urban2/views/citizen/requests.php" class="text-decoration-none">
                                <div class="card activity-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="activity-icon collection">
                                                <i class="fas fa-truck"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-0 text-dark">Collection Request</h5>
                                                <p class="text-muted mb-0">
                                                    <?php echo htmlspecialchars($collection['waste_type']); ?> - 
                                                    Status: <?php echo htmlspecialchars($collection['status']); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="date-badge">
                                                <i class="far fa-clock"></i> 
                                                <?php echo date('F j, Y g:i A', strtotime($collection['created_at'])); ?>
                                            </span>
                                            <span class="activity-type-badge collection-badge">
                                                Collection
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Upcoming Bookings Section -->
            <?php if (!empty($upcomingBookings)): ?>
                <h3 class="section-title">
                    <i class="fas fa-calendar-check text-warning"></i> Upcoming Bookings
                </h3>
                <div class="timeline">
                    <?php foreach ($upcomingBookings as $booking): ?>
                        <div class="activity-item">
                            <a href="/urban2/views/citizen/booking.php" class="text-decoration-none">
                                <div class="card activity-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="activity-icon booking">
                                                <i class="fas fa-calendar-check"></i>
                                            </div>
                                            <div>
                                                    <h5 class="card-title mb-0 text-dark"><?php echo htmlspecialchars(isset($booking['service_type']) ? $booking['service_type'] : 'N/A'); ?></h5>
                                                <p class="text-muted mb-0">
                                                        Scheduled for <?php 
                                                            $date = isset($booking['booking_date']) ? $booking['booking_date'] : null;
                                                            $time = isset($booking['booking_time']) ? $booking['booking_time'] : null;
                                                            if ($date && $time) {
                                                                echo date('F j, Y g:i A', strtotime($date . ' ' . $time));
                                                            } elseif ($date) {
                                                                echo date('F j, Y', strtotime($date));
                                                            } elseif ($time) {
                                                                echo date('g:i A', strtotime($time));
                                                            } else {
                                                                echo 'Unknown';
                                                            }
                                                        ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="date-badge">
                                                <i class="far fa-clock"></i> 
                                                    <?php echo isset($booking['created_at']) ? date('F j, Y g:i A', strtotime($booking['created_at'])) : 'Unknown'; ?>
                                            </span>
                                            <span class="activity-type-badge booking-badge">
                                                Booking
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Recent Payments Section -->
            <?php if (!empty($recentPayments)): ?>
                <h3 class="section-title">
                    <i class="fas fa-money-bill-wave text-primary"></i> Recent Payments
                </h3>
                <div class="timeline">
                    <?php foreach ($recentPayments as $payment): ?>
                        <div class="activity-item">
                            <a href="/urban2/views/citizen/payments.php" class="text-decoration-none">
                                <div class="card activity-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="activity-icon payment">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-0 text-dark"><?php echo htmlspecialchars($payment['payment_type']); ?></h5>
                                                <p class="text-muted mb-0">
                                                    Amount: Rs. <?php echo number_format($payment['amount'], 2); ?> - 
                                                        Status: <?php echo htmlspecialchars(isset($payment['status']) ? $payment['status'] : 'N/A'); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="date-badge">
                                                <i class="far fa-clock"></i> 
                                                <?php echo date('F j, Y g:i A', strtotime($payment['created_at'])); ?>
                                            </span>
                                            <span class="activity-type-badge payment-badge">
                                                Payment
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 