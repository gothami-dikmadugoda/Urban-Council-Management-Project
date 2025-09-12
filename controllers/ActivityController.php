<?php
require_once __DIR__ . '/../config/database.php';

class ActivityController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    public function getUserActivities($userId) {
        try {
            // Get payments
            $paymentQuery = "SELECT 
                'payment' as type,
                CONCAT('Payment - ', payment_type) as title,
                CONCAT('Rs. ', amount, ' - ', payment_status) as description,
                created_at
                FROM payments 
                WHERE user_id = :user_id";

            // Get complaints
            $complaintQuery = "SELECT 
                'complaint' as type,
                CONCAT('Complaint - ', complaint_type) as title,
                CONCAT(subject, ' - ', status) as description,
                created_at
                FROM complaints 
                WHERE user_id = :user_id";

            // Get bookings
            $bookingQuery = "SELECT 
                'booking' as type,
                'Venue Booking' as title,
                CONCAT(venue_name, ' - ', booking_status) as description,
                created_at
                FROM bookings 
                WHERE user_id = :user_id";

            // Combine all activities
            $query = "($paymentQuery) UNION ALL ($complaintQuery) UNION ALL ($bookingQuery) 
                     ORDER BY created_at DESC LIMIT 20";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $userId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user activities: " . $e->getMessage());
            return [];
        }
    }
} 