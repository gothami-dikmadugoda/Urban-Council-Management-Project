<?php
session_start();
require_once '../../controllers/PaymentController.php';
require_once '../../controllers/NotificationController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /urban2/login.php');
    exit();
}

$paymentController = new PaymentController();
$notificationController = new NotificationController();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_payment'])) {
    $payment_data = [
        'user_id' => $_SESSION['user_id'],
        'amount' => $_POST['amount'],
        'payment_purpose' => $_POST['payment_type'],
        'bank_name' => $_POST['bank_name'],
        'branch_name' => $_POST['branch'],
        'slip_number' => $_POST['reference_number'],
        'payment_date' => $_POST['deposit_date'],
        'notes' => $_POST['description']
    ];

    // Handle file upload
    if (isset($_FILES['bank_slip']) && $_FILES['bank_slip']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $filename = $_FILES['bank_slip']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array(strtolower($filetype), $allowed)) {
            $temp_name = $_FILES['bank_slip']['tmp_name'];
            $new_filename = uniqid('slip_') . '.' . $filetype;
            $upload_path = '../../uploads/bank_slips/' . $new_filename;

            // Create directory if it doesn't exist
            if (!file_exists('../../uploads/bank_slips/')) {
                mkdir('../../uploads/bank_slips/', 0777, true);
            }

            if (move_uploaded_file($temp_name, $upload_path)) {
                $payment_data['slip_file'] = $new_filename;
                
                $result = $paymentController->createPayment($payment_data);
                
                if ($result['success']) {
                    $_SESSION['success'] = 'Payment submitted successfully! We will verify and update you soon.';
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            } else {
                $_SESSION['error'] = 'Failed to upload bank slip. Please try again.';
            }
        } else {
            $_SESSION['error'] = 'Invalid file type. Please upload JPG, JPEG, PNG or PDF files only.';
        }
    } else {
        $_SESSION['error'] = 'Please upload a bank slip.';
    }
    
    header('Location: /urban2/views/citizen/payments.php');
    exit();
}

