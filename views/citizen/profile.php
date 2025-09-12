<?php
session_start();
require_once '../../controllers/CitizenController.php';
require_once '../../controllers/ProfileController.php';
require_once '../../controllers/AuthController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'citizen') {
    header('Location: /urban2/login.php');
    exit();
}

$citizenController = new CitizenController();
$profileController = new ProfileController();

$citizenInfo = $citizenController->getCitizenInfo($_SESSION['user_id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $result = $profileController->updateProfile($_SESSION['user_id'], $_POST);
        $passwordMessage = '';
        // Password update logic
        if (!empty($_POST['current_password']) || !empty($_POST['new_password']) || !empty($_POST['confirm_password'])) {
            if (empty($_POST['current_password']) || empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
                $passwordMessage = 'Please fill in all password fields to update your password.';
            } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
                $passwordMessage = 'New passwords do not match.';
            } else {
                // Validate current password
                $authController = new AuthController();
                if (!$authController->verifyPassword($_SESSION['user_id'], $_POST['current_password'])) {
                    $passwordMessage = 'Current password is incorrect.';
                } else {
                    $updateResult = $profileController->updatePassword($_SESSION['user_id'], $_POST['new_password']);
                    if ($updateResult['success']) {
                        $passwordMessage = 'Password updated successfully!';
                    } else {
                        $passwordMessage = $updateResult['message'];
                    }
                }
            }
        }
        if ($result['success'] && empty($passwordMessage)) {
            $_SESSION['success'] = "Profile updated successfully!";
            header('Location: /urban2/views/citizen/profile.php');
            exit();
        } elseif (!$result['success']) {
            $_SESSION['error'] = $result['message'];
        } elseif (!empty($passwordMessage)) {
            $_SESSION['error'] = $passwordMessage;
        }
    } elseif (isset($_FILES['profile_picture'])) {
        $result = $profileController->updateProfilePicture($_SESSION['user_id'], $_FILES['profile_picture']);
        if ($result['success']) {
            $_SESSION['success'] = "Profile picture updated successfully!";
            header('Location: /urban2/views/citizen/profile.php');
            exit();
        } else {
            $_SESSION['error'] = $result['message'];
        }
    }
}

