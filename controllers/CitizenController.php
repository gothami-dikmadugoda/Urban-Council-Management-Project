<?php
require_once __DIR__ . '/../config/database.php';

class CitizenController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getCitizenInfo($userId) {
        try {
            $query = "SELECT u.*, 
                     (SELECT COUNT(*) FROM notifications n WHERE n.user_id = u.id AND n.is_read = 0) as unread_notifications 
                     FROM users u 
                     WHERE u.id = ? AND u.role = 'citizen'";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if the user has completed their profile
            if ($result) {
                $result['profile_completed'] = !empty($result['first_name']) && 
                                            !empty($result['last_name']) && 
                                            !empty($result['email']) && 
                                            !empty($result['phone']) && 
                                            !empty($result['address']);
                
                // Set the name field by combining first_name and last_name
                $result['name'] = trim($result['first_name'] . ' ' . $result['last_name']);
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting citizen info: " . $e->getMessage());
            return null;
        }
    }

    public function getRecentComplaints($userId, $limit = 3) {
        try {
            $query = "SELECT * FROM complaints WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting recent complaints: " . $e->getMessage());
            return [];
        }
    }

    public function getUpcomingCollections($area, $limit = 3) {
        try {
            $query = "SELECT * FROM garbage_schedules 
                     WHERE area = ? AND schedule_date >= CURDATE() 
                     ORDER BY schedule_date ASC, schedule_time ASC 
                     LIMIT ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$area, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting upcoming collections: " . $e->getMessage());
            return [];
        }
    }
} 