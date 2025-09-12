<?php
require_once dirname(__DIR__) . '/config/paths.php';
require_once dirname(__DIR__) . '/config/database.php';

class MessageController {
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

    public function getUnreadMessageCount($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM messages 
                WHERE recipient_id = ? 
                AND is_read = 0
            ");
            
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] ?? 0;
        } catch(PDOException $e) {
            error_log("Error getting unread message count: " . $e->getMessage());
            return 0;
        }
    }

    public function markMessageAsRead($messageId, $userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE messages 
                SET is_read = 1 
                WHERE id = ? AND recipient_id = ?
            ");
            
            return $stmt->execute([$messageId, $userId]);
        } catch(PDOException $e) {
            error_log("Error marking message as read: " . $e->getMessage());
            return false;
        }
    }
} 