// Get updated citizen info after form submission
$citizenInfo = $citizenController->getCitizenInfo($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management</title>
    <link rel="stylesheet" href="/urban2/assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
    :root {
        --sidebar-width: 250px;
        --main-bg: #0d112b;
        --profile-card-bg: #16213e;
        --profile-card-border: #14532d;
        --profile-accent: #14532d;
        --profile-btn: #14532d;
        --profile-btn-hover: #166534;
        --profile-font: #f1f5f9;
        --profile-heading: #fff;
        --profile-input-bg: #22325c;
        --profile-input-border: #14532d;
        --profile-input-focus: #166534;
        --profile-subtitle: #14532d;
    }
    body {
        background: var(--main-bg);
        min-height: 100vh;
        color: var(--profile-font);
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
    .main-content {
        margin-left: var(--sidebar-width);
        padding: 2.5rem 2rem 2.5rem 2rem;
        background: transparent;
        min-height: 100vh;
    }
    .profile-title {
        color: #fff;
        font-weight: 700;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }
    .profile-subtitle {
        color: #d1fae5;
        font-size: 1.1rem;
        margin-bottom: 2.2rem;
    }
    .profile-card {
        background: var(--profile-card-bg);
        border: 1.5px solid var(--profile-card-border);
        border-radius: 18px;
        box-shadow: 0 4px 24px rgba(20,83,45,0.10);
        padding: 2.5rem 2rem 2rem 2rem;
        margin-bottom: 2rem;
        color: var(--profile-font);
    }
    .profile-card .card-body {
        padding: 0;
    }
    .profile-card .form-label {
        color: #fff;
        font-weight: 500;
    }
    .profile-card .form-control {
        border-radius: 10px;
        border: 1.5px solid var(--profile-card-border);
        padding: 0.75rem 1rem;
        font-size: 1.05rem;
        background: var(--profile-input-bg);
        color: #e5e7eb;
        box-shadow: 0 1px 4px rgba(20,83,45,0.08);
        transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    }
    .profile-card .form-control:focus {
        border-color: var(--profile-input-focus);
        box-shadow: 0 0 0 2px rgba(22,101,52,0.18);
        background: #1e293b;
        color: #fff;
    }
    .profile-card .btn-primary {
        background: var(--profile-btn);
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 1.1rem;
        padding: 0.75rem 0;
        transition: background 0.2s, box-shadow 0.2s;
        color: #fff;
        box-shadow: 0 2px 8px rgba(20,83,45,0.10);
        letter-spacing: 0.5px;
    }
    .profile-card .btn-primary:hover {
        background: var(--profile-btn-hover);
        color: #fff;
        box-shadow: 0 4px 16px rgba(22,101,52,0.13);
    }
    .profile-card .img-fluid {
        box-shadow: 0 2px 12px rgba(20,83,45,0.10);
        border: 4px solid var(--profile-card-bg);
        background: #22325c;
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
        .profile-card {
            padding: 1.2rem 0.7rem 1.2rem 0.7rem;
        }
    }
    .alert {
        border-radius: 12px;
        font-size: 1.08rem;
        font-family: inherit;
        color: #fff;
        background: #14532d;
        border: none;
        box-shadow: 0 2px 8px rgba(20,83,45,0.10);
    }
    .alert-danger { background: #ef4444; }
    .alert-success { background: #14532d; }
    .alert-warning { background: #f59e42; color: #fff; }
    hr.my-4 {
        border-top: 2px solid #14532d;
        opacity: 0.2;
    }
    </style>
</head>
<body>
    <!-- Header removed for a cleaner, more focused profile page -->
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="profile-section">
                <img src="<?php echo $citizenInfo['profile_image'] ?? '/urban2/assets/images/default-avatar.png'; ?>" 
                     alt="Profile Image">
                <h5 class="text-white mb-1"><?php echo htmlspecialchars($citizenInfo['name']); ?></h5>
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
                    <a class="nav-link active" href="/urban2/views/citizen/profile.php">
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
            <div class="container">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="profile-title">Profile Management</h2>
                        <p class="profile-subtitle">Update your personal information and profile picture below.</p>
                    </div>
                </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (!$citizenInfo['profile_completed']): ?>
            <div class="alert alert-warning">
                Please complete your profile by filling in all required fields marked with *.
            </div>
        <?php endif; ?>

                <div class="row g-4 align-items-stretch">
            <div class="col-md-4">
                        <div class="profile-card h-100">
                            <div class="card-body text-center py-5">
                        <img src="<?php echo $citizenInfo['profile_picture'] ?? '/urban2/assets/images/default-profile.png'; ?>" 
                             alt="Profile Picture" 
                                     class="img-fluid rounded-circle mb-3" 
                                     style="max-width: 180px;">
                                <form action="" method="POST" enctype="multipart/form-data" class="mt-4">
                                    <div class="form-group mb-2">
                                        <label for="profile_picture" class="form-label">Update Profile Picture</label>
                                        <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Upload Picture</button>
                                </form>
                            </div>
                        </div>
                    </div>
            <div class="col-md-8">
                        <div class="profile-card h-100">
                            <div class="card-body p-4">
                        <form action="" method="POST">
                                    <div class="form-group mb-3">
                                        <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($citizenInfo['first_name'] ?? ''); ?>" required>
                            </div>
                                    <div class="form-group mb-3">
                                        <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($citizenInfo['last_name'] ?? ''); ?>" required>
                            </div>
                                    <div class="form-group mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($citizenInfo['email'] ?? ''); ?>" required>
                            </div>
                                    <div class="form-group mb-3">
                                        <label for="phone" class="form-label">Phone *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($citizenInfo['phone'] ?? ''); ?>" required>
                            </div>
                                    <div class="form-group mb-4">
                                        <label for="address" class="form-label">Address *</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($citizenInfo['address'] ?? ''); ?></textarea>
                                    </div>
                                    <hr class="my-4">
                                    <h5 class="mb-3">Change Password</h5>
                                    <div class="form-group mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" autocomplete="new-password">
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" autocomplete="new-password">
                                    </div>
                                    <button type="submit" name="update_profile" class="btn btn-primary w-100">Update Profile</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html> 