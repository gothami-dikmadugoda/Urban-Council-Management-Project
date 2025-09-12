<?php
// Include necessary files
require_once 'config/database.php';
require_once 'includes/functions.php';

// Set page title
$page_title = 'Appointments';

// Get all appointments
$sql = "SELECT a.*, v.name as visitor_name 
        FROM appointments a
        JOIN visitors v ON a.visitor_id = v.visitor_id
        ORDER BY a.appointment_date DESC";
$appointments = $conn->query($sql);

// Get all visitors for the dropdown
$sql = "SELECT * FROM visitors ORDER BY name ASC";
$visitors = $conn->query($sql);

// Check if in edit mode
$edit_mode = false;
$appointment = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = $conn->real_escape_string($_GET['edit']);
    $sql = "SELECT * FROM appointments WHERE appointment_id = '$edit_id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
        $edit_mode = true;
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <?php echo $edit_mode ? 'Edit Appointment' : 'Add New Appointment'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form action="processes/process_appointment.php" method="post" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="<?php echo $edit_mode ? 'edit' : 'add'; ?>">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="visitor_id" class="form-label">Select Visitor</label>
                            <select class="form-select" id="visitor_id" name="visitor_id" required>
                                <option value="">Choose a visitor...</option>
                                <?php if ($visitors->num_rows > 0): ?>
                                    <?php while($visitor = $visitors->fetch_assoc()): ?>
                                        <option value="<?php echo $visitor['visitor_id']; ?>" <?php echo ($edit_mode && $appointment['visitor_id'] == $visitor['visitor_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($visitor['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback">
                                Please select a visitor.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="appointment_date" class="form-label">Appointment Date & Time</label>
                            <input type="datetime-local" class="form-control" id="appointment_date" name="appointment_date" 
                                   value="<?php echo $edit_mode ? date('Y-m-d\TH:i', strtotime($appointment['appointment_date'])) : ''; ?>" required>
                            <div class="invalid-feedback">
                                Please provide a valid date and time.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="purpose" class="form-label">Purpose</label>
                            <textarea class="form-control" id="purpose" name="purpose" rows="3"><?php echo $edit_mode ? htmlspecialchars($appointment['purpose']) : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" <?php echo ($edit_mode && $appointment['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo ($edit_mode && $appointment['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo ($edit_mode && $appointment['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="completed" <?php echo ($edit_mode && $appointment['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a status.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo $edit_mode ? htmlspecialchars($appointment['notes']) : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="duration" class="form-label">Duration (hours)</label>
                            <input type="number" step="0.5" class="form-control" id="duration" name="duration" 
                                   value="<?php echo $edit_mode ? $appointment['duration'] : '1.0'; ?>" min="0.5" max="24" required>
                            <div class="invalid-feedback">
                                Please provide a valid duration between 0.5 and 24 hours.
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $edit_mode ? 'Update Appointment' : 'Schedule Appointment'; ?>
                            </button>
                            <?php if ($edit_mode): ?>
                                <a href="appointments.php" class="btn btn-outline-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8 mb-4">
            <div class="card border-0 shadow">
                <div class="card-header bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-secondary status-filter active" data-status="all" data-target="appointments-table">All</button>
                            <button type="button" class="btn btn-sm btn-outline-warning status-filter" data-status="pending" data-target="appointments-table">Pending</button>
                            <button type="button" class="btn btn-sm btn-outline-primary status-filter" data-status="confirmed" data-target="appointments-table">Confirmed</button>
                            <button type="button" class="btn btn-sm btn-outline-danger status-filter" data-status="cancelled" data-target="appointments-table">Cancelled</button>
                            <button type="button" class="btn btn-sm btn-outline-success status-filter" data-status="completed" data-target="appointments-table">Completed</button>
                        </div>
                        <div class="input-group w-50">
                            <span class="input-group-text" id="basic-addon1">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control table-search" data-target="appointments-table" placeholder="Search appointments...">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="appointments-table">
                            <thead>
                                <tr>
                                    <th>Visitor</th>
                                    <th>Date & Time</th>
                                    <th>Purpose</th>
                                    <th>Status</th>
                                    <th>Duration</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
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
                                                <span class="badge status-badge <?php echo get_status_badge($row['status']); ?>">
                                                    <?php echo htmlspecialchars($row['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $row['duration']; ?> hours</td>
                                            <td><?php echo htmlspecialchars($row['notes'] ?: '-'); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="appointments.php?edit=<?php echo $row['appointment_id']; ?>" class="btn btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $row['appointment_id']; ?>">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal<?php echo $row['appointment_id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Confirm Deletion</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete this appointment with <strong><?php echo htmlspecialchars($row['visitor_name']); ?></strong>?
                                                                This action cannot be undone.
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form action="processes/process_appointment.php" method="post">
                                                                    <input type="hidden" name="action" value="delete">
                                                                    <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No appointments found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>