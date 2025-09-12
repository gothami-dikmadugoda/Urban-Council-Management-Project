<?php
// Include necessary files
require_once 'config/database.php';
require_once 'includes/functions.php';

// Set page title
$page_title = 'Status Dashboard';

// Get today's statistics
$today_appointments = get_today_appointments_count($conn);
$today_visitors = get_today_visitors_count($conn);

// Get status breakdown
$status_breakdown = get_status_breakdown($conn);

// Get current time
$now = date('Y-m-d H:i:s');

// Include header
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4>Reception Status Dashboard</h4>
                            <p class="text-muted">
                                Overview of today's reception activity: <?php echo date('F j, Y'); ?>
                            </p>
                            <div class="d-flex">
                                <div class="me-4">
                                    <h6>Current Time</h6>
                                    <h3 class="text-primary" id="current-time">
                                        <?php echo date('H:i:s'); ?>
                                    </h3>
                                </div>
                                <div>
                                    <h6>Reception Status</h6>
                                    <h3 class="text-success">
                                        <i class="fas fa-check-circle me-2"></i>Active
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-6">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body py-4 stat-card">
                                            <h2 class="display-4 fw-bold"><?php echo $today_appointments; ?></h2>
                                            <p class="mb-0">Today's Appointments</p>
                                            <i class="fas fa-calendar-check icon"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-success text-white">
                                        <div class="card-body py-4 stat-card">
                                            <h2 class="display-4 fw-bold"><?php echo $today_visitors; ?></h2>
                                            <p class="mb-0">Today's Visitors</p>
                                            <i class="fas fa-users icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Appointments Status -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow h-100">
                <div class="card-header bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie me-2 text-primary"></i>
                            Appointment Status Breakdown
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="mb-2">
                                <div class="status-indicator status-pending mx-auto"></div>
                            </div>
                            <h3 class="h2 text-warning"><?php echo $status_breakdown['pending']; ?></h3>
                            <p class="text-muted">Pending</p>
                        </div>
                        <div class="col-3">
                            <div class="mb-2">
                                <div class="status-indicator status-confirmed mx-auto"></div>
                            </div>
                            <h3 class="h2 text-primary"><?php echo $status_breakdown['confirmed']; ?></h3>
                            <p class="text-muted">Confirmed</p>
                        </div>
                        <div class="col-3">
                            <div class="mb-2">
                                <div class="status-indicator status-completed mx-auto"></div>
                            </div>
                            <h3 class="h2 text-info"><?php echo $status_breakdown['completed']; ?></h3>
                            <p class="text-muted">Completed</p>
                        </div>
                        <div class="col-3">
                            <div class="mb-2">
                                <div class="status-indicator status-cancelled mx-auto"></div>
                            </div>
                            <h3 class="h2 text-danger"><?php echo $status_breakdown['cancelled']; ?></h3>
                            <p class="text-muted">Cancelled</p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div id="chart-container" style="height: 250px;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow h-100">
                <div class="card-header bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2 text-primary"></i>
                            Recent Activity
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    // Get recent appointments
                    $sql = "SELECT a.*, v.name as visitor_name 
                            FROM appointments a
                            JOIN visitors v ON a.visitor_id = v.visitor_id
                            WHERE DATE(a.appointment_date) = CURDATE()
                            ORDER BY a.appointment_date DESC
                            LIMIT 5";
                    $recent_appointments = $conn->query($sql);
                    
                    // Get recent visitors
                    $sql = "SELECT * FROM visitors 
                            WHERE DATE(checkin_time) = CURDATE()
                            ORDER BY checkin_time DESC
                            LIMIT 5";
                    $recent_visitors = $conn->query($sql);
                    ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Recent Appointments</h6>
                            <?php if ($recent_appointments->num_rows > 0): ?>
                                <ul class="list-group list-group-flush">
                                    <?php while($appointment = $recent_appointments->fetch_assoc()): ?>
                                        <li class="list-group-item px-0">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($appointment['visitor_name']); ?></h6>
                                                <small class="text-muted"><?php echo date('H:i', strtotime($appointment['appointment_date'])); ?></small>
                                            </div>
                                            <p class="mb-1 small"><?php echo htmlspecialchars($appointment['purpose'] ?: 'No purpose specified'); ?></p>
                                            <span class="badge <?php echo get_status_badge($appointment['status']); ?>"><?php echo $appointment['status']; ?></span>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-center text-muted">No appointments today</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Recent Visitors</h6>
                            <?php if ($recent_visitors->num_rows > 0): ?>
                                <ul class="list-group list-group-flush">
                                    <?php while($visitor = $recent_visitors->fetch_assoc()): ?>
                                        <li class="list-group-item px-0">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($visitor['name']); ?></h6>
                                                <small class="text-muted"><?php echo date('H:i', strtotime($visitor['checkin_time'])); ?></small>
                                            </div>
                                            <p class="mb-1 small"><?php echo htmlspecialchars($visitor['purpose'] ?: 'No purpose specified'); ?></p>
                                            <?php if ($visitor['id_verified']): ?>
                                                <span class="badge bg-success">ID Verified</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">ID Not Verified</span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-center text-muted">No visitors today</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Additional JavaScript for status page
$extra_js = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Update current time every second
    setInterval(function() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, "0");
        const minutes = String(now.getMinutes()).padStart(2, "0");
        const seconds = String(now.getSeconds()).padStart(2, "0");
        document.getElementById("current-time").textContent = `${hours}:${minutes}:${seconds}`;
    }, 1000);
    
    // Create status breakdown chart
    const ctx = document.createElement("canvas");
    document.getElementById("chart-container").appendChild(ctx);
    
    new Chart(ctx, {
        type: "doughnut",
        data: {
            labels: ["Pending", "Confirmed", "Completed", "Cancelled"],
            datasets: [{
                data: [
                    ' . $status_breakdown['pending'] . ',
                    ' . $status_breakdown['confirmed'] . ',
                    ' . $status_breakdown['completed'] . ',
                    ' . $status_breakdown['cancelled'] . '
                ],
                backgroundColor: ["#ffc107", "#0d6efd", "#0dcaf0", "#dc3545"],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "bottom"
                }
            }
        }
    });
});
</script>';

// Include footer
include 'includes/footer.php';
?>