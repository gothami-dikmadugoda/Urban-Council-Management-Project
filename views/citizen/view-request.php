<?php
session_start();
require_once '../../controllers/CitizenController.php';
require_once '../../controllers/CollectionController.php';
require_once '../../controllers/NotificationController.php';

// Check if user is logged in and is a citizen
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to continue.";
    header('Location: /urban2/login.php');
    exit();
}

$citizenController = new CitizenController();
$collectionController = new CollectionController();
$notificationController = new NotificationController();

// Get request ID from URL
$requestId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$requestId) {
    $_SESSION['error'] = 'Invalid request ID';
    header('Location: /urban2/views/citizen/requests.php');
    exit();
}

// Get collection request details
$requests = $collectionController->getCollectionRequests($_SESSION['user_id']);
$request = null;
foreach ($requests as $req) {
    if ($req['id'] == $requestId) {
        $request = $req;
        break;
    }
}

if (!$request) {
    $_SESSION['error'] = 'Collection request not found';
    header('Location: /urban2/views/citizen/requests.php');
    exit();
}

// Get collection notes
$notes = $collectionController->getCollectionNotes($requestId);

// Get notifications for this request
$notifications = $notificationController->getNotificationsByReference($requestId, 'collection_request');

// Mark notifications as read when viewing the request
if (!empty($notifications)) {
    foreach ($notifications as $notification) {
        if (!$notification['is_read']) {
            $notificationController->markAsRead($notification['id']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Collection Request - Urban Council</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .status-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
        .timeline {
            border-left: 3px solid #dee2e6;
            padding-left: 20px;
            margin-left: 10px;
        }
        .timeline-item {
            margin-bottom: 20px;
            position: relative;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            left: -26px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #0d6efd;
        }
        .status-pending { background-color: #ffd700; }
        .status-approved { background-color: #90EE90; }
        .status-rejected { background-color: #ffcccb; }
        .status-completed { background-color: #87CEEB; }
        .notification-item {
            border-left: 3px solid #007bff;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <a href="/urban2/views/citizen/requests.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Requests
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success" role="alert">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Collection Request Details</h4>
                <span class="badge rounded-pill <?php
                    switch($request['status']) {
                        case 'pending': echo 'bg-warning'; break;
                        case 'approved': echo 'bg-success'; break;
                        case 'rejected': echo 'bg-danger'; break;
                        case 'completed': echo 'bg-info'; break;
                    }
                ?> status-badge">
                    <?php echo ucfirst($request['status']); ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Area:</strong> <?php echo htmlspecialchars($request['area']); ?></p>
                        <p><strong>Collection Date:</strong> <?php echo date('F j, Y', strtotime($request['collection_date'])); ?></p>
                        <p><strong>Collection Time:</strong> <?php echo date('g:i A', strtotime($request['collection_time'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Waste Type:</strong> <?php echo ucfirst(htmlspecialchars($request['waste_type'])); ?></p>
                        <p><strong>Waste Volume:</strong> <?php echo ucfirst(htmlspecialchars($request['waste_volume'])); ?></p>
                        <p><strong>Special Instructions:</strong> <?php echo $request['special_instructions'] ? htmlspecialchars($request['special_instructions']) : 'None'; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Communication History</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php if (empty($notifications)): ?>
                        <p>No communication history available</p>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <div class="timeline-item">
                                <p class="mb-1">
                                    <strong><?php echo htmlspecialchars($notification['title']); ?></strong>
                                    <small class="text-muted">
                                        (<?php echo date('F j, Y g:i A', strtotime($notification['created_at'])); ?>)
                                    </small>
                                </p>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($notification['message'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 