<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<style>
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 280px;
        background: #2c3e50;
        padding: 20px;
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .sidebar-header {
        padding: 10px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 20px;
    }

    .sidebar-header h3 {
        color: white;
        font-size: 1.2rem;
        margin: 0;
    }

    .nav-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .nav-item {
        margin-bottom: 5px;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        color: #ecf0f1;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    .nav-link.active {
        background: #3498db;
        color: #fff;
    }

    .nav-link i {
        margin-right: 10px;
        font-size: 1.2rem;
    }

    .nav-text {
        font-size: 0.95rem;
    }

    .logout-link {
        position: absolute;
        bottom: 20px;
        left: 20px;
        right: 20px;
    }

    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }
        .sidebar.active {
            transform: translateX(0);
        }
    }
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <h3>Urban Council Admin</h3>
    </div>
    
    <ul class="nav-list">
        <li class="nav-item">
            <a href="/urban2/" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class='bx bxs-home'></i>
                <span class="nav-text">Home</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/urban2/views/admin/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class='bx bxs-dashboard'></i>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/urban2/views/admin/staff.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'staff.php' ? 'active' : ''; ?>">
                <i class='bx bxs-user-detail'></i>
                <span class="nav-text">Manage Staff</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/urban2/views/admin/complaints.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'complaints.php' ? 'active' : ''; ?>">
                <i class='bx bxs-message-square-detail'></i>
                <span class="nav-text">Complaints</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/urban2/views/admin/track_staff.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'track_staff.php' ? 'active' : ''; ?>">
                <i class='bx bxs-map'></i>
                <span class="nav-text">Track Staff</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/urban2/views/admin/reports.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <i class='bx bxs-report'></i>
                <span class="nav-text">Reports</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/urban2/views/admin/settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class='bx bxs-cog'></i>
                <span class="nav-text">Settings</span>
            </a>
        </li>
    </ul>

    <div class="logout-link">
        <a href="/urban2/logout.php" class="nav-link">
            <i class='bx bxs-log-out'></i>
            <span class="nav-text">Logout</span>
        </a>
    </div>
</div>

<script>
// Add mobile toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.createElement('button');
    toggleBtn.className = 'btn btn-primary d-md-none position-fixed';
    toggleBtn.style.cssText = 'top: 10px; left: 10px; z-index: 1001;';
    toggleBtn.innerHTML = '<i class="bx bx-menu"></i>';
    document.body.appendChild(toggleBtn);

    toggleBtn.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
    });
});
</script> 