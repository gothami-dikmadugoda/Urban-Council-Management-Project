<?php
session_start();
require_once '../../controllers/PaymentController.php';
require_once '../../controllers/NotificationController.php';

// Check if user is logged in and is IT staff
if (!isset($_SESSION['user_id']) || 
    !isset($_SESSION['department']) || 
    !isset($_SESSION['job_role']) || 
    strtolower($_SESSION['department']) !== 'it' || 
    strtolower($_SESSION['job_role']) !== 'it_staff') {
    header('Location: /urban2/login.php');
    exit();
}

$paymentController = new PaymentController();
$notificationController = new NotificationController();

// Get payment ID from URL
$paymentId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$paymentId) {
    $_SESSION['error'] = 'Invalid payment ID';
    header('Location: /urban2/views/admin/staff_dashboard.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $result = $paymentController->updatePaymentStatus($paymentId, $_POST['status'], $_SESSION['user_id']);
        if ($result['success']) {
            // Create notification for the user
            $payment = $paymentController->getPaymentById($paymentId);
            if ($payment) {
                $notificationData = [
                    'user_id' => $payment['user_id'],
                    'title' => 'Payment Status Update',
                    'message' => "Your payment status has been updated to: " . ucfirst($_POST['status']),
                    'type' => 'payment_update',
                    'reference_id' => $paymentId
                ];
                if ($notificationController->createNotification($notificationData)) {
                    $_SESSION['success'] = 'Status updated successfully';
                } else {
                    $_SESSION['error'] = 'Failed to create notification';
                }
            } else {
                $_SESSION['error'] = 'Failed to get payment details';
            }
        } else {
            $_SESSION['error'] = $result['message'];
        }
    } elseif (isset($_POST['add_reply'])) {
        $message = trim($_POST['reply_message']);
        if (!empty($message)) {
            $result = $paymentController->addPaymentReply($paymentId, $_SESSION['user_id'], $message);
            if ($result['success']) {
                $_SESSION['success'] = 'Reply sent successfully';
            } else {
                $_SESSION['error'] = $result['message'];
            }
        } else {
            $_SESSION['error'] = 'Reply message cannot be empty';
        }
    }
    header("Location: /urban2/views/staff/payment_details.php?id=" . $paymentId);
    exit();
}

// Get payment details
$payment = $paymentController->getPaymentById($paymentId);
if (!$payment) {
    $_SESSION['error'] = 'Payment not found';
    header('Location: /urban2/views/admin/staff_dashboard.php');
    exit();
}

// Get payment replies
$replies = $paymentController->getPaymentReplies($paymentId);

