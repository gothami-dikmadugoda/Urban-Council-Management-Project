<?php
require_once dirname(__DIR__) . '/config/database.php';

class Notification {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO notifications (
                user_id, title, message, type, 
                reference_id, is_read, created_at
            ) VALUES (?, ?, ?, ?, ?, 0, NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['user_id'],
                $data['title'],
                $data['message'],
                $data['type'],
                $data['reference_id'] ?? null
            ]);

            if ($result) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error creating notification: " . $e->getMessage());
            return false;
        }
    }

    public function getByUserId($userId, $limit = 10) {
        try {
            $sql = "SELECT * FROM notifications 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching notifications: " . $e->getMessage());
            return [];
        }
    }

    public function markAsRead($notificationId) {
        try {
            $sql = "UPDATE notifications 
                    SET is_read = 1 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$notificationId]);
        } catch (PDOException $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }

    public function getUnreadCount($userId) {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM notifications 
                    WHERE user_id = ? AND is_read = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Error getting unread notification count: " . $e->getMessage());
            return 0;
        }
    }

    public function getUnreadByUserId($userId) {
        try {
            $sql = "SELECT * FROM notifications 
                    WHERE user_id = ? 
                    AND is_read = 0 
                    ORDER BY created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching unread notifications: " . $e->getMessage());
            return [];
        }
    }
}
?> 