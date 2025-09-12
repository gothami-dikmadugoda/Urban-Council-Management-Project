<?php
session_start();
require_once __DIR__ . '/../../controllers/LayoutController.php';
require_once __DIR__ . '/../../controllers/CitizenController.php';
require_once __DIR__ . '/../../controllers/CollectionController.php';

// Debug session
error_log("Session data: " . print_r($_SESSION, true));

// Check if user is logged in and is a citizen
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to continue.";
    header('Location: /urban2/login.php');
    exit();
}

$citizenController = new CitizenController();
$collectionController = new CollectionController();

// Get citizen information
$citizenInfo = $citizenController->getCitizenInfo($_SESSION['user_id']);

// Debug POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST data: " . print_r($_POST, true));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_collection_request'])) {
    try {
        $data = [
            'user_id' => $_SESSION['user_id'],
            'area' => $_POST['area'] ?? '',
            'collection_date' => $_POST['collection_date'] ?? '',
            'collection_time' => $_POST['collection_time'] ?? '',
            'waste_type' => $_POST['waste_type'] ?? '',
            'waste_volume' => $_POST['waste_volume'] ?? '',
            'special_instructions' => $_POST['special_instructions'] ?? ''
        ];

        // Debug collection request data
        error_log("Collection request data: " . print_r($data, true));

        $result = $collectionController->createCollectionRequest($data);
        
        if ($result['success']) {
            $_SESSION['success'] = "Collection request submitted successfully!";
            header('Location: /urban2/views/citizen/garbage-schedule.php');
            exit();
        } else {
            $_SESSION['error'] = $result['message'];
        }
    } catch (Exception $e) {
        error_log("Error in form submission: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while submitting your request. Please try again.";
    }
}

// Get user's active collection requests
$activeRequests = $collectionController->getUserCollectionRequests($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Garbage Collection - Urban Council</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
    :root {
        --sidebar-width: 250px;
        --main-bg: #0d112b;
        --sidebar-bg: #181e3a;
        --card-bg: #16213e;
        --card-shadow: 0 6px 32px rgba(34,197,94,0.10), 0 2px 8px rgba(34,197,94,0.06);
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
        padding: 3rem 2rem 3rem 2rem;
        background: var(--main-bg);
        min-height: 100vh;
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

    .dashboard-card {
        background: var(--card-bg);
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        padding: 2.5rem 2.5rem 2rem 2.5rem;
        margin-bottom: 2.5rem;
        transition: box-shadow 0.18s, transform 0.18s;
        color: var(--text);
    }

    .dashboard-card:hover {
        box-shadow: 0 8px 36px rgba(44,62,80,0.13);
        transform: translateY(-4px) scale(1.01);
    }

    .dashboard-card h4 {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--accent);
        margin-bottom: 1.5rem;
        letter-spacing: 0.2px;
    }

    .form-label {
        font-weight: 700;
        color: var(--accent);
        font-size: 1.08rem;
        letter-spacing: 0.1px;
    }

    .form-control, .form-select {
        border-radius: 12px;
        border: 1.5px solid #22c55e;
        padding: 1rem 1.3rem;
        font-size: 1.08rem;
        background: #22325c;
        color: var(--text);
        transition: border-color 0.18s, box-shadow 0.18s;
    }

    .form-control:focus, .form-select:focus {
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

    .mb-3 {
        margin-bottom: 1.5rem !important;
    }

    .alert {
        border-radius: 12px;
        font-size: 1.08rem;
        font-family: inherit;
    }

    .status-badge {
        padding: 0.5rem 0.75rem;
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
    }

    .status-approved {
        background-color: #d1fae5;
        color: #065f46;
    }

    .status-rejected {
        background-color: #fee2e2;
        color: #991b1b;
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
            padding: 1.2rem 0.7rem 1.2rem 0.7rem;
        }
    }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="profile-section">
            <img src="<?php echo $citizenInfo['profile_image'] ?? '/urban2/assets/images/default-avatar.png'; ?>" 
                 alt="Profile Image">
            <h5 class="text-white mb-1"><?php echo htmlspecialchars($citizenInfo['first_name'] . ' ' . $citizenInfo['last_name']); ?></h5>
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
                <a class="nav-link" href="/urban2/views/citizen/notifications.php">
                    <i class="fas fa-bell"></i> Notifications
                    <?php if (isset($citizenInfo['unread_notifications']) && $citizenInfo['unread_notifications'] > 0): ?>
                        <span class="badge bg-danger ms-2"><?php echo $citizenInfo['unread_notifications']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/urban2/views/citizen/requests.php">
                    <i class="fas fa-clipboard-list"></i> Active Requests
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
        <div class="container-fluid">
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

            <h2 class="mb-4">Schedule Garbage Collection</h2>
            
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <div class="dashboard-card">
                        <h4><i class="fas fa-calendar-plus"></i> Request Schedule Collection</h4>
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                            
                            <div class="mb-3">
                                <label for="area" class="form-label">Collection Area *</label>
                                <input type="text" class="form-control" id="area" name="area" 
                                       value="<?php echo htmlspecialchars($citizenInfo['area'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Please enter the collection area.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="collection_date" class="form-label">Preferred Collection Date *</label>
                                <input type="date" class="form-control" id="collection_date" name="collection_date" required 
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                <div class="invalid-feedback">Please select a collection date.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="collection_time" class="form-label">Preferred Collection Time *</label>
                                <select class="form-select" id="collection_time" name="collection_time" required>
                                    <option value="">Select time</option>
                                    <option value="morning">Morning (8:00 AM - 12:00 PM)</option>
                                    <option value="afternoon">Afternoon (12:00 PM - 4:00 PM)</option>
                                    <option value="evening">Evening (4:00 PM - 8:00 PM)</option>
                                </select>
                                <div class="invalid-feedback">Please select a collection time.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="waste_type" class="form-label">Waste Type *</label>
                                <select class="form-select" id="waste_type" name="waste_type" required>
                                    <option value="">Select waste type</option>
                                    <option value="household">Household Waste</option>
                                    <option value="garden">Garden Waste</option>
                                    <option value="construction">Construction Waste</option>
                                    <option value="hazardous">Hazardous Waste</option>
                                    <option value="recyclable">Recyclable Waste</option>
                                </select>
                                <div class="invalid-feedback">Please select a waste type.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="waste_volume" class="form-label">Estimated Waste Volume *</label>
                                <select class="form-select" id="waste_volume" name="waste_volume" required>
                                    <option value="">Select volume</option>
                                    <option value="small">Small (1-2 bags)</option>
                                    <option value="medium">Medium (3-5 bags)</option>
                                    <option value="large">Large (6+ bags)</option>
                                </select>
                                <div class="invalid-feedback">Please select waste volume.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="special_instructions" class="form-label">Special Instructions</label>
                                <textarea class="form-control" id="special_instructions" name="special_instructions" rows="3" 
                                          placeholder="Any special instructions for the collection team"></textarea>
                            </div>
                            
                            <button type="submit" name="submit_collection_request" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Request
                            </button>
                        </form>
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
</script>
</body>
</html> 