// Mark notification as read if accessed through notification
if (isset($_GET['notification_id'])) {
    $notificationId = (int)$_GET['notification_id'];
    $notificationController->markAsRead($_SESSION['user_id'], 'payment', $notificationId);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Payment - Urban Council</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f1c40f;
            --accent-color: #9b59b6;
            --dark-blue: #23406e;
            --light-bg: #f8f9fa;
            --card-bg: #fff;
            --divider: #e0e0e0;
            --shadow-main: 0 8px 32px rgba(52, 152, 219, 0.10);
            --shadow-hover: 0 16px 40px rgba(52, 152, 219, 0.18);
        }

        body {
            background: linear-gradient(120deg, #e3e6f3 60%, var(--dark-blue) 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 60px;
        }

        .main-bg {
            background: rgba(255,255,255,0.96);
            border-radius: 28px;
            box-shadow: var(--shadow-main);
            padding: 2.5rem 1.5rem 2.5rem 1.5rem;
            margin-bottom: 2.5rem;
        }

        .page-header {
            background: linear-gradient(135deg, var(--dark-blue), var(--primary-color));
            color: white;
            padding: 2.5rem 0 1.5rem 0;
            margin-bottom: 2.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .page-header .subtitle {
            font-size: 1.1rem;
            color: #e0e0e0;
            margin-top: 0.5rem;
        }
        .page-header .divider {
            width: 60px;
            height: 4px;
            background: var(--accent-color);
            border-radius: 2px;
            margin: 1rem 0 0.5rem 0;
        }

        .section-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--dark-blue);
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 0.7rem;
        }
        .section-title i {
            font-size: 1.3rem;
            color: var(--dark-blue);
        }
        .section-divider {
            height: 3px;
            width: 60px;
            background: linear-gradient(90deg, var(--primary-color), var(--dark-blue));
            border-radius: 2px;
            margin-bottom: 1.2rem;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: var(--shadow-main);
            transition: transform 0.3s cubic-bezier(.4,2,.3,1), box-shadow 0.3s;
            margin-bottom: 2.2rem;
            background: var(--card-bg);
        }
        .card:hover {
            transform: translateY(-4px) scale(1.012);
            box-shadow: var(--shadow-hover);
        }
        .card-header.bg-white {
            background: #eaf0fa !important;
            border-bottom: 1px solid var(--divider);
            border-radius: 20px 20px 0 0;
            padding-top: 1.2rem;
            padding-bottom: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-header h4 {
            font-weight: 700;
            color: var(--dark-blue);
            margin-bottom: 0.2rem;
            font-size: 1.18rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .status-badge {
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-left: 1rem;
            font-size: 1rem;
            box-shadow: 0 0 8px 2px rgba(52,152,219,0.10);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .status-badge i {
            font-size: 1.1rem;
            filter: drop-shadow(0 0 2px #fff8);
        }
        .status-pending { background-color: var(--warning-color); color: #000; }
        .status-completed { background-color: var(--secondary-color); color: white; }
        .status-failed { background-color: var(--danger-color); color: white; }
        .status-refunded { background-color: var(--primary-color); color: white; }
        .status-under_review { background-color: var(--accent-color); color: white; }

        .bank-slip-box {
            background: #f7faff;
            border: 2px dashed var(--primary-color);
            border-radius: 14px;
            padding: 1.2rem;
            margin-top: 1.2rem;
            text-align: center;
        }
        .bank-slip-image {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.10);
            margin-top: 0.5rem;
            margin-bottom: 0.7rem;
        }
        .download-slip-btn {
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1.3rem;
            font-weight: 600;
            margin-top: 0.5rem;
            transition: background 0.2s, transform 0.2s;
        }
        .download-slip-btn:hover {
            background: linear-gradient(90deg, var(--accent-color), var(--primary-color));
            transform: scale(1.04);
        }

        .timeline {
            border-left: 3px solid var(--accent-color);
            margin-left: 1.2rem;
            padding-left: 1.5rem;
            position: relative;
        }
        .timeline .reply-dot {
            position: absolute;
            left: -1.7rem;
            width: 18px;
            height: 18px;
            background: var(--accent-color);
            border-radius: 50%;
            top: 1.2rem;
            box-shadow: 0 2px 8px rgba(155,89,182,0.10);
            border: 3px solid #fff;
        }
        .reply-card.card {
            border-left: 0;
            margin-bottom: 22px;
            background: #fafdff;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.06);
            transition: transform 0.3s cubic-bezier(.4,2,.3,1), box-shadow 0.3s;
            position: relative;
        }
        .reply-card.card:hover {
            transform: translateX(8px) scale(1.01);
            box-shadow: 0 8px 24px rgba(52, 152, 219, 0.10);
        }
        .reply-card .card-body {
            padding-left: 2.2rem;
        }
        .reply-card .reply-dot {
            position: absolute;
            left: -1.7rem;
            top: 1.2rem;
        }
        .btn-primary, .btn-light {
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border: none;
            border-radius: 10px;
            font-weight: 600;
            transition: background 0.2s, transform 0.2s;
        }
        .btn-primary:hover, .btn-light:hover {
            background: linear-gradient(90deg, var(--accent-color), var(--primary-color));
            transform: scale(1.04);
        }
        .form-label {
            font-weight: 600;
            color: var(--primary-color);
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1.5px solid var(--divider);
            font-size: 1.05rem;
        }
        .alert {
            border-radius: 10px;
            font-size: 1.05rem;
        }
        @media (max-width: 768px) {
            .main-bg {
                padding: 1rem 0.5rem;
            }
            .page-header {
                padding: 1.5rem 0 1rem 0;
            }
            .card {
                padding: 0.5rem;
            }
            .reply-card .card-body {
                padding-left: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-auto">
                    <a href="/urban2/views/admin/staff_dashboard.php" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                <div class="col">
                    <h2 class="mb-0">Payment Details</h2>
                    <div class="subtitle">Review and manage payment information, status, and replies.</div>
                    <div class="divider"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="container main-bg">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Payment Details Card -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h4 class="mb-0 section-title"><i class="fas fa-file-invoice-dollar"></i> Payment Information</h4>
                <span class="status-badge status-<?php echo strtolower($payment['payment_status']); ?>">
                    <i class="
                        <?php
                        $status = strtolower($payment['payment_status']);
                        echo $status === 'pending' ? 'fas fa-hourglass-half' :
                             ($status === 'completed' ? 'fas fa-check-circle' :
                             ($status === 'failed' ? 'fas fa-times-circle' :
                             ($status === 'refunded' ? 'fas fa-undo' :
                             ($status === 'under_review' ? 'fas fa-search' : 'fas fa-info-circle'))));
                        ?>"></i>
                    <?php echo ucfirst($payment['payment_status']); ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Payment ID:</strong> <?php echo htmlspecialchars($payment['payment_id']); ?></p>
                        <p><strong>Amount:</strong> Rs. <?php echo number_format($payment['amount'], 2); ?></p>
                        <p><strong>Payment Type:</strong> <?php echo ucfirst(str_replace('_', ' ', $payment['payment_type'])); ?></p>
                        <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>User:</strong> <?php echo htmlspecialchars($payment['user_name']); ?></p>
                        <p><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($payment['created_at'])); ?></p>
                        <p><strong>Reference Number:</strong> <?php echo htmlspecialchars($payment['reference_number']); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($payment['description']); ?></p>
                    </div>
                </div>

                <?php if (isset($payment['bank_slip_image']) && !empty($payment['bank_slip_image'])): ?>
                <div class="bank-slip-box">
                    <h5 class="section-title"><i class="fas fa-university"></i> Bank Slip</h5>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($payment['bank_slip_image']); ?>" 
                         class="bank-slip-image" alt="Bank Slip">
                    <a href="data:image/jpeg;base64,<?php echo base64_encode($payment['bank_slip_image']); ?>" download="bank-slip-<?php echo $payment['payment_id']; ?>.jpg" class="download-slip-btn">
                        <i class="fas fa-download"></i> Download Slip
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Update Status Form -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h4 class="mb-0 section-title"><i class="fas fa-edit"></i> Update Status</h4>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Payment Status</label>
                        <select name="status" class="form-select">
                            <option value="pending" <?php echo $payment['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo $payment['payment_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="failed" <?php echo $payment['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            <option value="refunded" <?php echo $payment['payment_status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                            <option value="under_review" <?php echo $payment['payment_status'] === 'under_review' ? 'selected' : ''; ?>>Under Review</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Add Reply Form -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h4 class="mb-0 section-title"><i class="fas fa-reply"></i> Add Reply</h4>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="reply_message" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" name="add_reply" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Reply
                    </button>
                </form>
            </div>
        </div>

        <!-- Payment Replies -->
        <div class="card">
            <div class="card-header bg-white">
                <h4 class="mb-0 section-title"><i class="fas fa-comments"></i> Payment Replies</h4>
            </div>
            <div class="card-body">
                <?php if (empty($replies)): ?>
                    <p class="text-muted">No replies yet.</p>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($replies as $reply): ?>
                            <div class="reply-card card">
                                <span class="reply-dot"></span>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">
                                            <?php echo htmlspecialchars($reply['staff_name']); ?>
                                            <small class="text-muted ms-2"><?php echo ucfirst($reply['department']); ?> Department</small>
                                        </h6>
                                        <small class="text-muted">
                                            <?php echo date('F j, Y g:i A', strtotime($reply['created_at'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 