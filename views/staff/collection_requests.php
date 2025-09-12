<?php
session_start();
require_once '../../controllers/CollectionController.php';
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
$collectionRequests = $collectionController->getCollectionRequests();

$adminController = new AdminController();
$staffId = $_SESSION['user_id'];
$staffDetails = $adminController->getStaffDetails($staffId);
$staff = $staffDetails['data'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection Requests - Urban Council Management System</title>
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
            padding: 2.5rem 2.5rem 2rem 2.5rem;
            background: #f8f9fa;
            min-height: 100vh;
            transition: all 0.3s ease;
        }
        .card {
            border: none;
            border-radius: 18px;
            box-shadow: var(--box-shadow);
            margin-bottom: 2.5rem;
            background: #fff;
        }
        .card-header {
            background: var(--gradient-primary);
            color: #fff;
            border-bottom: none;
            padding: 1.5rem 2rem;
            border-radius: 18px 18px 0 0 !important;
        }
        .card-body {
            padding: 2rem 2.2rem 2.2rem 2.2rem;
        }
        .table {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
        }
        .table thead th {
            background: var(--gradient-primary);
            color: #fff;
            border: none;
            font-weight: 600;
            padding: 1rem 0.75rem;
        }
        .table tbody tr:nth-child(even) {
            background: #f3f3fa;
        }
        .table tbody tr:hover {
            background: #f8e6f2;
            transition: background 0.2s;
        }
        .table td, .table th {
            vertical-align: middle;
            padding: 0.9rem 0.75rem;
        }
        .badge {
            padding: 0.5rem 1.1rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 1rem;
        }
        .btn-primary {
            background: var(--primary);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            .card-body {
                padding: 1.2rem 1rem 1.5rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
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
        <div class="main-content w-100">
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <a href="/urban2/views/admin/staff_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Collection Requests</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Citizen Name</th>
                                <th>Area</th>
                                <th>Collection Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                                    <?php foreach (
                                        $collectionRequests as $request): ?>
                            <tr>
                                <td><?php echo $request['id']; ?></td>
                                <td><?php echo htmlspecialchars($request['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['area']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($request['collection_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $request['status'] === 'pending' ? 'warning' : 
                                            ($request['status'] === 'approved' ? 'success' : 
                                            ($request['status'] === 'rejected' ? 'danger' : 'info')); 
                                    ?>">
                                        <?php echo ucfirst($request['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/urban2/views/staff/view_collection_request.php?id=<?php echo $request['id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
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
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 