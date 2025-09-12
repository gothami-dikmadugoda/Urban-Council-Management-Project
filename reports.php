<?php
// Include necessary files
require_once 'config/database.php';
require_once 'includes/functions.php';

// Set page title
$page_title = 'Reports';

// Get filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Get current time
$now = date('Y-m-d H:i:s');

// Query appointments with filters
$sql = "SELECT a.*, v.name as visitor_name 
        FROM appointments a
        JOIN visitors v ON a.visitor_id = v.visitor_id
        WHERE 1=1";

if ($start_date) {
    $sql .= " AND DATE(a.appointment_date) >= '$start_date'";
}
if ($end_date) {
    $sql .= " AND DATE(a.appointment_date) <= '$end_date'";
}
if ($status_filter && $status_filter != 'all') {
    $sql .= " AND a.status = '$status_filter'";
}

$sql .= " ORDER BY a.appointment_date DESC";
$appointments = $conn->query($sql);

// Initialize statistics variables
$status_counts = [
    'pending' => 0,
    'confirmed' => 0,
    'cancelled' => 0,
    'completed' => 0
];
$total_duration = 0;
$total_appointments = 0;
$avg_duration = 0;

// Calculate statistics if there are appointments
if ($appointments->num_rows > 0) {
    $appointments->data_seek(0); // Reset pointer
    
    while($row = $appointments->fetch_assoc()) {
        $status_counts[$row['status']]++;
        $total_duration += $row['duration'];
    }
    
    $total_appointments = array_sum($status_counts);
    $avg_duration = $total_appointments > 0 ? $total_duration / $total_appointments : 0;
}

// Include header
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2 text-primary"></i>
                            Appointment Reports
                        </h5>
                        <button type="button" class="btn btn-outline-secondary btn-print">
                            <i class="fas fa-print me-2"></i>Print
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="get" action="reports.php" class="row mb-4">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Statuses</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        </div>
                    </form>
                    
                    <!-- Report Summary -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h5>Report Summary</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Period:</strong> <?php echo date('M d, Y', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Total Appointments:</strong> <?php echo $appointments->num_rows; ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Generated:</strong> <?php echo date('M d, Y H:i', strtotime($now)); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Report Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Visitor</th>
                                    <th>Date & Time</th>
                                    <th>Purpose</th>
                                    <th>Status</th>
                                    <th>Duration</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($appointments->num_rows > 0): ?>
                                    <?php while($row = $appointments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['visitor_name']); ?></td>
                                            <td><?php echo format_date($row['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['purpose'] ?: '-'); ?></td>
                                            <td>
                                                <span class="badge <?php echo get_status_badge($row['status']); ?>">
                                                    <?php echo htmlspecialchars($row['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $row['duration']; ?> hours</td>
                                            <td><?php echo htmlspecialchars($row['notes'] ?: '-'); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No appointments found for the selected period</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Statistics Section -->
                    <?php if ($appointments->num_rows > 0): ?>
                        <div class="row mt-5">
                            <div class="col-md-12">
                                <h5 class="mb-4">Appointment Statistics</h5>
                            </div>
                            
                            <div class="col-md-6">
                                <canvas id="statusChart" width="400" height="300"></canvas>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">Key Metrics</h6>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Total Appointments
                                                <span class="badge bg-primary rounded-pill"><?php echo $total_appointments; ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Pending Appointments
                                                <span class="badge bg-warning rounded-pill"><?php echo $status_counts['pending']; ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Confirmed Appointments
                                                <span class="badge bg-primary rounded-pill"><?php echo $status_counts['confirmed']; ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Completed Appointments
                                                <span class="badge bg-info rounded-pill"><?php echo $status_counts['completed']; ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Cancelled Appointments
                                                <span class="badge bg-danger rounded-pill"><?php echo $status_counts['cancelled']; ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Average Appointment Duration
                                                <span class="badge bg-success rounded-pill"><?php echo number_format($avg_duration, 1); ?> hours</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Additional JavaScript for reports page
$extra_js = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Create status chart
    const statusCtx = document.getElementById("statusChart");
    if (statusCtx) {
        new Chart(statusCtx, {
            type: "pie",
            data: {
                labels: ["Pending", "Confirmed", "Completed", "Cancelled"],
                datasets: [{
                    data: [
                        ' . $status_counts['pending'] . ',
                        ' . $status_counts['confirmed'] . ',
                        ' . $status_counts['completed'] . ',
                        ' . $status_counts['cancelled'] . '
                    ],
                    backgroundColor: ["#ffc107", "#0d6efd", "#0dcaf0", "#dc3545"],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: "bottom"
                    },
                    title: {
                        display: true,
                        text: "Appointment Status Distribution"
                    }
                }
            }
        });
    }
    
    // Initialize date range validation
    const startDateInput = document.getElementById("start_date");
    const endDateInput = document.getElementById("end_date");
    
    if (startDateInput && endDateInput) {
        endDateInput.addEventListener("change", function() {
            if (startDateInput.value && this.value && new Date(this.value) < new Date(startDateInput.value)) {
                alert("End date cannot be earlier than start date");
                this.value = startDateInput.value;
            }
        });
        
        startDateInput.addEventListener("change", function() {
            if (endDateInput.value && this.value && new Date(this.value) > new Date(endDateInput.value)) {
                endDateInput.value = this.value;
            }
        });
    }
});
</script>';

// Include footer
include 'includes/footer.php';
?>