// Get user's payment history
$payments = $paymentController->getUserPayments($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment - Urban Council</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --main-bg: #0d112b;
            --sidebar-bg: #181e3a;
            --card-bg: #16213e;
            --card-radius: 18px;
            --card-shadow: 0 6px 24px rgba(34,197,94,0.08), 0 1.5px 4px rgba(34,197,94,0.08);
            --input-bg: #22325c;
            --input-border: #22c55e;
            --accent: #22c55e;
            --btn-main: #22c55e;
            --btn-hover: #166534;
            --heading: #fff;
            --text: #e5e7eb;
        }
        body {
            background: var(--main-bg);
            font-family: 'Inter', 'Segoe UI', 'Roboto', Arial, sans-serif;
            color: var(--text);
        }
        .sidebar {
            background: var(--sidebar-bg);
            color: white;
        }
        .page-header, .card, .payment-item {
            background: var(--card-bg);
            color: var(--text);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
        }
        .page-header {
            color: var(--heading);
            padding: 2rem 0;
            margin-bottom: 2rem;
            border: none;
        }
        .card-header {
            background: var(--card-bg);
            border-bottom: 1px solid #22325c;
            color: var(--accent);
            padding: 1.5rem;
            border-radius: var(--card-radius) var(--card-radius) 0 0 !important;
        }
        .form-control, .form-select {
            border-radius: 12px;
            padding: 1rem 1.3rem;
            border: 1.5px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text);
            transition: border-color 0.18s, box-shadow 0.18s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(34,197,94,0.18);
            background: #16213e;
            color: #fff;
        }
        .btn-primary, .btn-primary:focus {
            background: var(--btn-main);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.08rem;
            letter-spacing: 0.1px;
            transition: background 0.18s, box-shadow 0.18s;
            color: #fff;
            box-shadow: 0 2px 8px rgba(34,197,94,0.10);
        }
        .btn-primary:hover {
            background: var(--btn-hover);
            color: #fff;
            box-shadow: 0 4px 16px rgba(34,197,94,0.13);
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8rem;
            background: #22325c;
            color: var(--accent);
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .file-upload-label {
            display: block;
            padding: 1rem;
            background: var(--input-bg);
            border: 2px dashed #22c55e;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--text);
        }
        .file-upload-label:hover {
            border-color: var(--accent);
            background: #22325c;
        }
        .payment-history {
            margin-top: 3rem;
        }
        .payment-history h4 {
            color: #22c55e;
            font-weight: 700;
            margin-bottom: 2rem;
            letter-spacing: 0.5px;
        }
        .payment-item {
            background: #22325c !important;
            color: #fff !important;
            border-left: 5px solid #22c55e !important;
            box-shadow: 0 4px 18px rgba(34,197,94,0.10), 0 1.5px 4px rgba(34,197,94,0.06) !important;
            border-radius: 16px !important;
            margin-bottom: 1.5rem;
            padding: 1.5rem 1.2rem 1.2rem 1.2rem !important;
            transition: box-shadow 0.18s, background 0.18s, transform 0.18s;
        }
        .payment-item:hover {
            background: #16213e !important;
            transform: translateX(8px) scale(1.01);
            box-shadow: 0 8px 36px rgba(34,197,94,0.13) !important;
        }
        .payment-item h6 {
            color: #22c55e !important;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .payment-item strong {
            color: #e5e7eb !important;
        }
        .payment-item .status-badge, .payment-item .badge {
            background: #16213e !important;
            color: #22c55e !important;
            font-weight: 600;
            border-radius: 8px;
            padding: 4px 14px;
            font-size: 0.98em;
        }
        .payment-item .text-muted, .payment-item small {
            color: #b5c2d6 !important;
        }
        h2, .page-header h2 {
            color: var(--heading);
            font-weight: 900;
            letter-spacing: 0.5px;
        }
        .alert {
            border-radius: 12px;
            font-size: 1.08rem;
            font-family: inherit;
            color: #fff;
            background: #22c55e;
            border: none;
            box-shadow: 0 2px 8px rgba(34,197,94,0.10);
        }
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            padding: 1rem;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #888 #2c3e50;
            z-index: 1000;
        }
        .sidebar::-webkit-scrollbar {
            width: 8px;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 3rem 2rem 3rem 2rem;
            background: #f8fafc;
            min-height: 100vh;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            margin: 0.2rem 0;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        .sidebar .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        .profile-section {
            text-align: center;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .profile-section img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 1rem;
            border: 3px solid rgba(255, 255, 255, 0.2);
        }
        .card, .payment-item, .page-header {
            border-radius: 20px !important;
            box-shadow: 0 6px 32px rgba(44,62,80,0.10), 0 2px 8px rgba(44,62,80,0.06) !important;
        }
        .card-header, .page-header {
            padding: 2rem 2rem 1.5rem 2rem !important;
        }
        .card-body {
            padding: 2rem 2rem 2rem 2rem !important;
        }
        .form-label {
            font-weight: 500;
            color: #3B3B98;
            font-size: 1.08rem;
            letter-spacing: 0.1px;
        }
        .form-control, .form-select {
            border-radius: 12px;
            border: 1.5px solid #e0e0e0;
            padding: 1rem 1.3rem;
            font-size: 1.08rem;
            background: #fff;
            transition: border-color 0.18s, box-shadow 0.18s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.10);
        }
        .btn-primary, .btn-primary:focus {
            background: #3498db;
            border: none;
            border-radius: 10px;
            font-weight: 500;
            font-size: 1.08rem;
            padding: 0.7rem 1.7rem;
            box-shadow: 0 2px 8px rgba(52,152,219,0.10);
            margin-top: 0.5rem;
            letter-spacing: 0.1px;
            transition: background 0.18s, box-shadow 0.18s;
        }
        .btn-primary:hover {
            background: #2980b9;
            color: #fff;
            box-shadow: 0 4px 16px rgba(41,128,185,0.13);
        }
        .payment-history {
            margin-top: 3rem;
        }
        .payment-item {
            background: #fff;
            padding: 1.5rem;
            border-radius: 20px;
            margin-bottom: 1.5rem;
            border-left: 6px solid #3498db;
            transition: box-shadow 0.18s, background 0.18s, transform 0.18s;
        }
        .payment-item:hover {
            transform: translateX(10px) scale(1.01);
            box-shadow: 0 8px 36px rgba(52,152,219,0.13);
            background: #f4f6fb;
        }
        .status-badge {
            padding: 0.5rem 1.1rem;
            border-radius: 2rem;
            font-weight: 400;
            font-size: 1.01rem;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            box-shadow: 0 1px 4px rgba(59,59,152,0.06);
        }
        @media (max-width: 900px) {
            .main-content {
                padding: 1.5rem 0.7rem 1.5rem 0.7rem;
            }
            .card-header, .page-header, .card-body, .payment-item {
                padding: 1.2rem 0.7rem 1.2rem 0.7rem !important;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="profile-section">
                <img src="<?php echo $_SESSION['profile_image'] ?? '/urban2/assets/images/default-avatar.png'; ?>" 
                     alt="Profile Image">
                <h5 class="text-white mb-1"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Citizen'); ?></h5>
                <p class="text-muted mb-0">Citizen</p>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/index.php">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/citizen/dashboard.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/citizen/profile.php">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/citizen/requests.php">
                        <i class="fas fa-clipboard-list"></i> Active Requests
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/urban2/views/citizen/notifications.php">
                        <i class="fas fa-bell"></i> Notifications
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="/urban2/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
        <!-- Main Content -->
        <div class="main-content">
    <div class="page-header">
            <div class="row align-items-center">
                <div class="col-auto">
                        <a href="/urban2/views/citizen/dashboard.php" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                <div class="col">
                    <h2 class="mb-0">Make Payment</h2>
                </div>
            </div>
        </div>
    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success fade-in" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger fade-in" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card fade-in">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Payment Details</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="amount" class="form-label">Amount (Rs.)</label>
                                    <input type="number" class="form-control" id="amount" name="amount" required 
                                           min="1" step="0.01" placeholder="Enter amount">
                                </div>
                                <div class="col-md-6">
                                    <label for="payment_type" class="form-label">Payment Type</label>
                                    <select class="form-select" id="payment_type" name="payment_type" required>
                                        <option value="">Select Type</option>
                                        <option value="tax">Tax</option>
                                        <option value="service_charge">Service Charge</option>
                                        <option value="fine">Fine</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="bank_name" class="form-label">Bank Name</label>
                                    <input type="text" class="form-control" id="bank_name" name="bank_name" required
                                           placeholder="Enter bank name">
                                </div>
                                <div class="col-md-6">
                                    <label for="branch" class="form-label">Branch Name</label>
                                    <input type="text" class="form-control" id="branch" name="branch" required
                                           placeholder="Enter branch name">
                                </div>
                                <div class="col-md-6">
                                    <label for="reference_number" class="form-label">Reference Number</label>
                                    <input type="text" class="form-control" id="reference_number" name="reference_number" required
                                           placeholder="Enter reference/slip number">
                                </div>
                                <div class="col-md-6">
                                    <label for="deposit_date" class="form-label">Deposit Date</label>
                                    <input type="date" class="form-control" id="deposit_date" name="deposit_date" required>
                                </div>
                                <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required
                                              placeholder="Enter payment description"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Bank Slip</label>
                                    <div class="file-upload">
                                        <input type="file" class="file-upload-input" id="bank_slip" name="bank_slip" 
                                               accept=".jpg,.jpeg,.png,.pdf" required>
                                        <label for="bank_slip" class="file-upload-label">
                                            <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                            Click to upload bank slip (JPG, JPEG, PNG, PDF)
                                        </label>
                                    </div>
                                    <small class="text-muted">Maximum file size: 5MB</small>
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="submit_payment" class="btn btn-primary w-100">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Payment
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Payment History -->
                <div class="payment-history fade-in">
                    <h4 class="mb-4"><i class="fas fa-history me-2"></i>Payment History</h4>
                    <?php if (empty($payments)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No payment history available</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <div class="payment-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-1">
                                        <?php echo ucfirst(str_replace('_', ' ', $payment['payment_type'])); ?>
                                    </h6>
                                    <span class="badge status-<?php echo $payment['payment_status']; ?>">
                                        <?php echo ucfirst($payment['payment_status']); ?>
                                    </span>
                                </div>
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('F j, Y', strtotime($payment['deposit_date'])); ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Amount:</strong> Rs. <?php echo number_format($payment['amount'], 2); ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Bank:</strong> <?php echo htmlspecialchars($payment['bank_name']); ?> 
                                    (<?php echo htmlspecialchars($payment['branch']); ?>)
                                </p>
                                <p class="mb-0">
                                    <strong>Reference:</strong> <?php echo htmlspecialchars($payment['reference_number']); ?>
                                </p>
                                <?php if ($payment['description']): ?>
                                    <p class="mb-0">
                                        <strong>Description:</strong> <?php echo htmlspecialchars($payment['description']); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if ($payment['verification_date']): ?>
                                    <p class="mb-0 mt-2 text-muted">
                                        <small>
                                            <i class="fas fa-check-circle"></i> 
                                            Verified by <?php echo htmlspecialchars($payment['verifier_name']); ?> 
                                            on <?php echo date('F j, Y g:i A', strtotime($payment['verification_date'])); ?>
                                        </small>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File upload preview
        document.getElementById('bank_slip').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'No file chosen';
            const label = document.querySelector('.file-upload-label');
            label.innerHTML = `<i class="fas fa-file-alt fa-2x mb-2"></i><br>${fileName}`;
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('bank_slip');
            const file = fileInput.files[0];
            
            if (file) {
                const fileSize = file.size / 1024 / 1024; // Convert to MB
                if (fileSize > 5) {
                    e.preventDefault();
                    alert('File size should not exceed 5MB');
                }
            }
        });
    </script>
</body>
</html> 