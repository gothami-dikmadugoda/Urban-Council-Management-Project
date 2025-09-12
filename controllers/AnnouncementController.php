<?php
// Remove duplicate session_start() since it's already started in the parent file
// session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Announcement.php';

class AnnouncementController {
    private $conn;
    private $announcement;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->announcement = new Announcement();
    }

    public function createAnnouncement($title, $content, $posted_by, $expiry_datetime = null) {
        try {
            // Validate input
            if (empty($title) || empty($content)) {
                return ['success' => false, 'message' => 'Title and content are required'];
            }

            // Create announcement
            $result = $this->announcement->create($title, $content, $posted_by, $expiry_datetime);
            
            if ($result) {
                return ['success' => true, 'message' => 'Announcement created successfully', 'id' => $result];
            }
                return ['success' => false, 'message' => 'Failed to create announcement'];
        } catch (Exception $e) {
            error_log("Error in createAnnouncement: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while creating the announcement'];
        }
    }

    public function updateAnnouncement($id, $title, $content, $expiry_datetime = null) {
        try {
            // Validate input
            if (empty($title) || empty($content)) {
                return ['success' => false, 'message' => 'Title and content are required'];
            }

            // Update announcement
            $result = $this->announcement->update($id, $title, $content, $expiry_datetime);
            
            if ($result) {
                return ['success' => true, 'message' => 'Announcement updated successfully'];
            }
                return ['success' => false, 'message' => 'Failed to update announcement'];
        } catch (Exception $e) {
            error_log("Error in updateAnnouncement: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating the announcement'];
        }
    }

    public function deleteAnnouncement($id) {
        try {
            $result = $this->announcement->delete($id);
            
            if ($result) {
                return ['success' => true, 'message' => 'Announcement deleted successfully'];
            }
                return ['success' => false, 'message' => 'Failed to delete announcement'];
        } catch (Exception $e) {
            error_log("Error in deleteAnnouncement: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deleting the announcement'];
        }
    }

    public function getAllAnnouncements() {
        try {
            $query = "SELECT a.*, u.first_name, u.last_name, 
                     CASE 
                         WHEN a.expiry_datetime IS NULL THEN 'No expiry'
                         ELSE DATE_FORMAT(a.expiry_datetime, '%Y-%m-%d %H:%i:%s')
                     END as formatted_expiry
                     FROM announcements a 
                     LEFT JOIN users u ON a.posted_by = u.id 
                     ORDER BY a.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching announcements: " . $e->getMessage());
            return [];
        }
    }

    public function getActiveAnnouncements() {
        try {
            $announcements = $this->announcement->getActiveAnnouncements();
            return ['success' => true, 'data' => $announcements];
        } catch (Exception $e) {
            error_log("Error in getActiveAnnouncements: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while fetching announcements'];
        }
    }

    public function getAnnouncement($id) {
        try {
            $announcement = $this->announcement->getById($id);
            if ($announcement) {
                return ['success' => true, 'data' => $announcement];
            }
            return ['success' => false, 'message' => 'Announcement not found'];
        } catch (Exception $e) {
            error_log("Error in getAnnouncement: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while fetching the announcement'];
        }
    }

    public function getWeeklyAnnouncements($startDate, $endDate) {
        try {
            $query = "SELECT 
                        a.*,
                        u.first_name,
                        u.last_name
                     FROM announcements a
                     LEFT JOIN users u ON a.posted_by = u.id
                     WHERE DATE(a.created_at) BETWEEN :start_date AND :end_date
                     ORDER BY a.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching weekly announcements: " . $e->getMessage());
            return [];
        }
    }

    public function getUserAnnouncements($user_id) {
        try {
            $announcements = $this->announcement->getByUser($user_id);
            return ['success' => true, 'data' => $announcements];
        } catch (Exception $e) {
            error_log("Error in getUserAnnouncements: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while fetching user announcements'];
        }
    }
} 