<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if(!isset($_GET['id'])) {
    header('Location: appointments.php');
    exit;
}

$appointment_id = (int)$_GET['id'];
$sql = "SELECT a.*, v.name as visitor_name 
        FROM appointments a
        JOIN visitors v ON a.visitor_id = v.visitor_id
        WHERE a.appointment_id = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();

if(!$appointment) {
    header('Location: appointments.php');
    exit;
}

$page_title = 'Appointment Details';
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="card border-0 shadow">
        <div class="card-header bg-white">
            <h2 class="mb-0">Appointment Details</h2>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Visitor</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($appointment['visitor_name']) ?></dd>
                
                <dt class="col-sm-3">Date & Time</dt>
                <dd class="col-sm-9"><?= format_date($appointment['appointment_date']) ?></dd>
                
                <dt class="col-sm-3">Purpose</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($appointment['purpose']) ?></dd>
                
                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    <span class="badge <?= get_status_badge($appointment['status']) ?>">
                        <?= ucfirst($appointment['status']) ?>
                    </span>
                </dd>
            </dl>
            
            <a href="appointments.php" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i> Back to Appointments
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
