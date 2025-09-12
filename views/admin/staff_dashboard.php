<?php
// Set session cookie parameters for better security
$currentCookieParams = session_get_cookie_params();
session_set_cookie_params(
    $currentCookieParams["lifetime"],
    $currentCookieParams["path"],
    $currentCookieParams["domain"],
    true, // Secure
    true  // HttpOnly
);

// Start the session after setting cookie parameters
session_start();

// Validate staff access and session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'staff') {
    // Clear any existing session data
    session_unset();
    session_destroy();
    
    // Redirect to login with error message
    header('Location: /urban2/login.php?error=unauthorized');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';
require_once __DIR__ . '/../../controllers/ComplaintController.php';
require_once __DIR__ . '/../../controllers/GarbageScheduleController.php';
require_once __DIR__ . '/../../controllers/NotificationController.php';
require_once __DIR__ . '/../../controllers/PaymentController.php';
require_once __DIR__ . '/../../controllers/CollectionController.php';
require_once __DIR__ . '/../../controllers/AnnouncementController.php';

$adminController = new AdminController();
$complaintController = new ComplaintController();
$garbageScheduleController = new GarbageScheduleController();
$notificationController = new NotificationController();
$paymentController = new PaymentController();
$collectionController = new CollectionController();
$announcementController = new AnnouncementController();

$staffId = $_SESSION['user_id'];
$department = $_SESSION['department'];
$jobRole = $_SESSION['job_role'];

// Get staff details
$staffDetails = $adminController->getStaffDetails($staffId);
$staff = $staffDetails['data'];

// Get dashboard data based on department and role
$dashboardData = array();

if ($department === 'health' && $jobRole === 'garbage_manager') {
    // Garbage Manager Dashboard
    $dashboardData = array(
        'garbage_schedules' => $garbageScheduleController->getAllSchedules(),
        'recent_notifications' => $adminController->getRecentNotifications(),
        'user_feedback' => $adminController->getUserFeedback('garbage')
    );
} elseif ($department === 'engineering' && $jobRole === 'engineer') {
    // Engineer Dashboard
    $dashboardData = array(
        'assigned_complaints' => $complaintController->getAssignedComplaints($staffId),
        'garbage_schedules' => $garbageScheduleController->getAllSchedules(),
        'recent_notifications' => $adminController->getRecentNotifications(),
        'user_feedback' => $adminController->getUserFeedback('engineering')
    );
} elseif ($department === 'it' && $jobRole === 'it_staff') {
    // IT Staff Dashboard
    $dashboardData = array(
        'recent_notifications' => $adminController->getRecentNotifications(),
        'user_feedback' => $adminController->getUserFeedback('it'),
        'announcements' => $announcementController->getAllAnnouncements(),
        'payments' => $paymentController->getPayments()
    );
}

// Get analytics data
$analytics = $adminController->getStaffAnalytics($staffId);
$analyticsData = $analytics['status'] === 'success' ? $analytics['data'] : null;

// Debug information
if ($department === 'it' && $jobRole === 'it_staff') {
    error_log('Dashboard Data for IT Staff:');
    error_log('Announcements: ' . print_r($dashboardData['announcements'], true));
    error_log('Analytics Data: ' . print_r($analyticsData, true));
}

