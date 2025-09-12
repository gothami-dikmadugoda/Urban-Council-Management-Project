<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/models/Payment.php';
require_once __DIR__ . '/../config/database.php';

// Check if vendor autoload exists
$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
$hasVendorLibraries = file_exists($vendorAutoload);

if ($hasVendorLibraries) {
    require_once $vendorAutoload;
}

class PaymentController {
    private $payment;
    private $conn;
    private $hasVendorLibraries;
    private $db;

    public function __construct() {
        $this->payment = new Payment();
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->hasVendorLibraries = false; // We're not using vendor libraries anymore

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'make_payment':
                    $this->handleMakePayment();
                    break;
            }
        }
    }

    private function handleMakePayment() {
        try {
            // Check if user is authenticated
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('User not authenticated');
            }

            // Add user_id to POST data
            $_POST['user_id'] = $_SESSION['user_id'];

            // Process the payment
            $result = $this->payment->processPayment($_POST, $_FILES['bank_slip']);

            if ($result['status'] === 'success') {
                $_SESSION['message'] = 'Payment submitted successfully! Receipt ID: ' . $result['receipt_id'];
                $_SESSION['message_type'] = 'success';
                header('Location: /A-11/views/citizen/CitizenDashboard.php');
                exit();
            } else {
                throw new Exception($result['message']);
            }
        } catch (Exception $e) {
            $_SESSION['message'] = 'Error: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
            header('Location: /A-11/views/citizen/make-payment.php');
            exit();
        }
    }

    public function getPayments($filter = 'all', $startDate = '', $endDate = '', $status = '') {
        $query = "SELECT p.*, u.first_name, u.last_name, 
                        v.first_name as verifier_first_name, v.last_name as verifier_last_name
                 FROM payments p 
                 LEFT JOIN users u ON p.user_id = u.id 
                 LEFT JOIN users v ON p.verified_by = v.id
                 WHERE 1=1";
        
        $params = array();

        if ($startDate) {
            $query .= " AND DATE(p.created_at) >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $query .= " AND DATE(p.created_at) <= ?";
            $params[] = $endDate;
        }
        
        if ($status) {
            $query .= " AND p.payment_status = ?";
            $params[] = $status;
        }

        $query .= " ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalPayments() {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM payments");
        return $stmt->fetchColumn();
    }

    public function getSuccessfulPayments() {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'completed'");
        return $stmt->fetchColumn();
    }

    public function getPendingPayments() {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'pending'");
        return $stmt->fetchColumn();
    }

    public function getTotalRevenue() {
        $stmt = $this->conn->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'completed'");
        return $stmt->fetchColumn() ?: 0;
    }

    public function getPaymentDetails($paymentId) {
        $stmt = $this->conn->prepare("
            SELECT p.*, 
                   u.first_name, u.last_name, u.email,
                   v.first_name as verifier_first_name, v.last_name as verifier_last_name
            FROM payments p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN users v ON p.verified_by = v.id
            WHERE p.payment_id = ?
        ");
        $stmt->execute([$paymentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function exportToCSV($payments) {
        // Create CSV content
        $output = fopen('php://temp', 'w');
        
        // Add headers
        fputcsv($output, [
            'Payment ID',
            'User',
            'Amount',
            'Payment Type',
            'Payment Method',
            'Status',
            'Bank Name',
            'Reference Number',
            'Deposit Date',
            'Verified By',
            'Verification Date',
            'Created Date'
        ]);
        
        // Add data
        foreach ($payments as $payment) {
            fputcsv($output, [
                $payment['payment_id'],
                $payment['first_name'] . ' ' . $payment['last_name'],
                $payment['amount'],
                ucfirst($payment['payment_type']),
                ucfirst(str_replace('_', ' ', $payment['payment_method'])),
                ucfirst($payment['payment_status']),
                $payment['bank_name'],
                $payment['reference_number'],
                $payment['deposit_date'],
                $payment['verifier_first_name'] ? $payment['verifier_first_name'] . ' ' . $payment['verifier_last_name'] : 'Not Verified',
                $payment['verification_date'],
                $payment['created_at']
            ]);
        }
        
        // Get CSV content
        rewind($output);
        $csv_content = stream_get_contents($output);
        fclose($output);
        
        // Create file
        $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = __DIR__ . '/../public/reports/' . $filename;
        file_put_contents($filepath, $csv_content);
        
        return $filename;
    }

    public function exportToPrintableHTML($payments) {
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Payment Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        .header { margin-bottom: 20px; }
        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-failed { background-color: #f8d7da; color: #721c24; }
        .status-refunded { background-color: #cce5ff; color: #004085; }
        .status-under_review { background-color: #e2e3e5; color: #383d41; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Payment Report</h2>
        <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
    </div>
    <button onclick="window.print()" class="no-print">Print Report</button>
    <table>
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>User</th>
                <th>Amount</th>
                <th>Type</th>
                <th>Method</th>
                <th>Status</th>
                <th>Bank Details</th>
                <th>Verification</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($payments as $payment) {
            $html .= '<tr>
                <td>' . $payment['payment_id'] . '</td>
                <td>' . htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) . '</td>
                <td>Rs. ' . number_format($payment['amount'], 2) . '</td>
                <td>' . ucfirst($payment['payment_type']) . '</td>
                <td>' . ucfirst(str_replace('_', ' ', $payment['payment_method'])) . '</td>
                <td><span class="status-badge status-' . $payment['payment_status'] . '">' 
                    . ucfirst($payment['payment_status']) . '</span></td>
                <td>' . ($payment['payment_method'] === 'bank_transfer' 
                    ? htmlspecialchars($payment['bank_name']) . '<br>Ref: ' . htmlspecialchars($payment['reference_number'])
                    : '-') . '</td>
                <td>' . ($payment['verified_by']
                    ? 'Verified by: ' . htmlspecialchars($payment['verifier_first_name'] . ' ' . $payment['verifier_last_name']) 
                        . '<br>' . date('Y-m-d', strtotime($payment['verification_date']))
                    : 'Not verified') . '</td>
            </tr>';
        }

        $html .= '</tbody></table></body></html>';

        $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.html';
        $filepath = __DIR__ . '/../public/reports/' . $filename;
        file_put_contents($filepath, $html);

        return $filename;
    }

    public function generateJasperReport($payments) {
        // Create a simple XML report format
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<paymentReport>
    <metadata>
        <title>Payment Report</title>
        <generatedDate>' . date('Y-m-d H:i:s') . '</generatedDate>
    </metadata>
    <payments>';

        foreach ($payments as $payment) {
            $xml .= '
        <payment>
            <paymentId>' . $payment['payment_id'] . '</paymentId>
            <userName>' . htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) . '</userName>
            <amount>' . $payment['amount'] . '</amount>
            <type>' . $payment['payment_type'] . '</type>
            <method>' . $payment['payment_method'] . '</method>
            <status>' . $payment['payment_status'] . '</status>
            <bankDetails>' . ($payment['payment_method'] === 'bank_transfer' ? $payment['bank_name'] . ' - ' . $payment['reference_number'] : '') . '</bankDetails>
            <verificationDetails>' . ($payment['verified_by'] ? 'Verified by ' . $payment['verifier_first_name'] . ' ' . $payment['verifier_last_name'] . ' on ' . $payment['verification_date'] : 'Not verified') . '</verificationDetails>
            <createdDate>' . $payment['created_at'] . '</createdDate>
        </payment>';
        }

        $xml .= '
    </payments>
</paymentReport>';

        $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.xml';
        $filepath = __DIR__ . '/../public/reports/' . $filename;
        file_put_contents($filepath, $xml);

        return $filename;
    }

    public function getWeeklyPayments($startDate, $endDate) {
        try {
            $query = "SELECT 
                        DATE(created_at) as date,
                        SUM(amount) as amount,
                        payment_type,
                        payment_status,
                        COUNT(*) as count
                     FROM payments 
                     WHERE DATE(created_at) BETWEEN :start_date AND :end_date
                     GROUP BY DATE(created_at), payment_type, payment_status
                     ORDER BY date ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching weekly payments: " . $e->getMessage());
            return [];
        }
    }

    public function getPaymentTypeDistribution($startDate, $endDate) {
        try {
            $query = "SELECT 
                        payment_type,
                        COUNT(*) as count,
                        SUM(amount) as total_amount
                     FROM payments 
                     WHERE DATE(created_at) BETWEEN :start_date AND :end_date
                     GROUP BY payment_type";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching payment type distribution: " . $e->getMessage());
            return [];
        }
    }

    public function getPaymentStatusDistribution($startDate, $endDate) {
        try {
            $query = "SELECT 
                        payment_status,
                        COUNT(*) as count,
                        SUM(amount) as total_amount
                     FROM payments 
                     WHERE DATE(created_at) BETWEEN :start_date AND :end_date
                     GROUP BY payment_status";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching payment status distribution: " . $e->getMessage());
            return [];
        }
    }

    public function getFilteredPayments($startDate = null, $endDate = null, $status = null, $paymentMethod = null, $paymentType = null) {
        try {
            $query = "SELECT 
                        p.*,
                        CONCAT(u.first_name, ' ', u.last_name) as user_name,
                        CONCAT(v.first_name, ' ', v.last_name) as verifier_name
                    FROM payments p
                    LEFT JOIN users u ON p.user_id = u.id
                    LEFT JOIN users v ON p.verified_by = v.id
                    WHERE 1=1";
            
            $params = [];

            if ($startDate) {
                $query .= " AND DATE(p.created_at) >= ?";
                $params[] = $startDate;
            }

            if ($endDate) {
                $query .= " AND DATE(p.created_at) <= ?";
                $params[] = $endDate;
            }

            if ($status) {
                $query .= " AND p.payment_status = ?";
                $params[] = $status;
            }

            if ($paymentMethod) {
                $query .= " AND p.payment_method = ?";
                $params[] = $paymentMethod;
            }

            if ($paymentType) {
                $query .= " AND p.payment_type = ?";
                $params[] = $paymentType;
            }

            $query .= " ORDER BY p.created_at DESC";

            error_log("SQL Query: " . $query);
            error_log("Parameters: " . print_r($params, true));

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting filtered payments: " . $e->getMessage());
            return [];
        }
    }

    public function createPayment($data) {
        try {
            // First, get an IT staff member ID
            $staffQuery = "SELECT id FROM users WHERE role = 'staff' AND department = 'it' AND job_role = 'it_staff' AND status = 'active' LIMIT 1";
            $stmt = $this->db->getConnection()->prepare($staffQuery);
            $stmt->execute();
            $staffId = $stmt->fetchColumn();
            
            if (!$staffId) {
                throw new PDOException("No IT staff member found to assign the payment to.");
            }

            $query = "INSERT INTO payments (
                user_id, amount, payment_type, payment_method, 
                bank_name, branch, reference_number, deposit_date, 
                description, payment_status, bank_slip_image, bank_slip_upload_date,
                assigned_to, created_at, updated_at
            ) VALUES (
                :user_id, :amount, :payment_type, 'bank_transfer',
                :bank_name, :branch, :reference_number, :deposit_date,
                :description, 'pending', :bank_slip_image, CURRENT_TIMESTAMP,
                :assigned_to, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            )";

            $stmt = $this->db->getConnection()->prepare($query);
            
            // Read the bank slip file content
            $bank_slip_content = null;
            if (isset($data['slip_file']) && file_exists('../../uploads/bank_slips/' . $data['slip_file'])) {
                $bank_slip_content = file_get_contents('../../uploads/bank_slips/' . $data['slip_file']);
            }
            
            $stmt->execute([
                ':user_id' => $data['user_id'],
                ':amount' => $data['amount'],
                ':payment_type' => $data['payment_purpose'],
                ':bank_name' => $data['bank_name'],
                ':branch' => $data['branch_name'],
                ':reference_number' => $data['slip_number'],
                ':deposit_date' => $data['payment_date'],
                ':description' => $data['notes'],
                ':bank_slip_image' => $bank_slip_content,
                ':assigned_to' => $staffId
            ]);

            $payment_id = $this->db->getConnection()->lastInsertId();

            // Create notification for IT staff
            $notificationQuery = "INSERT INTO notifications (
                user_id, title, message, type, reference_id, created_at, is_read
            ) VALUES (
                :user_id,
                'New Payment Submission',
                :message,
                'payment',
                :reference_id,
                CURRENT_TIMESTAMP,
                0
            )";

            $notifStmt = $this->db->getConnection()->prepare($notificationQuery);
            $notifStmt->execute([
                ':user_id' => $staffId,
                ':message' => "A new payment of Rs. {$data['amount']} has been submitted for {$data['payment_purpose']}. Reference: {$data['slip_number']}",
                ':reference_id' => $payment_id
            ]);

            // Delete the temporary file after storing in database
            if (isset($data['slip_file'])) {
                @unlink('../../uploads/bank_slips/' . $data['slip_file']);
            }

            return [
                'success' => true,
                'message' => 'Payment submitted successfully! An IT staff member will review your payment.',
                'payment_id' => $payment_id
            ];
        } catch (PDOException $e) {
            error_log("Error creating payment: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create payment: ' . $e->getMessage()
            ];
        }
    }

    public function getUserPayments($userId) {
        try {
            $query = "SELECT * FROM payments WHERE user_id = :user_id ORDER BY created_at DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user payments: " . $e->getMessage());
            return [];
        }
    }

    public function updatePaymentStatus($paymentId, $status, $staffId = null) {
        try {
            $query = "UPDATE payments SET 
                        payment_status = :status, 
                        updated_at = CURRENT_TIMESTAMP,
                        verified_by = :staff_id,
                        verification_date = CURRENT_TIMESTAMP
                     WHERE payment_id = :payment_id";

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([
                ':status' => $status,
                ':staff_id' => $staffId,
                ':payment_id' => $paymentId
            ]);

            return [
                'success' => true,
                'message' => 'Payment status updated successfully'
            ];
        } catch (PDOException $e) {
            error_log("Error updating payment status: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update payment status'
            ];
        }
    }

    public function getPaymentById($paymentId) {
        try {
            $query = "SELECT p.*, 
                             CONCAT(u.first_name, ' ', u.last_name) as user_name,
                             u.phone as user_phone,
                             u.email as user_email
                      FROM payments p
                      JOIN users u ON p.user_id = u.id
                      WHERE p.payment_id = :payment_id";

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([':payment_id' => $paymentId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching payment: " . $e->getMessage());
            return null;
        }
    }

    public function getAllPayments($status = null) {
        try {
            $query = "SELECT p.*, 
                             CONCAT(u.first_name, ' ', u.last_name) as user_name
                      FROM payments p
                      JOIN users u ON p.user_id = u.id";
            
            $params = [];
            if ($status) {
                $query .= " WHERE p.status = :status";
                $params[':status'] = $status;
            }
            
            $query .= " ORDER BY p.created_at DESC";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching all payments: " . $e->getMessage());
            return [];
        }
    }

    public function addPaymentNote($paymentId, $staffId, $note) {
        try {
            $query = "INSERT INTO payment_notes (
                payment_id, staff_id, note, created_at
            ) VALUES (
                :payment_id, :staff_id, :note, CURRENT_TIMESTAMP
            )";

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([
                ':payment_id' => $paymentId,
                ':staff_id' => $staffId,
                ':note' => $note
            ]);

            return [
                'success' => true,
                'message' => 'Note added successfully'
            ];
        } catch (PDOException $e) {
            error_log("Error adding payment note: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to add note'
            ];
        }
    }

    public function getPaymentNotes($paymentId) {
        try {
            $query = "SELECT pn.*, 
                             CONCAT(u.first_name, ' ', u.last_name) as staff_name
                      FROM payment_notes pn
                      JOIN users u ON pn.staff_id = u.id
                      WHERE pn.payment_id = :payment_id
                      ORDER BY pn.created_at DESC";

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([':payment_id' => $paymentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching payment notes: " . $e->getMessage());
            return [];
        }
    }

    // New method to add staff reply to payment
    public function addPaymentReply($paymentId, $staffId, $message) {
        try {
            $query = "INSERT INTO payment_replies (
                payment_id, staff_id, message, created_at
            ) VALUES (
                :payment_id, :staff_id, :message, CURRENT_TIMESTAMP
            )";

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([
                ':payment_id' => $paymentId,
                ':staff_id' => $staffId,
                ':message' => $message
            ]);

            // Create notification for the payment user
            $userQuery = "SELECT p.user_id, p.payment_type, p.amount 
                         FROM payments p 
                         WHERE p.payment_id = :payment_id";
            $userStmt = $this->db->getConnection()->prepare($userQuery);
            $userStmt->execute([':payment_id' => $paymentId]);
            $paymentInfo = $userStmt->fetch(PDO::FETCH_ASSOC);

            if ($paymentInfo) {
                $notificationQuery = "INSERT INTO notifications (
                    user_id, title, message, type, reference_id, created_at
                ) VALUES (
                    :user_id,
                    'Payment Update',
                    :message,
                    'payment_reply',
                    :reference_id,
                    CURRENT_TIMESTAMP
                )";

                $notifStmt = $this->db->getConnection()->prepare($notificationQuery);
                $notifStmt->execute([
                    ':user_id' => $paymentInfo['user_id'],
                    ':message' => "Your payment of Rs. {$paymentInfo['amount']} has received a new update from staff.",
                    ':reference_id' => $paymentId
                ]);
            }

            return [
                'success' => true,
                'message' => 'Reply added successfully'
            ];
        } catch (PDOException $e) {
            error_log("Error adding payment reply: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to add reply'
            ];
        }
    }

    // New method to get payment replies
    public function getPaymentReplies($paymentId) {
        try {
            $query = "SELECT pr.*, 
                             CONCAT(u.first_name, ' ', u.last_name) as staff_name,
                             u.department, u.job_role
                      FROM payment_replies pr
                      JOIN users u ON pr.staff_id = u.id
                      WHERE pr.payment_id = :payment_id
                      ORDER BY pr.created_at DESC";

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([':payment_id' => $paymentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching payment replies: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentPayments($limit = 5) {
        try {
            $sql = "SELECT p.*, u.name as user_name 
                    FROM payments p 
                    JOIN users u ON p.user_id = u.id 
                    ORDER BY p.created_at DESC 
                    LIMIT ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $payments = [];
            while ($row = $result->fetch_assoc()) {
                $payments[] = $row;
            }
            
            return $payments;
        } catch (Exception $e) {
            error_log("Error getting recent payments: " . $e->getMessage());
            return [];
        }
    }

    public function getPaymentsByUserId($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM payments WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new PaymentController();
} 