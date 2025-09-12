<?php
require_once __DIR__ . '/../config/database.php';

class DashboardController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getComplaintsAnalytics($userId) {
        try {
            // Get total complaints
            $totalQuery = "SELECT COUNT(*) as total FROM complaints WHERE user_id = :user_id";
            $stmt = $this->db->prepare($totalQuery);
            $stmt->execute([':user_id' => $userId]);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Get monthly trend
            $trendQuery = "SELECT 
                            DATE_FORMAT(created_at, '%Y-%m') as month,
                            COUNT(*) as count
                          FROM complaints 
                          WHERE user_id = :user_id
                          AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
                          GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                          ORDER BY month ASC";
            $stmt = $this->db->prepare($trendQuery);
            $stmt->execute([':user_id' => $userId]);
            $trend = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate percentage change
            $currentMonth = date('Y-m');
            $lastMonth = date('Y-m', strtotime('-1 month'));
            
            $currentCount = 0;
            $lastCount = 0;
            
            foreach ($trend as $data) {
                if ($data['month'] === $currentMonth) $currentCount = $data['count'];
                if ($data['month'] === $lastMonth) $lastCount = $data['count'];
            }
            
            $percentageChange = $lastCount > 0 ? (($currentCount - $lastCount) / $lastCount) * 100 : 0;

            return [
                'total' => $total,
                'trend' => $trend,
                'percentage_change' => round($percentageChange, 1)
            ];
        } catch (PDOException $e) {
            error_log("Error getting complaints analytics: " . $e->getMessage());
            return ['total' => 0, 'trend' => [], 'percentage_change' => 0];
        }
    }

    public function getResolvedComplaintsAnalytics($userId) {
        try {
            // Get resolved complaints
            $totalQuery = "SELECT COUNT(*) as total FROM complaints 
                          WHERE user_id = :user_id AND status = 'resolved'";
            $stmt = $this->db->prepare($totalQuery);
            $stmt->execute([':user_id' => $userId]);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Get monthly trend
            $trendQuery = "SELECT 
                            DATE_FORMAT(updated_at, '%Y-%m') as month,
                            COUNT(*) as count
                          FROM complaints 
                          WHERE user_id = :user_id AND status = 'resolved'
                          AND updated_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
                          GROUP BY DATE_FORMAT(updated_at, '%Y-%m')
                          ORDER BY month ASC";
            $stmt = $this->db->prepare($trendQuery);
            $stmt->execute([':user_id' => $userId]);
            $trend = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate resolution rate
            $allComplaintsQuery = "SELECT COUNT(*) as total FROM complaints WHERE user_id = :user_id";
            $stmt = $this->db->prepare($allComplaintsQuery);
            $stmt->execute([':user_id' => $userId]);
            $allComplaints = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            $resolutionRate = $allComplaints > 0 ? ($total / $allComplaints) * 100 : 0;

            return [
                'total' => $total,
                'trend' => $trend,
                'resolution_rate' => round($resolutionRate, 1)
            ];
        } catch (PDOException $e) {
            error_log("Error getting resolved complaints analytics: " . $e->getMessage());
            return ['total' => 0, 'trend' => [], 'resolution_rate' => 0];
        }
    }

    public function getBookingsAnalytics($userId) {
        try {
            // Get total bookings
            $totalQuery = "SELECT COUNT(*) as total FROM bookings WHERE user_id = :user_id";
            $stmt = $this->db->prepare($totalQuery);
            $stmt->execute([':user_id' => $userId]);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Get monthly trend
            $trendQuery = "SELECT 
                            DATE_FORMAT(created_at, '%Y-%m') as month,
                            COUNT(*) as count
                          FROM bookings 
                          WHERE user_id = :user_id
                          AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
                          GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                          ORDER BY month ASC";
            $stmt = $this->db->prepare($trendQuery);
            $stmt->execute([':user_id' => $userId]);
            $trend = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate percentage change
            $currentMonth = date('Y-m');
            $lastMonth = date('Y-m', strtotime('-1 month'));
            
            $currentCount = 0;
            $lastCount = 0;
            
            foreach ($trend as $data) {
                if ($data['month'] === $currentMonth) $currentCount = $data['count'];
                if ($data['month'] === $lastMonth) $lastCount = $data['count'];
            }
            
            $percentageChange = $lastCount > 0 ? (($currentCount - $lastCount) / $lastCount) * 100 : 0;

            return [
                'total' => $total,
                'trend' => $trend,
                'percentage_change' => round($percentageChange, 1)
            ];
        } catch (PDOException $e) {
            error_log("Error getting bookings analytics: " . $e->getMessage());
            return ['total' => 0, 'trend' => [], 'percentage_change' => 0];
        }
    }

    public function getPaymentsAnalytics($userId) {
        try {
            // Get total payments
            $totalQuery = "SELECT COUNT(*) as total FROM payments WHERE user_id = :user_id";
            $stmt = $this->db->prepare($totalQuery);
            $stmt->execute([':user_id' => $userId]);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Get monthly trend
            $trendQuery = "SELECT 
                            DATE_FORMAT(created_at, '%Y-%m') as month,
                            COUNT(*) as count
                          FROM payments 
                          WHERE user_id = :user_id
                          AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
                          GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                          ORDER BY month ASC";
            $stmt = $this->db->prepare($trendQuery);
            $stmt->execute([':user_id' => $userId]);
            $trend = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate percentage change
            $currentMonth = date('Y-m');
            $lastMonth = date('Y-m', strtotime('-1 month'));
            
            $currentCount = 0;
            $lastCount = 0;
            
            foreach ($trend as $data) {
                if ($data['month'] === $currentMonth) $currentCount = $data['count'];
                if ($data['month'] === $lastMonth) $lastCount = $data['count'];
            }
            
            $percentageChange = $lastCount > 0 ? (($currentCount - $lastCount) / $lastCount) * 100 : 0;

            return [
                'total' => $total,
                'trend' => $trend,
                'percentage_change' => round($percentageChange, 1)
            ];
        } catch (PDOException $e) {
            error_log("Error getting payments analytics: " . $e->getMessage());
            return ['total' => 0, 'trend' => [], 'percentage_change' => 0];
        }
    }
} 