$isITStaff = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'staff' && 
             isset($_SESSION['department']) && strtolower($_SESSION['department']) === 'it' && 
             isset($_SESSION['job_role']) && strtolower($_SESSION['job_role']) === 'it_staff';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Urban Council Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <?php if ($department === 'health' && in_array($jobRole, ['garbage_collector', 'field_visitor'])): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
    <style>
        #staffMap {
            height: 500px;
            width: 100%;
            border-radius: 10px;
            margin-bottom: 20px;
            z-index: 1;
        }
        .status-icon {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-active {
            background-color: #2ecc71;
            box-shadow: 0 0 8px #2ecc71;
        }
        .status-inactive {
            background-color: #e74c3c;
            box-shadow: 0 0 8px #e74c3c;
        }
        #locationStatus {
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .leaflet-popup-content {
            font-size: 14px;
            line-height: 1.6;
        }
        .leaflet-popup-content strong {
            color: #2c3e50;
        }
        .leaflet-control-geocoder {
            margin-top: 60px !important;
        }
        .map-container {
            position: relative;
            margin-bottom: 20px;
        }
        .map-overlay {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background: white;
            padding: 10px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
    <?php endif; ?>
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
        }

        body {
            background: linear-gradient(135deg, #f6f8fd 0%, #f1f4f9 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark);
            line-height: 1.6;
        }

        /* Modern Dark Card Styles */
        .card {
            background: rgba(32, 35, 45, 0.95);
            border-radius: 20px;
            padding: 2rem;
            border: none;
            color: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            height: 200px;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #00A389;
        }

        .card.database::before {
            background: #0EA5E9;
        }

        .card.announcement::before {
            background: #F59E0B;
        }

        .card-title {
            color: #94A3B8;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
        }

        .card-value {
            font-size: 2rem;
            font-weight: 700;
            color: #ffffff;
            margin: 1rem 0;
        }

        .card-subtitle {
            color: #94A3B8;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        /* System Status Card */
        .system-status {
            color: #00A389;
        }

        .system-status .card-value {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .system-status .bx {
            font-size: 2rem;
        }

        /* Database Status Card */
        .database-status {
            color: #0EA5E9;
        }

        .database-status .card-value {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .database-status .bx {
            justify-content: center;
            font-size: 1.2rem;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            margin-right: 0.75rem;
        }

        /* Card Grid Layout */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
        }

        /* Card Content Spacing */
        .card-content {
            padding: 0.5rem 0;
        }

        .card-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 0.5rem;
            margin-top: 0.5rem;
        }

        /* Responsive Adjustments */
        @media (max-width: 1400px) {
            .card-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 992px) {
            .card-grid {
                grid-template-columns: 1fr;
                padding: 0.5rem;
            }
        }

        /* Glassmorphism Sidebar */
        .sidebar {
            background: rgba(61, 54, 92, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            width: 280px;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            color: var(--light);
            transition: var(--transition);
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        /* Main Content with Glassmorphism */
        .main-content {
            background: linear-gradient(135deg, rgba(246, 248, 253, 0.8) 0%, rgba(241, 244, 249, 0.8) 100%);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            min-height: 100vh;
            width: calc(100% - 280px);
            margin-left: 280px;
            padding: 2rem 2.5rem;
            position: relative;
            z-index: 1;
        }

        /* Status Icons with Glassmorphism */
        .status-icon {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .status-icon:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: scale(1.1);
        }

        /* Animation for Cards */
        @keyframes cardFloat {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .card:hover, .stats-card:hover, .system-status-card:hover {
            animation: cardFloat 3s ease-in-out infinite;
        }

        /* Welcome Section with Glassmorphism */
        .welcome-section {
            background: rgba(61, 54, 92, 0.75);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 2rem;
            color: white;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 100%);
            z-index: 0;
        }

        .welcome-section * {
            position: relative;
            z-index: 1;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                width: 100%;
                margin-left: 0;
                padding: 1.5rem;
            }

            .card, .stats-card, .system-status-card {
                backdrop-filter: blur(7px);
                -webkit-backdrop-filter: blur(7px);
            }
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: var(--gradient-primary);
            padding: 2rem;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            color: var(--light);
            transition: var(--transition);
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }

        .sidebar-content::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-content::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .scroll-down-btn {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
        }

        .scroll-down-btn.visible {
            opacity: 1;
            visibility: visible;
        }

        .scroll-down-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-50%) translateY(-5px);
        }

        .profile-section {
            text-align: center;
            padding: 2rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 2rem;
        }

        .profile-section img {
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

        /* Main Content Styles */
        .main-content {
            padding: 2rem 2.5rem;
            background: #f8f9fc;
            min-height: 100vh;
            width: calc(100% - 280px);
            margin-left: 280px;
        }

        /* Enhanced Card Styles */
        .card {
            --card-padding: 1.5rem;
            --card-radius: 15px;
            --card-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            --card-hover-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            --transition-speed: 0.3s;
            
            background: #ffffff;
            border: none;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            opacity: 0;
            transition: opacity var(--transition-speed);
        }

        .card:hover::before {
            opacity: 1;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-hover-shadow);
        }

        /* Card Header Styles */
        .card-header {
            background: linear-gradient(145deg, #ffffff, #f8f9fc);
            border: none;
            padding: var(--card-padding);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header h5 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-header i {
            font-size: 1.25rem;
            color: var(--primary);
            opacity: 0.8;
            transition: transform var(--transition-speed);
        }

        .card:hover .card-header i {
            transform: scale(1.1);
        }

        /* Card Body Enhanced */
        .card-body {
            padding: var(--card-padding);
            position: relative;
        }

        /* Stats Card Specific */
        .stats-card {
            background: linear-gradient(145deg, #ffffff, #f8f9fc);
            padding: var(--card-padding);
            border-radius: var(--card-radius);
            position: relative;
            overflow: hidden;
        }

        .stats-card::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background: linear-gradient(45deg, 
                rgba(255, 255, 255, 0.2) 25%, 
                transparent 25%, 
                transparent 50%, 
                rgba(255, 255, 255, 0.2) 50%, 
                rgba(255, 255, 255, 0.2) 75%, 
                transparent 75%, 
                transparent);
            background-size: 20px 20px;
            opacity: 0.05;
        }

        .stats-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin: 1rem 0;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .stats-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Status Cards */
        .status-card {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            padding: var(--card-padding);
            background: #ffffff;
            border-radius: var(--card-radius);
            transition: all var(--transition-speed);
            position: relative;
            overflow: hidden;
        }

        .status-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary), var(--secondary));
            opacity: 0.8;
        }

        .status-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: rgba(var(--primary-rgb), 0.1);
            color: var(--primary);
            position: relative;
            overflow: hidden;
        }

        .status-icon::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, transparent 30%, rgba(var(--primary-rgb), 0.1) 100%);
            animation: pulse 2s infinite;
        }

        /* Activity Cards */
        .activity-card {
            padding: var(--card-padding);
            background: #ffffff;
            border-radius: var(--card-radius);
            transition: all var(--transition-speed);
            position: relative;
        }

        .activity-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            background: rgba(var(--primary-rgb), 0.1);
            color: var(--primary);
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .activity-time {
            font-size: 0.875rem;
            color: var(--gray);
        }

        /* Card Animations */
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.5;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes shine {
            to {
                background-position: 200% center;
            }
        }

        /* Hover Effects */
        .card:hover .stats-value {
            background-size: 200% auto;
            animation: shine 2s linear infinite;
        }

        .status-card:hover {
            transform: translateX(5px);
        }

        .activity-card:hover {
            transform: translateY(-3px);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .card {
                --card-padding: 1.25rem;
            }

            .stats-value {
                font-size: 1.5rem;
            }

            .status-card {
                flex-direction: column;
                text-align: center;
            }

            .status-icon {
                margin-bottom: 1rem;
            }

            .activity-header {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Card Grid Layout */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
        }

        /* Card Footer */
        .card-footer {
            background: linear-gradient(145deg, #ffffff, #f8f9fc);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem var(--card-padding);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-footer-stats {
            display: flex;
            gap: 1.5rem;
        }

        .footer-stat {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-stat i {
            font-size: 1.1rem;
            color: var(--primary);
        }

        .footer-stat-value {
            font-weight: 600;
            color: var(--dark);
        }

        .footer-stat-label {
            font-size: 0.875rem;
            color: var(--gray);
        }

        /* IT Staff Specific Styles */
        .border-left-success, .border-left-info, .border-left-warning {
            border-left: 4px solid transparent;
            background-image: linear-gradient(to right, rgba(0,0,0,0.02), transparent);
        }

        .border-left-success {
            border-left-color: #1cc88a;
        }

        .border-left-info {
            border-left-color: #36b9cc;
        }

        .border-left-warning {
            border-left-color: #f6c23e;
        }

        .card-body {
            padding: 1.5rem;
        }

        .text-xs {
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .h5.mb-0 {
            font-size: 2rem;
            font-weight: 700;
            margin: 1rem 0;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .col-auto i {
            font-size: 2rem;
            opacity: 0.8;
            transition: transform 0.3s ease;
        }

        .card:hover .col-auto i {
            transform: scale(1.1);
        }

        /* Stats Cards */
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.75rem;
            position: relative;
            overflow: hidden;
            border: none;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
        }

        .stats-card .icon-wrapper {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: rgba(var(--primary-rgb), 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }

        .stats-card .icon-wrapper i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .stats-card h3 {
            font-size: 1.75rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .stats-card p {
            color: var(--gray);
            margin: 0;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .stats-card .trend {
            position: absolute;
            top: 1.75rem;
            right: 1.75rem;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .stats-card .trend.up {
            background: rgba(28, 200, 138, 0.1);
            color: #1cc88a;
        }

        .stats-card .trend.down {
            background: rgba(231, 74, 59, 0.1);
            color: #e74a3b;
        }

        /* Table Styles */
        .table {
            margin: 0;
        }

        .table th {
            background: var(--gradient-primary);
            color: white;
            font-weight: 500;
            border: none;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #eee;
        }

        .table tr:hover {
            background: rgba(61, 54, 92, 0.05);
        }

        /* Button Styles */
        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background: var(--gradient-accent);
            transform: translateY(-2px);
        }

        /* Status Badges */
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
        }

        .badge-success {
            background: var(--gradient-accent);
            color: white;
        }

        .badge-warning {
            background: var(--gradient-highlight);
            color: white;
        }

        /* Notification Styles */
        .notification-badge {
            background: var(--highlight);
            color: var(--dark);
            border-radius: 50%;
            padding: 0.25rem 0.6rem;
            font-size: 0.8rem;
            position: absolute;
            top: -5px;
            right: -5px;
        }

        /* Dropdown Styles */
        .dropdown-menu {
            border: none;
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
            padding: 0.5rem;
            background: white;
        }

        .dropdown-item {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            transition: var(--transition);
        }

        .dropdown-item:hover {
            background: var(--gradient-primary);
            color: white;
        }

        /* Form Controls */
        .form-control {
            border-radius: var(--border-radius);
            padding: 0.8rem 1.2rem;
            border: 1px solid #eee;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(201, 87, 146, 0.25);
        }

        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .welcome-section h2 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 600;
        }

        /* System Status Cards */
        .status-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 1rem;
        }

        .status-card:hover {
            transform: translateX(5px);
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
        }

        .status-card .status-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .status-card.online .status-icon {
            background: rgba(28, 200, 138, 0.1);
            color: #1cc88a;
        }

        .status-card.connected .status-icon {
            background: rgba(54, 185, 204, 0.1);
            color: #36b9cc;
        }

        .status-card .status-content {
            flex: 1;
        }

        .status-card h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .status-card p {
            font-size: 0.9rem;
            color: var(--gray);
            margin: 0;
        }

        /* Recent Notifications */
        .notification-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        }

        .notification-header {
            color: #f6c23e;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .no-notifications {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
            font-style: italic;
            background: rgba(0, 0, 0, 0.02);
            border-radius: 10px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Clear Card Styles */
        .dashboard-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        /* Status Section */
        .status-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .status-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .status-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        /* Notification Section */
        .notification-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        }

        .notification-header {
            color: #f6c23e;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .no-notifications {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
            font-style: italic;
            background: rgba(0, 0, 0, 0.02);
            border-radius: 10px;
        }

        /* System Status Cards */
        .system-status-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        }

        .status-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .status-icon.online {
            background: rgba(28, 200, 138, 0.1);
            color: #1cc88a;
        }

        .status-icon.connected {
            background: rgba(54, 185, 204, 0.1);
            color: #36b9cc;
        }

        .status-details {
            flex: 1;
        }

        .status-details h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0 0 0.25rem 0;
        }

        .status-details p {
            color: var(--gray);
            margin: 0;
            font-size: 0.9rem;
            }

        /* Responsive Fixes */
        @media (max-width: 768px) {
            .main-content {
                width: 100%;
                margin-left: 0;
                padding: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .system-status-card {
                flex-direction: column;
                text-align: center;
                padding: 1.25rem;
            }

            .status-icon {
                margin-bottom: 1rem;
            }
        }

        /* Clear Background */
        body {
            background: #f8f9fc;
        }

        /* Z-index Fixes */
            .sidebar {
            z-index: 1000;
        }

        .main-content {
            z-index: 1;
            position: relative;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 1.5rem 0;
        }

        .card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
                overflow: hidden;
            position: relative;
            }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .system-status::before { background: #28a745; }
        .database-status::before { background: #17a2b8; }
        .announcement-status::before { background: #ffc107; }

        .card-title {
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .card-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .card-value i {
            font-size: 1.75rem;
        }

        .system-status .card-value i { color: #28a745; }
        .database-status .card-value i { color: #17a2b8; }
        .announcement-status .card-value i { color: #ffc107; }

        .card-subtitle {
            font-size: 0.813rem;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .system-status, .database-status, .announcement-status {
            padding: 1.5rem;
        }

        @media (max-width: 768px) {
            .card-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Main content background styles */
            .main-content {
            background: linear-gradient(135deg, #f8f9fe 0%, #f1f4f9 100%);
            min-height: 100vh;
            padding: 2rem 2.5rem;
        }

        /* Card styles with lighter background */
        .card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* Status cards with glass effect */
        .system-status, .database-status, .announcement-status {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            }

        /* Collection type items with softer background */
        .collection-type-item {
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        .collection-type-item:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateX(5px);
        }

        /* Progress bars with lighter background */
        .progress {
            background-color: rgba(0, 0, 0, 0.03);
            border-radius: 10px;
            overflow: hidden;
            height: 15px;
        }

        /* Welcome section with glass effect */
        .welcome-section {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        /* Chart area with lighter background */
        .chart-area {
            background: rgba(255, 255, 255, 0.7);
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }

        /* Table styles with lighter background */
        .table {
            background: rgba(255, 255, 255, 0.9);
        }

        .table thead th {
            background: rgba(0, 0, 0, 0.02);
            border-bottom: none;
            }

        .table-hover tbody tr:hover {
            background: rgba(0, 0, 0, 0.02);
        }

        /* Card headers with subtle gradient */
        .card-header {
            background: linear-gradient(to right, rgba(248, 249, 254, 0.95), rgba(241, 244, 249, 0.95));
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem;
        }

        /* Notification section with glass effect */
        .notification-section {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Badge with glass effect */
        .badge {
            background: rgba(var(--bs-primary-rgb), 0.1);
            border: 1px solid rgba(var(--bs-primary-rgb), 0.2);
            color: var(--bs-primary);
        }

        .badge.bg-success {
            background: rgba(var(--bs-success-rgb), 0.1);
            border-color: rgba(var(--bs-success-rgb), 0.2);
            color: var(--bs-success);
        }

        .badge.bg-info {
            background: rgba(var(--bs-info-rgb), 0.1);
            border-color: rgba(var(--bs-info-rgb), 0.2);
            color: var(--bs-info);
        }

        .badge.bg-warning {
            background: rgba(var(--bs-warning-rgb), 0.1);
            border-color: rgba(var(--bs-warning-rgb), 0.2);
            color: var(--bs-warning);
        }

        .chat-icon, .message-icon-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 2000;
        }

        .chat-icon {
            background: #223A5E; /* Dark blue */
            color: #fff;
            border-radius: 50%;
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            font-size: 28px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .chat-icon:hover {
            background: #345B8C; /* Lighter dark blue */
        }

        .message-icon {
            background: #4FC3F7; /* Light blue */
            color: #fff;
            border-radius: 50%;
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            font-size: 28px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .message-icon:hover {
            background: #81D4FA; /* Lighter light blue */
        }

        .message-count {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #dc3545;
            color: #fff;
            border-radius: 50%;
            padding: 2px 7px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-content">
            <div class="profile-section">
                <img src="<?php echo $staff['profile_picture'] ?? '/urban2/assets/images/default-profile.png'; ?>" 
                     alt="Profile Image">
                <h5 class="text-white mb-1"><?php echo $staff['first_name'] . ' ' . $staff['last_name']; ?></h5>
                <p class="text-muted mb-0"><?php echo ucfirst($department); ?></p>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/">
                        <i class='bx bxs-home'></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'staff_dashboard.php' ? 'active' : ''; ?>" 
                       href="/urban2/views/admin/staff_dashboard.php">
                        <i class='bx bxs-dashboard'></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/admin/staff_profile.php">
                        <i class='bx bxs-user'></i> Profile
                    </a>
                </li>
                <?php if (strtolower($department) === 'health' && strtolower($jobRole) === 'garbage_manager'): ?>
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
                <?php if (strtolower($department) === 'it' && strtolower($jobRole) === 'it_staff'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>" 
                       href="/urban2/views/admin/it_dashboard.php">
                        <i class='bx bxs-credit-card'></i> Payments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'active' : ''; ?>" 
                       href="/urban2/views/admin/announcements.php">
                        <i class='bx bxs-megaphone'></i> Announcements
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/admin/analytics.php">
                        <i class='bx bxs-report'></i> Analytics
                    </a>
                </li>
                <?php endif; ?>
                <?php if (in_array(strtolower($_SESSION['user_role'] ?? ''), ['admin', 'staff'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'complaints.php' ? 'active' : ''; ?>" href="/urban2/views/admin/complaints.php">
                        <i class='bx bxs-error'></i> Complaints
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/admin/notifications.php">
                        <i class='bx bxs-bell'></i> Notifications
                        <?php if (isset($stats['unread_notifications']) && $stats['unread_notifications'] > 0): ?>
                            <span class="badge bg-danger ms-2"><?php echo $stats['unread_notifications']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="/urban2/logout.php" onclick="return confirm('Are you sure you want to logout?');">
                        <i class='bx bxs-log-out'></i> Logout
                    </a>
                </li>
            </ul>
            </div>
            <button class="scroll-down-btn" id="scrollDownBtn">
                <i class='bx bx-chevron-down'></i>
            </button>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container-fluid">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Staff Dashboard</h1>
                    <div class="d-flex align-items-center">
                        <!-- Notification Bell -->
                        <?php include_once __DIR__ . '/../notification_bell.php'; ?>
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="staff_profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <h2 class="mb-4">Welcome, <?php echo $staff['first_name']; ?>!</h2>

                <!-- Analytics Cards Section -->
                <div class="row mb-4">
                    <?php if ($department === 'health' && in_array($jobRole, ['garbage_collector', 'field_visitor'])): ?>
                        <div class="col-12 mb-4">
                            <div class="card shadow-sm" style="background: linear-gradient(135deg, #3D365C 60%, #7C4585 100%); color: #fff; border-radius: 28px; min-height: 260px; padding: 2.5rem 2rem; font-size: 1.25rem;">
                                <div class="card-body" style="padding: 2.5rem 2rem;">
                                    <h2 class="mb-3" style="font-size: 2.2rem; font-weight: 700;">Welcome, <?php echo htmlspecialchars($staff['first_name']); ?>!</h2>
                                    <p class="mb-3" style="font-size: 1.2rem;">Your main task today is to follow your assigned route and update your location as you work. Please check the map below for your current area and recent history.</p>
                                    <hr style="border-color: rgba(255,255,255,0.2);">
                                    <div class="row" style="font-size: 1.1rem;">
                                        <div class="col-md-4 mb-2">
                                            <strong>Assigned Area:</strong> <span id="assignedArea">(Check with supervisor)</span>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong>Shift Time:</strong> <span id="shiftTime">(Check with supervisor)</span>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong>Status:</strong> <span class="badge bg-success" style="font-size: 1rem; padding: 0.7em 1.2em;">Active</span>
                                        </div>
                                    </div>
                                    <div class="mt-4" style="font-size: 1.15rem;">
                                        <em>Stay safe and keep our city clean! 🚛</em>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Full-width map card for garbage_collector and field_visitor -->
                        <div class="mb-4" style="width:100%;">
                            <div class="card shadow-sm" style="border-radius: 28px; min-height: 700px;">
                                <div class="card-header bg-white py-4" style="font-size: 1.3rem;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="mb-0 text-primary" style="font-size: 1.5rem; font-weight: 700;">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            Live Location Tracking
                                        </h4>
                                        <div id="locationStatus" class="d-inline-flex align-items-center px-4 py-3 rounded-pill bg-light" style="font-size: 1.1rem;">
                                            <span class="status-icon status-inactive" id="statusIcon"></span>
                                            <span id="statusText" class="ms-2 fw-medium" style="color: #223A5E; font-weight: 600;"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-0" style="padding: 2rem 2rem;">
                                    <!-- Map Container -->
                                    <div class="map-container position-relative" style="width:100%;height:650px;">
                                        <div id="staffMap" style="width:100%;height:650px;"></div>
                                        <div class="map-overlay">
                                            <div class="bg-white p-3 rounded shadow-sm" style="font-size: 1.1rem;">
                                                <small class="text-muted">Current Area: <span id="currentArea">Loading...</span></small>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Location History -->
                                    <div class="p-4" style="font-size: 1.1rem;">
                                        <h5 class="fw-bold mb-3" style="font-size: 1.2rem;">
                                            <i class="fas fa-history me-2"></i>
                                            Recent Location History
                                        </h5>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Time</th>
                                                        <th>Location</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="locationHistory">
                                                    <!-- Location history will be populated here -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (in_array($jobRole, ['complaint_manager', 'engineer'])): ?>
                    <!-- Assigned Complaints Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Assigned Complaints</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $analyticsData ? $analyticsData['assigned_complaints']['total'] : 0; ?>
                            </div>
                                        <div class="mt-2 text-xs <?php echo $analyticsData && $analyticsData['assigned_complaints']['percentage_change'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <i class="fas fa-<?php echo $analyticsData && $analyticsData['assigned_complaints']['percentage_change'] >= 0 ? 'arrow-up' : 'arrow-down'; ?> mr-1"></i>
                                            <?php echo $analyticsData ? abs($analyticsData['assigned_complaints']['percentage_change']) : 0; ?>% since last month
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div id="assignedComplaintsSparkline"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resolved Complaints Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Resolved Complaints</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $analyticsData ? $analyticsData['resolved_complaints']['total'] : 0; ?>
                                </div>
                                        <div class="mt-2 text-xs text-success">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            <?php echo $analyticsData ? $analyticsData['resolved_complaints']['resolution_rate'] : 0; ?>% Resolution Rate
                                    </div>
                                </div>
                                    <div class="col-auto">
                                        <div id="resolvedComplaintsSparkline"></div>
                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
               
                    <!-- Pending Complaints Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Pending Complaints</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php 
                                            $pendingCount = isset($analyticsData['assigned_complaints']['trend'][0]) 
                                                ? ($analyticsData['assigned_complaints']['total'] - $analyticsData['resolved_complaints']['total'])
                                                : 0;
                                            echo $pendingCount;
                                            ?>
                                        </div>
                                        <div class="mt-2 text-xs">
                                            <i class="fas fa-clock mr-1"></i>
                                            Requires Attention
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                    <?php if (in_array($jobRole, ['garbage_manager', 'garbage_collector'])): ?>
                    <!-- Collection Requests Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Collection Requests</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo isset($dashboardData['collection_requests']) ? count($dashboardData['collection_requests']) : 0; ?>
                    </div>
                                        <div class="mt-2 text-xs text-info">
                                            <i class="fas fa-truck mr-1"></i>
                                            Today's Requests
                </div>
                            </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scheduled Collections Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Scheduled Collections</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo isset($dashboardData['garbage_schedules']) ? count($dashboardData['garbage_schedules']) : 0; ?>
                                </div>
                                        <div class="mt-2 text-xs text-primary">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Upcoming Schedule
                            </div>
                        </div>
                    </div>
                            </div>
                        </div>
                    </div>
                                                    <?php endif; ?>

                    <?php if ($jobRole === 'receptionist'): ?>
                    <!-- Public Area Bookings Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Public Area Bookings</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $analyticsData ? $analyticsData['bookings']['total'] : 0; ?>
                                </div>
                                        <div class="mt-2 text-xs <?php echo $analyticsData && $analyticsData['bookings']['percentage_change'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <i class="fas fa-<?php echo $analyticsData && $analyticsData['bookings']['percentage_change'] >= 0 ? 'arrow-up' : 'arrow-down'; ?> mr-1"></i>
                                    <?php echo $analyticsData ? abs($analyticsData['bookings']['percentage_change']) : 0; ?>% since last month
                            </div>
                        </div>
                                    <div class="col-auto">
                                        <div id="bookingsSparkline"></div>
                    </div>
                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Appointments Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Today's Appointments</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php 
                                            $todayBookings = isset($analyticsData['bookings']['trend'][0]) 
                                                ? $analyticsData['bookings']['trend'][0]['count']
                                                : 0;
                                            echo $todayBookings;
                                            ?>
                                </div>
                                        <div class="mt-2 text-xs text-info">
                                            <i class="fas fa-calendar-day mr-1"></i>
                                            Scheduled Today
                            </div>
                        </div>
                    </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($jobRole === 'it_staff'): ?>
                    <!-- IT Staff Analytics Cards -->
                <div class="card-grid">
                        <!-- System Status Card -->
                    <div class="card">
                        <div class="system-status">
                            <div class="card-title">System Status</div>
                            <div class="card-value">
                                <i class='bx bxs-check-circle'></i>
                                Online
                            </div>
                            <div class="card-subtitle">
                                <i class='bx bxs-time'></i>
                                All Systems Operational
                                </div>
                            </div>
                        </div>

                        <!-- Database Status Card -->
                    <div class="card database">
                        <div class="database-status">
                            <div class="card-title">Database Status</div>
                            <div class="card-value">
                                <i class='bx bxs-data'></i>
                                Connected
                                        </div>
                            <div class="card-subtitle">
                                <i class='bx bxs-time'></i>
                                Last Backup: <?php echo date('Y-m-d H:i'); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Announcements Card -->
                    <div class="card announcement">
                        <div class="announcement-status">
                            <div class="card-title">Recent Announcements</div>
                            <div class="card-value">
                                <i class='bx bxs-megaphone'></i>
                                    <?php 
                                                $announcementCount = 0;
                                                if (isset($dashboardData['announcements']) && is_array($dashboardData['announcements'])) {
                                                    $currentMonth = date('Y-m');
                                                    foreach ($dashboardData['announcements'] as $announcement) {
                                                        if (isset($announcement['created_at'])) {
                                                            $announcementMonth = date('Y-m', strtotime($announcement['created_at']));
                                                            if ($announcementMonth === $currentMonth) {
                                                                $announcementCount++;
                                                            }
                                                        }
                                                    }
                                                }
                                                echo $announcementCount;
                                                ?> 
                                    </div>
                            <div class="card-subtitle">
                                <i class='bx bxs-calendar'></i>
                                                Posted This Month
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                    <?php if (
    (
        (isset(
            
            $department) && $department === 'health') && (isset($jobRole) && $jobRole === 'garbage_manager')
    )
): ?>
<style>
    /* Modernize Garbage Manager dashboard cards */
    .gm-card {
        border-radius: 18px;
        box-shadow: 0 6px 24px rgba(61,54,92,0.10), 0 1.5px 4px rgba(61,54,92,0.08);
        background: #fff;
        color: #3D365C;
        transition: box-shadow 0.3s, transform 0.3s;
        border: none;
        margin-bottom: 2rem;
    }
    .gm-card:hover {
        box-shadow: 0 12px 32px rgba(61,54,92,0.16), 0 2px 8px rgba(61,54,92,0.10);
        transform: translateY(-4px) scale(1.01);
    }
    .gm-card .card-header {
        background: var(--gradient-primary);
        color: #fff;
        border-radius: 18px 18px 0 0;
        font-weight: 600;
        font-size: 1.1rem;
        border-bottom: none;
    }
    .gm-card .card-body {
        padding: 2rem 1.5rem 1.5rem 1.5rem;
    }
    .gm-table {
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 2px 8px rgba(61,54,92,0.07);
    }
    .gm-table th, .gm-table td {
        vertical-align: middle;
        padding: 0.85rem 1rem;
    }
    .gm-table thead th {
        background: var(--gradient-primary);
        color: #fff;
        border: none;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .gm-table tbody tr {
        transition: background 0.2s;
    }
    .gm-table tbody tr:nth-child(even) {
        background: #f8f9fa;
    }
    .gm-table tbody tr:hover {
        background: #f1e6f7;
    }
    .gm-table .btn {
        border-radius: 8px;
        font-size: 1rem;
        padding: 0.4rem 0.8rem;
    }
    .gm-table .btn-info {
        background: var(--secondary);
        color: #fff;
        border: none;
    }
    .gm-table .btn-info:hover {
        background: var(--accent);
    }
    .gm-table .btn-danger {
        background: #e74c3c;
        color: #fff;
        border: none;
    }
    .gm-table .btn-danger:hover {
        background: #c0392b;
    }
    .badge {
        font-size: 0.95rem;
        border-radius: 8px;
        padding: 0.4em 1em;
        font-weight: 500;
    }
    .progress {
        height: 14px;
        border-radius: 8px;
        background: #f1e6f7;
    }
    .progress-bar {
        border-radius: 8px;
        font-size: 0.95rem;
    }
    .collection-type-item .badge {
        font-size: 1rem;
        font-weight: 600;
    }
    @media (max-width: 991px) {
        .gm-card .card-body {
            padding: 1.2rem 0.7rem 1rem 0.7rem;
        }
        .gm-table th, .gm-table td {
            padding: 0.6rem 0.5rem;
        }
    }
</style>
                <?php endif; ?>

                    <?php if ($department === 'health' && $jobRole === 'garbage_manager'): ?>
                    <!-- Garbage Collection Analytics -->
                    <div class="row mb-4">
                        <!-- Collection Efficiency -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card gm-card border-left-success shadow h-100 py-2">
                                <div class="card-header">Collection Efficiency</div>
                            <div class="card-body">
                                    <div class="h5 mb-0 font-weight-bold text-success">
                                                <?php echo isset($analyticsData['collection_efficiency']) ? $analyticsData['collection_efficiency'] : '92%'; ?>
                                            </div>
                                            <div class="mt-2 text-xs text-success">
                                        <i class="fas fa-check-circle me-1"></i> This Month
                                    </div>
                                    </div>
                                </div>
                            </div>
                        <!-- Today's Collections -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card gm-card border-left-primary shadow h-100 py-2">
                                <div class="card-header">Today's Collections</div>
                            <div class="card-body">
                                    <div class="h5 mb-0 font-weight-bold text-primary">
                                                <?php echo isset($dashboardData['todays_collections']) ? count($dashboardData['todays_collections']) : '15'; ?>
                                    </div>
                                            <div class="mt-2 text-xs text-primary">
                                        <i class="fas fa-truck me-1"></i> Scheduled Routes
                                    </div>
                                    </div>
                                    </div>
                                </div>
                        <!-- Pending Requests -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card gm-card border-left-warning shadow h-100 py-2">
                                <div class="card-header">Pending Requests</div>
                                <div class="card-body">
                                    <div class="h5 mb-0 font-weight-bold text-warning">
                                                <?php echo isset($dashboardData['pending_requests']) ? count($dashboardData['pending_requests']) : '8'; ?>
                            </div>
                                            <div class="mt-2 text-xs text-warning">
                                        <i class="fas fa-clock me-1"></i> Require Action
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                        <!-- Active Collectors -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card gm-card border-left-info shadow h-100 py-2">
                                <div class="card-header">Active Collectors</div>
                            <div class="card-body">
                                    <div class="h5 mb-0 font-weight-bold text-info">
                                                <?php echo isset($dashboardData['active_collectors']) ? count($dashboardData['active_collectors']) : '12'; ?>
                                    </div>
                                            <div class="mt-2 text-xs text-info">
                                        <i class="fas fa-users me-1"></i> On Duty Today
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                    <!-- Collection Analytics Charts -->
                    <div class="row mb-4">
                        <!-- Collection Volume Chart -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card gm-card shadow mb-4" style="min-height: 500px;">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Collection Volume This Week</h6>
                    </div>
                    <div class="card-body">
                                    <div class="chart-area" style="height: 400px; position: relative;">
                                        <canvas id="collectionVolumeChart"></canvas>
                        </div>
                                </div>
                            </div>
                        </div>
                        <!-- Collection Types -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card gm-card shadow mb-4" style="min-height: 500px;">
                                <div class="card-header">Collection Types</div>
                                <div class="card-body">
                                    <div class="collection-types-list p-3">
                                        <div class="collection-type-item mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-dark h6">General Waste</span>
                                                <span class="badge bg-primary px-3 py-2">45%</span>
                                        </div>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 45%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <div class="collection-type-item mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-dark h6">Recyclables</span>
                                                <span class="badge bg-success px-3 py-2">30%</span>
                                        </div>
                                            <div class="progress">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <div class="collection-type-item mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-dark h6">Organic Waste</span>
                                                <span class="badge bg-info px-3 py-2">15%</span>
                                        </div>
                                            <div class="progress">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: 15%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                </div>
                                        <div class="collection-type-item">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-dark h6">Special Waste</span>
                                                <span class="badge bg-warning px-3 py-2">10%</span>
                            </div>
                                            <div class="progress">
                                                <div class="progress-bar bg-warning" role="progressbar" style="width: 10%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Garbage Schedules Table -->
                    <div class="card gm-card mt-4">
                        <div class="card-header">Garbage Collection Schedules</div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                <table class="table gm-table">
                                            <thead>
                                                <tr>
                                                    <th>Area</th>
                                                    <th>Date</th>
                                                    <th>Time</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (isset($dashboardData['garbage_schedules']) && is_array($dashboardData['garbage_schedules'])): ?>
                                                    <?php foreach ($dashboardData['garbage_schedules'] as $schedule): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($schedule['area'] ?? ''); ?></td>
                                                            <td><?php echo isset($schedule['schedule_date']) ? date('M d, Y', strtotime($schedule['schedule_date'])) : ''; ?></td>
                                                            <td><?php echo isset($schedule['schedule_time']) ? date('h:i A', strtotime($schedule['schedule_time'])) : ''; ?></td>
                                                            <td>
                                                                <span class="badge bg-<?php echo $schedule['status'] === 'active' ? 'success' : ($schedule['status'] === 'completed' ? 'primary' : 'warning'); ?>">
                                                                    <?php echo ucfirst($schedule['status'] ?? ''); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                    <button class="btn btn-info btn-sm" onclick="editSchedule(<?php echo $schedule['id']; ?>)">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteSchedule(<?php echo $schedule['id']; ?>)">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                            <tr><td colspan="5" class="text-center text-muted">No schedules found.</td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                </div>
                            </div>
                        </div>
                    <!-- Notifications -->
                    <div class="card gm-card mt-4">
                        <div class="card-header">Recent Notifications</div>
                    <div class="card-body">
                        <?php 
                                        $recentNotifications = [];
                                        if (isset($dashboardData['recent_notifications']) && is_array($dashboardData['recent_notifications'])) {
                                            $recentNotifications = $dashboardData['recent_notifications'];
                                        }
                            if (!empty($recentNotifications)): ?>
                                            <?php foreach ($recentNotifications as $notification): ?>
                                                <div class="notification-item d-flex justify-content-between align-items-center mb-2">
                                                    <div>
                                            <i class="fas fa-bell text-warning me-2"></i>
                                                        <?php echo htmlspecialchars($notification['message'] ?? ''); ?>
                        </div>
                                    <small class="text-muted">
                                                        <?php 
                                                        if (isset($notification['created_at'])) {
                                                            echo date('M d, H:i', strtotime($notification['created_at']));
                                                        }
                                                        ?>
                                    </small>
                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-center mb-0">No new notifications</p>
                                        <?php endif; ?>
                                </div>
                            </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Schedule Modal -->
    <?php if ($department === 'health' && $jobRole === 'garbage_manager'): ?>
    <div class="modal fade" id="addScheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Garbage Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addScheduleForm" method="POST" action="/urban2/admin/add_schedule.php">
                        <div class="mb-3">
                            <label class="form-label">Area</label>
                            <input type="text" class="form-control" name="area" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="schedule_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Time</label>
                            <input type="time" class="form-control" name="schedule_time" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="addScheduleForm" class="btn btn-primary">Add Schedule</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($department === 'it' && $jobRole === 'it_staff'): ?>
    <!-- Announcements Section -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Announcements</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dashboardData['announcements'] as $announcement): ?>
                        <tr>
                            <td><?php echo isset($announcement['title']) ? htmlspecialchars($announcement['title']) : 'Untitled'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($department === 'it' && $jobRole === 'it_staff'): ?>
    <!-- Payments Section -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Payments</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dashboardData['payments'] as $payment): ?>
                        <tr>
                            <td><?php echo isset($payment['title']) ? htmlspecialchars($payment['title']) : 'Untitled'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Chat Icon -->
    <a href="../views/chat.php" class="chat-icon">
        <i class="fas fa-comments"></i>
    </a>

    <!-- Message Icon -->
    <div class="message-icon-container">
        <div id="message-icon" class="message-icon" onclick="window.location.href='/urban2/views/chat.php'">
            <i class='bx bxs-message-dots' style="font-size: 24px;"></i>
            <span id="message-count" class="message-count" style="display: none;">0</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/urban2/assets/js/message-notifications.js"></script>
    <script>
        function editSchedule(id) {
            window.location.href = `/urban2/views/admin/edit_schedule.php?id=${id}`;
        }

        function deleteSchedule(id) {
            if (confirm('Are you sure you want to delete this schedule?')) {
                window.location.href = `/urban2/views/admin/delete_schedule.php?id=${id}`;
            }
        }

        function viewComplaint(id) {
            window.location.href = `/urban2/views/admin/view_complaint.php?id=${id}`;
        }

        function updateStatus(id) {
            window.location.href = `/urban2/views/admin/update_complaint_status.php?id=${id}`;
        }
        function viewAnnouncement(id) {
            window.location.href = `/urban2/views/admin/view_announcement.php?id=${id}`;
        }

        function viewPayment(id) {
            window.location.href = `/urban2/views/admin/view_payment.php?id=${id}`;
            
            
        }
    </script>

    <?php if ($department === 'health' && in_array($jobRole, ['garbage_collector', 'field_visitor'])): ?>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
    <style>
        .status-icon {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-active {
            background-color: #2ecc71;
            box-shadow: 0 0 8px #2ecc71;
        }
        .status-inactive {
            background-color: #e74c3c;
            box-shadow: 0 0 8px #e74c3c;
        }
        .map-container {
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }
    </style>
    <?php endif; ?>

    <!-- Add ApexCharts JS -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Common options for sparklines
        const sparklineOptions = {
            chart: {
                type: 'line',
                height: 35,
                width: 100,
                sparkline: {
                    enabled: true
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                }
            },
            stroke: {
                width: 2,
                curve: 'smooth'
            },
            tooltip: {
                fixed: {
                    enabled: false
                },
                x: {
                    show: false
                },
                marker: {
                    show: false
                }
            }
        };

        // Initialize sparklines with their respective data and colors
        const analyticsData = <?php echo json_encode($analyticsData ?? []); ?>;

        if (analyticsData) {
            // Assigned Complaints Sparkline
            if (document.querySelector("#assignedComplaintsSparkline") && analyticsData.assigned_complaints?.trend) {
                new ApexCharts(document.querySelector("#assignedComplaintsSparkline"), {
                    ...sparklineOptions,
                    series: [{
                        data: analyticsData.assigned_complaints.trend.map(item => item.count) || [0]
                    }],
                    colors: ['#4e73df']
                }).render();
            }

            // Resolved Complaints Sparkline
            if (document.querySelector("#resolvedComplaintsSparkline") && analyticsData.resolved_complaints?.trend) {
                new ApexCharts(document.querySelector("#resolvedComplaintsSparkline"), {
                    ...sparklineOptions,
                    series: [{
                        data: analyticsData.resolved_complaints.trend.map(item => item.count) || [0]
                    }],
                    colors: ['#1cc88a']
                }).render();
            }

            // Bookings Sparkline
            if (document.querySelector("#bookingsSparkline") && analyticsData.bookings?.trend) {
                new ApexCharts(document.querySelector("#bookingsSparkline"), {
                    ...sparklineOptions,
                    series: [{
                        data: analyticsData.bookings.trend.map(item => item.count) || [0]
                    }],
                    colors: ['#36b9cc']
                }).render();
            }
        }
    });
    </script>

    <!-- Add Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize charts when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Ticket Volume Chart
            if (document.getElementById('ticketVolumeChart')) {
                new Chart(document.getElementById('ticketVolumeChart'), {
                    type: 'line',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            label: 'Tickets',
                            data: [250, 200, 150, 100, 350, 300, 250],
                            borderColor: '#4e73df',
                            tension: 0.3,
                            fill: false
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Collection Volume Chart
            if (document.getElementById('collectionVolumeChart')) {
                new Chart(document.getElementById('collectionVolumeChart'), {
                    type: 'line',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            label: 'Collections',
                            data: [15, 18, 12, 20, 16, 14, 17],
                            borderColor: '#1cc88a',
                            tension: 0.3,
                            fill: false
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        });
    </script>

    <style>
        .progress {
            border-radius: 50%;
            background: rgba(0,0,0,0.1);
        }
        .progress-bar {
            border-radius: 50%;
        }
        .notification-item {
            padding: 10px;
            border-radius: 5px;
            background: #f8f9fc;
        }
        .notification-item:hover {
            background: #eaecf4;
        }
        .chart-area {
            position: relative;
            height: 300px;
        }
    </style>

    <!-- Location Tracking Section -->
    <?php if ($department === 'health' && in_array($jobRole, ['garbage_collector', 'field_visitor'])): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Live Location Tracking
                        </h5>
                        <div id="locationStatus" class="d-inline-flex align-items-center px-3 py-2 rounded-pill bg-light">
                            <span class="status-icon status-inactive" id="statusIcon"></span>
                            <span id="statusText" class="ms-2 fw-medium"></span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Map Container -->
                    <div class="map-container position-relative">
                        <div id="staffMap"></div>
                        <div class="map-overlay">
                            <div class="bg-white p-2 rounded shadow-sm">
                                <small class="text-muted">Current Area: <span id="currentArea">Loading...</span></small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Location History -->
                    <div class="p-4">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-history me-2"></i>
                            Recent Location History
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Time</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="locationHistory">
                                    <!-- Location history will be populated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Location tracking specific styles */
        #staffMap {
            height: 500px;
            width: 100%;
            border-radius: 0;
            margin: 0;
            z-index: 1;
        }

        .map-container {
            position: relative;
            width: 100%;
            background: #f8f9fa;
            overflow: hidden;
        }

        .map-overlay {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .status-icon {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
            transition: all 0.3s ease;
        }

        .status-active {
            background-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
            animation: pulse 2s infinite;
        }

        .status-inactive {
            background-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.2);
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
            }
            70% {
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        /* Table styles */
        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .table td {
            vertical-align: middle;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
        }

        /* Badge styles */
        .badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 600;
            border-radius: 0.25rem;
        }

        /* Leaflet map customization */
        .leaflet-control-zoom {
            border: none !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }

        .leaflet-control-zoom a {
            background-color: white !important;
            color: #374151 !important;
            border: none !important;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 0.5rem !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1) !important;
        }

        .leaflet-popup-content {
            margin: 0.75rem 1rem !important;
            font-size: 0.875rem !important;
        }
    </style>
    <?php endif; ?>

    <!-- Add required libraries for location tracking -->
    <?php if ($department === 'health' && in_array($jobRole, ['garbage_collector', 'field_visitor'])): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
    <script src="/urban2/assets/js/location-tracking.js"></script>
    <?php endif; ?>

    <!-- Add this script before the closing body tag -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarContent = document.querySelector('.sidebar-content');
            const scrollDownBtn = document.getElementById('scrollDownBtn');

            // Show/hide scroll button based on scroll position
            sidebarContent.addEventListener('scroll', function() {
                const maxScroll = sidebarContent.scrollHeight - sidebarContent.clientHeight;
                if (sidebarContent.scrollTop < maxScroll) {
                    scrollDownBtn.classList.add('visible');
                } else {
                    scrollDownBtn.classList.remove('visible');
                }
            });

            // Scroll down when button is clicked
            scrollDownBtn.addEventListener('click', function() {
                sidebarContent.scrollTo({
                    top: sidebarContent.scrollHeight,
                    behavior: 'smooth'
                });
            });

            // Initial check for scroll button visibility
            const maxScroll = sidebarContent.scrollHeight - sidebarContent.clientHeight;
            if (maxScroll > 0) {
                scrollDownBtn.classList.add('visible');
            }
        });
    </script>

    <?php if ($department === 'health' && in_array($jobRole, ['garbage_collector', 'field_visitor'])): ?>
    <div class="welcome-section mb-4">
        <h2>Welcome, <?php echo htmlspecialchars($staff['first_name']); ?>!</h2>
        <p>Your main task today is to follow your assigned route and update your location as you work. Please check the map below for your current area and recent history.</p>
    </div>
<?php endif; ?>
</body>
</html> 