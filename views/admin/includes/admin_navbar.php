<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /urban2/login.php');
    exit;
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/urban2/admin/dashboard.php">
            <i class="fas fa-city"></i> Urban Council Admin
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    
                    <a class="nav-link" href="/urban2/admin/dashboard.php">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/admin/complaints.php">
                        <i class="fas fa-exclamation-circle"></i> Complaints
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/admin/users.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/admin/settings.php">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php if ($department === 'health'): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<?php endif; ?>
<div id="locationStatus">
    <span class="status-icon status-inactive" id="statusIcon"></span>
    <span id="statusText">Location tracking inactive</span>
</div>
<script>
function startLocationTracking() {
    if ("geolocation" in navigator) {
        // Get location every 30 seconds
        function updateLocation() {
            navigator.geolocation.getCurrentPosition(function(position) {
                // Send location to server
                fetch('/urban2/api/update_location.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    })
                })
            });
        }
        updateLocation();
        locationUpdateInterval = setInterval(updateLocation, 30000);
    }
}
</script>
<style>
.status-icon {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}
.status-active {
    background: #2ecc71;
}
.status-inactive {
    background: #e74c3c;
}
</style> 