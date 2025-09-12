<?php
session_start();
require_once __DIR__ . '/../../controllers/AdminController.php';

// Validate staff access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    header('Location: /urban2/login.php');
    exit;
}

$adminController = new AdminController();
$staffId = $_SESSION['user_id'];

// Get staff details
$staffDetails = $adminController->getStaffDetails($staffId);
$staff = $staffDetails['data'];

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
        $fileName = uniqid() . '_' . basename($file['name']);
        $targetPath = __DIR__ . '/../../assets/images/profiles/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $profilePicture = '/urban2/assets/images/profiles/' . $fileName;
            $result = $adminController->updateProfilePicture($staffId, $profilePicture);
            
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
                // Refresh staff details
                $staffDetails = $adminController->getStaffDetails($staffId);
                $staff = $staffDetails['data'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
        } else {
            $_SESSION['error_message'] = "පින්තූරය උඩුගත කිරීමේදී දෝෂයක් ඇති විය / Error uploading image";
        }
    } else {
        $_SESSION['error_message'] = "අවලංගු ගොනු වර්ගයක් හෝ ගොනු ප්‍රමාණය වැඩි ය / Invalid file type or file too large";
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $data = [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address']
    ];

    $result = $adminController->updateProfile($data);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        // Refresh staff details
        $staffDetails = $adminController->getStaffDetails($staffId);
        $staff = $staffDetails['data'];
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Urban Council Management System</title>
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
            --sidebar-width: 280px;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: var(--dark);
            line-height: 1.6;
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
        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid var(--accent);
            padding: 3px;
            margin-bottom: 1rem;
            object-fit: cover;
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
        .card {
            background: white;
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        .card-header {
            background: var(--gradient-primary);
            color: white;
            border-bottom: none;
            padding: 1.2rem;
            font-weight: 500;
        }
        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            color: white;
            transition: var(--transition);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(61, 54, 92, 0.3);
        }

        /* Main Content Layout */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            background: linear-gradient(135deg, #f8f9fe 0%, #f1f4f9 100%);
        }

        /* Profile Container */
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Profile Header */
        .profile-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 15px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            color: white;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            border-radius: 50%;
            transform: translate(50%, -50%);
            pointer-events: none;
        }

        .profile-header h1 {
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: white;
            position: relative;
        }

        .profile-header p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
            position: relative;
        }

        .profile-header .header-icon {
            position: absolute;
            right: 2.5rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3rem;
            opacity: 0.2;
            color: white;
        }

        /* Profile Grid Layout */
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Profile Card Improvements */
        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            height: 100%;
        }

        .profile-card .card-header {
            padding: 1.25rem 1.5rem;
            margin: -1.5rem -1.5rem 1.5rem;
            border-radius: 15px 15px 0 0;
        }

        /* Form Group Spacing */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(201, 87, 146, 0.25);
        }

        /* Profile Picture Section */
        .profile-picture-section {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .profile-picture-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 1.5rem;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--accent);
            padding: 3px;
        }

        .profile-picture-upload {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--accent);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .profile-picture-upload:hover {
            transform: scale(1.1);
        }

        /* Button Improvements */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Alert Messages */
        .alert {
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .profile-container {
                padding: 1rem;
            }

            .profile-grid {
                grid-template-columns: 1fr;
            }

            .profile-picture-section {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="profile-section">
            <img src="<?php 
                $profilePicture = isset($staff['profile_picture']) && !empty($staff['profile_picture']) 
                    ? '/urban2/uploads/profile_pictures/' . $staff['profile_picture'] 
                    : '/urban2/assets/images/default-avatar.png';
                echo $profilePicture;
            ?>" 
                 alt="Profile Image" class="profile-picture">
            <h5 class="text-white mb-1"><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></h5>
            <p class="text-muted mb-0"><?php echo ucfirst($staff['department']); ?> Department</p>
        </div>

        <div class="nav-container">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/">
                        <i class='bx bxs-home'></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/admin/staff_dashboard.php">
                        <i class='bx bxs-dashboard'></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/urban2/views/admin/staff_profile.php">
                        <i class='bx bxs-user'></i> Profile
                    </a>
                </li>
                <?php if ($staff['department'] === 'health' && $staff['job_role'] === 'garbage_manager'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/admin/garbage_schedule.php">
                        <i class='bx bxs-calendar'></i> Garbage Schedule
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/staff/collection_requests.php">
                        <i class='bx bxs-truck'></i> Collection Requests
                    </a>
                </li>
                <?php endif; ?>
                <?php if (strtolower($staff['department']) === 'it' && strtolower($staff['job_role']) === 'it_staff'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/admin/analytics.php">
                        <i class='bx bxs-report'></i> Analytics
                    </a>
                </li>
                <?php endif; ?>
                <?php if ($staff['department'] === 'engineering' && $staff['job_role'] === 'engineer'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/admin/assigned_complaints.php">
                        <i class='bx bxs-task'></i> Assigned Complaints
                    </a>
                </li>
                <?php endif; ?>
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

    <!-- Main Content -->
    <div class="main-content">
        <div class="profile-container">
            <!-- Profile Header -->
            <div class="profile-header">
                <i class='bx bxs-user-circle header-icon'></i>
                <h1>My Profile</h1>
                <p>Manage your account information and settings</p>
            </div>

            <!-- Profile Picture Section -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>
            <div class="profile-picture-section">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="profile-picture-container">
                        <img src="<?php echo $staff['profile_picture'] ?? '/urban2/assets/images/default-profile.png'; ?>" 
                             alt="Profile Picture" class="profile-picture">
                        <label for="profile_picture" class="profile-picture-upload">
                            <i class="bx bx-camera"></i>
                        </label>
                        <input type="file" id="profile_picture" name="profile_picture" class="d-none" 
                               accept="image/jpeg,image/png,image/gif" onchange="this.form.submit()">
                    </div>
                </form>
                <h4 class="mb-1"><?php echo $staff['first_name'] . ' ' . $staff['last_name']; ?></h4>
                <p class="text-muted mb-0"><?php echo ucfirst($staff['department']); ?></p>
            </div>

            <!-- Profile Information -->
            <div class="profile-grid">
                <!-- Personal Information -->
                <div class="profile-card">
                    <div class="card-header">
                        <h5 class="mb-0">Personal Information</h5>
                    </div>
                    <form method="POST" action="" class="mt-4">
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" 
                                   value="<?php echo htmlspecialchars($staff['first_name']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" 
                                   value="<?php echo htmlspecialchars($staff['last_name']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?php echo htmlspecialchars($staff['email']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" 
                                   value="<?php echo htmlspecialchars($staff['phone']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($staff['address']); ?></textarea>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            Update Profile
                        </button>
                    </form>
                </div>

                <!-- Account Information -->
                <div class="profile-card">
                    <div class="card-header">
                        <h5 class="mb-0">Account Information</h5>
                    </div>
                    <div class="mt-4">
                        <div class="form-group">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" value="<?php echo ucfirst($staff['department']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Job Role</label>
                            <input type="text" class="form-control" value="<?php echo ucfirst($staff['job_role']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Employee ID</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($staff['id']); ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 