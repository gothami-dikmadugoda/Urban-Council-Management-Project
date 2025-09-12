<?php
// Include necessary files
require_once 'config/database.php';
require_once 'includes/functions.php';

// Set page title
$page_title = 'Visitors';

// Get all visitors
$sql = "SELECT * FROM visitors ORDER BY created_at DESC";
$visitors = $conn->query($sql);

// Check if in edit mode
$edit_mode = false;
$visitor = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = $conn->real_escape_string($_GET['edit']);
    $sql = "SELECT * FROM visitors WHERE visitor_id = '$edit_id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $visitor = $result->fetch_assoc();
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
                        <?php echo $edit_mode ? 'Edit Visitor' : 'Add New Visitor'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form action="processes/process_visitor.php" method="post" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="<?php echo $edit_mode ? 'edit' : 'add'; ?>">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="visitor_id" value="<?php echo $visitor['visitor_id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $edit_mode ? htmlspecialchars($visitor['name']) : ''; ?>" required>
                            <div class="invalid-feedback">
                                Please provide a name.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $edit_mode ? htmlspecialchars($visitor['email']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $edit_mode ? htmlspecialchars($visitor['phone']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="id_card_number" class="form-label">ID Card Number</label>
                            <input type="text" class="form-control" id="id_card_number" name="id_card_number" value="<?php echo $edit_mode ? htmlspecialchars($visitor['id_card_number']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="id_verified" name="id_verified" value="1" <?php echo ($edit_mode && $visitor['id_verified']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="id_verified">ID Verified</label>
                        </div>
                        
                        <div class="mb-3">
                            <label for="purpose" class="form-label">Purpose</label>
                            <textarea class="form-control" id="purpose" name="purpose" rows="3"><?php echo $edit_mode ? htmlspecialchars($visitor['purpose']) : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="checkin_time" class="form-label">Check-in Time</label>
                            <input type="datetime-local" class="form-control" id="checkin_time" name="checkin_time" value="<?php echo $edit_mode ? date('Y-m-d\TH:i', strtotime($visitor['checkin_time'])) : date('Y-m-d\TH:i'); ?>" required>
                            <div class="invalid-feedback">
                                Please provide a check-in time.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="checkout_time" class="form-label">Check-out Time</label>
                            <input type="datetime-local" class="form-control" id="checkout_time" name="checkout_time" value="<?php echo ($edit_mode && $visitor['checkout_time']) ? date('Y-m-d\TH:i', strtotime($visitor['checkout_time'])) : ''; ?>">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $edit_mode ? 'Update Visitor' : 'Register Visitor'; ?>
                            </button>
                            <?php if ($edit_mode): ?>
                                <a href="visitors.php" class="btn btn-outline-secondary">Cancel</a>
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
                        <h5 class="mb-0">Visitor List</h5>
                        <div class="input-group w-50">
                            <span class="input-group-text" id="basic-addon1">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control table-search" data-target="visitors-table" placeholder="Search visitors...">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="visitors-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>ID Number</th>
                                    <th>ID Verified</th>
                                    <th>Purpose</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($visitors->num_rows > 0): ?>
                                    <?php while($row = $visitors->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email'] ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($row['phone'] ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($row['id_card_number'] ?: '-'); ?></td>
                                            <td>
                                                <?php if ($row['id_verified']): ?>
                                                    <span class="badge bg-success">Verified</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Not Verified</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['purpose'] ?: '-'); ?></td>
                                            <td><?php echo format_date($row['checkin_time']); ?></td>
                                            <td><?php echo $row['checkout_time'] ? format_date($row['checkout_time']) : '-'; ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="visitors.php?edit=<?php echo $row['visitor_id']; ?>" class="btn btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $row['visitor_id']; ?>">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal<?php echo $row['visitor_id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Confirm Deletion</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete visitor <strong><?php echo htmlspecialchars($row['name']); ?></strong>?
                                                                This action cannot be undone.
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form action="processes/process_visitor.php" method="post">
                                                                    <input type="hidden" name="action" value="delete">
                                                                    <input type="hidden" name="visitor_id" value="<?php echo $row['visitor_id']; ?>">
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
                                        <td colspan="9" class="text-center">No visitors found</td>
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