<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Settings.php';
require_once __DIR__ . '/../models/Department.php';

$database = new Database();
$db = $database->getConnection();
$settings = new Settings($db);
$department = new Department($db);
$siteSettings = $settings->getSettings();
$departments = $department->getAllDepartments();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $siteSettings['site_name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/urban2/assets/css/style.css">
    <style>
        .header, .main-header {
            background: linear-gradient(120deg, #003314 0%, #1a237e 100%) !important;
            color: #fff !important;
            min-height: 90px;
            padding-top: 0.7rem;
            padding-bottom: 0.7rem;
        }
        .main-header {
            min-height: 100px;
            padding-top: 1.2rem;
            padding-bottom: 1.2rem;
            display: flex;
            align-items: center;
        }
        .navbar {
            background: linear-gradient(90deg, #003314 0%, #1a237e 100%) !important;
            color: #fff !important;
            min-height: 70px;
            padding-top: 1rem;
            padding-bottom: 1rem;
            font-size: 1.15rem;
        }
        .site-title, .site-subtitle, .contact-info a, .contact-info i, .navbar-nav .nav-link {
            color: #fff !important;
        }
        .gov-seal {
            height: 70px;
            width: auto;
            margin-right: 1.5rem;
        }
        .site-info {
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="top-bar">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class='bx bxs-envelope'></i>
                            <a href="mailto:<?php echo $siteSettings['contact_email']; ?>"><?php echo $siteSettings['contact_email']; ?></a>
                        </div>
                        <div class="contact-item">
                            <i class='bx bxs-phone'></i>
                            <a href="tel:<?php echo $siteSettings['contact_phone']; ?>"><?php echo $siteSettings['contact_phone']; ?></a>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="language-selector">
                            <a href="#" class="active">English</a>
                            <a href="#">සිංහල</a>
                            <a href="#">தமிழ்</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-header">
            <div class="container">
                <div class="site-info">
                    <img src="/urban2/assets/images/gov-seal.jpeg" alt="Government Seal" class="gov-seal">
                    <div class="site-title-wrapper">
                        <a href="/urban2" class="site-title">
                            <i class='bx bxs-city'></i>
                            <?php echo $siteSettings['site_name']; ?>
                        </a>
                        <div class="site-subtitle">Government of Sri Lanka</div>
                    </div>
                </div>
            </div>
        </div>

        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="/urban2">
                                <i class='bx bxs-home'></i>HOME
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'departments.php' ? 'active' : ''; ?>" href="/urban2/departments.php">
                                <i class='bx bxs-building'></i>DEPARTMENTS
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>" href="/urban2/views/about.php">
                                <i class='bx bxs-info-circle'></i>ABOUT US
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''; ?>" href="/urban2/services.php">
                                <i class='bx bxs-cog'></i>SERVICES
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="/urban2/contact.php">
                                <i class='bx bxs-contact'></i>CONTACT
                            </a>
                        </li>
                    </ul>
                    <ul class="navbar-nav">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="/urban2/views/admin/dashboard.php">
                                        <i class='bx bxs-dashboard'></i>DASHBOARD
                                    </a>
                                </li>
                            <?php elseif ($_SESSION['user_role'] === 'staff'): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="/urban2/views/admin/staff_dashboard.php">
                                        <i class='bx bxs-dashboard'></i>DASHBOARD
                                    </a>
                                </li>
                            <?php elseif ($_SESSION['user_role'] === 'citizen'): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="/urban2/views/citizen/dashboard.php">
                                        <i class='bx bxs-dashboard'></i>DASHBOARD
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/urban2/logout.php">
                                    <i class='bx bxs-log-out'></i>LOGOUT
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/urban2/login.php">
                                    <i class='bx bxs-log-in'></i>LOGIN
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/urban2/register.php">
                                    <i class='bx bxs-user-plus'></i>REGISTER
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main>
        <!-- Main content will be inserted here -->
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 