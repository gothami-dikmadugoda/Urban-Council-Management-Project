<?php
require_once dirname(__DIR__) . '/config/database.php';

class Payment {
    private $db;

    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
        } catch(Exception $e) {
            error_log("Connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    public function processPayment($data, $file) {
        try {
            // Validate required fields
            $this->validatePaymentData($data, $file);

            // Get IT staff for assignment
            $assigned_to = $this->getITStaff();
            if (!$assigned_to) {
                throw new Exception("No IT staff available to process the payment");
            }

            $stmt = $this->db->prepare("
                INSERT INTO payments (
                    user_id, amount, payment_type, payment_method, payment_status,
                    bank_name, branch, deposit_date, reference_number,
                    bank_slip_image, bank_slip_upload_date, description,
                    assigned_to, created_at
                ) VALUES (
                    :user_id, :amount, :payment_type, :payment_method, 'pending',
                    :bank_name, :branch, :deposit_date, :reference_number,
                    :bank_slip_image, NOW(), :description,
                    :assigned_to, NOW()
                )
            ");

            // Process bank slip image
            $bank_slip_image = null;
            if (isset($file['tmp_name']) && !empty($file['tmp_name'])) {
                $bank_slip_image = file_get_contents($file['tmp_name']);
            }

            $params = [
                'user_id' => $data['user_id'],
                'amount' => $data['amount'],
                'payment_type' => $data['payment_type'],
                'payment_method' => $data['payment_method'],
                'bank_name' => $data['bank_name'] ?? null,
                'branch' => $data['branch'] ?? null,
                'deposit_date' => $data['deposit_date'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'bank_slip_image' => $bank_slip_image,
                'description' => $data['description'],
                'assigned_to' => $assigned_to
            ];

            $result = $stmt->execute($params);

            if ($result) {
                $receipt_id = $this->db->lastInsertId();
                $this->createITStaffNotification($receipt_id);
                return [
                    'status' => 'success',
                    'receipt_id' => $receipt_id
                ];
            }
            return [
                'status' => 'error',
                'message' => 'Failed to insert payment'
            ];
        } catch (Exception $e) {
            error_log("Error in processPayment: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function validatePaymentData($data, $file) {
        // Required fields validation
        $required_fields = ['amount', 'payment_type', 'payment_method', 'description'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("$field is required");
            }
        }

        // Amount validation
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new Exception("Amount must be a positive number");
        }

        // Payment type validation
        $valid_types = ['tax', 'service_charge', 'fine', 'other'];
        if (!in_array($data['payment_type'], $valid_types)) {
            throw new Exception("Invalid payment type");
        }

        // Payment method validation
        $valid_methods = ['bank_transfer', 'cash', 'online_payment'];
        if (!in_array($data['payment_method'], $valid_methods)) {
            throw new Exception("Invalid payment method");
        }

        // Bank transfer specific validation
        if ($data['payment_method'] === 'bank_transfer') {
            $bank_fields = ['bank_name', 'branch', 'deposit_date', 'reference_number'];
            foreach ($bank_fields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("$field is required for bank transfer");
                }
            }
        }

        // Bank slip validation
        if (empty($file['tmp_name'])) {
            throw new Exception("Bank slip is required");
        }

        // File type validation
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception("Invalid file type. Only JPG, PNG, and PDF are allowed");
        }

        // File size validation (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception("File size must be less than 5MB");
        }
    }

    private function createITStaffNotification($receipt_id) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (
                    user_id, title, message, type, category, status, action_url
                ) VALUES (
                    :user_id, 'New Payment Received', :message, 'info', 'payment', 'unread', :action_url
                )
            ");

            $it_staff_id = $this->getITStaff();
            if ($it_staff_id) {
                $stmt->execute([
                    'user_id' => $it_staff_id,
                    'message' => "A new payment has been received. Receipt ID: {$receipt_id}.",
                    'action_url' => '/views/staff/view-payment.php?id=' . $receipt_id
                ]);
            }
        } catch (PDOException $e) {
            error_log("Error creating IT staff notification: " . $e->getMessage());
        }
    }

    private function getITStaff() {
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM users 
                WHERE role = 'staff' 
                AND job_role = 'it_staff' 
                AND status = 'active' 
                LIMIT 1
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['id'] : null;
        } catch (PDOException $e) {
            error_log("Error getting IT staff: " . $e->getMessage());
            return null;
        }
    }

    public function getRecentPayments($user_id, $limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT *
                FROM payments
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error fetching recent payments: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalPayments($user_id) {
        try {
            // Get current month's count
            $currentStmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM payments
                WHERE user_id = :user_id
                AND status = 'completed'
                AND MONTH(created_at) = MONTH(CURRENT_DATE())
                AND YEAR(created_at) = YEAR(CURRENT_DATE())
            ");
            $currentStmt->execute(['user_id' => $user_id]);
            $currentCount = $currentStmt->fetch(PDO::FETCH_ASSOC);

            // Get last month's count
            $lastStmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM payments
                WHERE user_id = :user_id
                AND status = 'completed'
                AND MONTH(created_at) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
                AND YEAR(created_at) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
            ");
            $lastStmt->execute(['user_id' => $user_id]);
            $lastCount = $lastStmt->fetch(PDO::FETCH_ASSOC);

            // Calculate trend percentage
            $trend = 0;
            if ($lastCount['count'] > 0) {
                $trend = (($currentCount['count'] - $lastCount['count']) / $lastCount['count']) * 100;
            }

            return [
                'count' => $currentCount['count'],
                'trend' => round($trend, 1)
            ];
        } catch(PDOException $e) {
            error_log("Error fetching total payments: " . $e->getMessage());
            return ['count' => 0, 'trend' => 0];
        }
    }

    public function getPaymentTrend($user_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM payments
                WHERE user_id = :user_id
                AND status = 'completed'
                AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
                GROUP BY YEAR(created_at), MONTH(created_at)
                ORDER BY YEAR(created_at), MONTH(created_at)
                LIMIT 6
            ");
            $stmt->execute(['user_id' => $user_id]);
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'count');
        } catch(PDOException $e) {
            error_log("Error fetching payment trend: " . $e->getMessage());
            return [0, 0, 0, 0, 0, 0];
        }
    }
} 