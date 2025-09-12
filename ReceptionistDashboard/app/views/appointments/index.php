<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="container">
    <h1>Appointments</h1>
    
    <!-- Add/Edit Appointment Form -->
    <div class="card mb-4">
        <div class="card-header">
            <?php echo $edit_mode ? 'Edit Appointment' : 'Add New Appointment'; ?>
        </div>
        <div class="card-body">
            <form action="/appointments/process" method="POST">
                <input type="hidden" name="action" value="<?php echo $edit_mode ? 'edit' : 'add'; ?>">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="visitor_id">Visitor</label>
                            <select class="form-control" id="visitor_id" name="visitor_id" required>
                                <option value="">Select Visitor</option>
                                <?php foreach ($visitors as $visitor): ?>
                                    <option value="<?php echo $visitor['id']; ?>" 
                                        <?php echo ($edit_mode && $appointment['visitor_id'] == $visitor['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($visitor['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="appointment_date">Date</label>
                            <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                                value="<?php echo $edit_mode ? $appointment['appointment_date'] : ''; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="appointment_time">Time</label>
                            <input type="time" class="form-control" id="appointment_time" name="appointment_time" 
                                value="<?php echo $edit_mode ? $appointment['appointment_time'] : ''; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="duration">Duration (minutes)</label>
                            <input type="number" class="form-control" id="duration" name="duration" 
                                value="<?php echo $edit_mode ? $appointment['duration'] : '30'; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="purpose">Purpose</label>
                    <input type="text" class="form-control" id="purpose" name="purpose" 
                        value="<?php echo $edit_mode ? $appointment['purpose'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="scheduled" <?php echo ($edit_mode && $appointment['status'] == 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="completed" <?php echo ($edit_mode && $appointment['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo ($edit_mode && $appointment['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo $edit_mode ? $appointment['notes'] : ''; ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update' : 'Add'; ?> Appointment</button>
                <?php if ($edit_mode): ?>
                    <a href="/appointments" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Appointments Table -->
    <div class="card">
        <div class="card-header">
            All Appointments
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Visitor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['visitor_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                <td><?php echo htmlspecialchars($appointment['purpose']); ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $appointment['status'] == 'scheduled' ? 'primary' : 
                                            ($appointment['status'] == 'completed' ? 'success' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/appointments?edit=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" 
                                        data-target="#deleteModal<?php echo $appointment['id']; ?>">
                                        Delete
                                    </button>
                                    
                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal<?php echo $appointment['id']; ?>" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirm Delete</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete this appointment?
                                                </div>
                                                <div class="modal-footer">
                                                    <form action="/appointments/process" method="POST">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 