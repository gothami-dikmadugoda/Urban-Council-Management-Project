<?php
require_once __DIR__ . '/../config/database.php';

class SystemMessage {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function createSystemMessage($senderId, $message, $fileUrl = null, $fileType = null, $fileName = null) {
        try {
            // Validate input
            if (empty($senderId) || empty($message)) {
                throw new Exception("Sender ID and message are required");
            }

            $sql = "INSERT INTO system_messages (sender_id, message, file_url, file_type, file_name, created_at) 
                    VALUES (:sender_id, :message, :file_url, :file_type, :file_name, NOW())";
            
            $stmt = $this->db->prepare($sql);
            
            // Bind parameters
            $stmt->bindParam(':sender_id', $senderId, PDO::PARAM_INT);
            $stmt->bindParam(':message', $message, PDO::PARAM_STR);
            $stmt->bindParam(':file_url', $fileUrl, PDO::PARAM_STR);
            $stmt->bindParam(':file_type', $fileType, PDO::PARAM_STR);
            $stmt->bindParam(':file_name', $fileName, PDO::PARAM_STR);
            
            if (!$stmt->execute()) {
                $error = $stmt->errorInfo();
                throw new Exception("Database error: " . $error[2]);
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Database error in createSystemMessage: " . $e->getMessage());
            throw new Exception("Failed to create system message");
        } catch (Exception $e) {
            error_log("Error in createSystemMessage: " . $e->getMessage());
            throw $e;
        }
    }

    public function getSystemMessages($limit = 50) {
        try {
            $sql = "SELECT sm.*, u.first_name, u.last_name, u.role 
                    FROM system_messages sm 
                    JOIN users u ON sm.sender_id = u.id 
                    ORDER BY sm.created_at DESC 
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $error = $stmt->errorInfo();
                throw new Exception("Database error: " . $error[2]);
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getSystemMessages: " . $e->getMessage());
            throw new Exception("Failed to retrieve system messages");
        }
    }

    public function getUnreadSystemMessageCount($userId) {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM system_messages sm 
                    LEFT JOIN system_message_reads smr ON sm.id = smr.message_id AND smr.user_id = :user_id 
                    WHERE smr.id IS NULL";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $error = $stmt->errorInfo();
                throw new Exception("Database error: " . $error[2]);
            }
            
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (PDOException $e) {
            error_log("Database error in getUnreadSystemMessageCount: " . $e->getMessage());
            throw new Exception("Failed to get unread message count");
        }
    }

    public function markAsRead($messageId, $userId) {
        try {
            // Check if already marked as read
            $checkSql = "SELECT id FROM system_message_reads 
                        WHERE message_id = :message_id AND user_id = :user_id";
            
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->bindParam(':message_id', $messageId, PDO::PARAM_INT);
            $checkStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                return true; // Already marked as read
            }

            $sql = "INSERT INTO system_message_reads (message_id, user_id, read_at) 
                    VALUES (:message_id, :user_id, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':message_id', $messageId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $error = $stmt->errorInfo();
                throw new Exception("Database error: " . $error[2]);
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Database error in markAsRead: " . $e->getMessage());
            throw new Exception("Failed to mark message as read");
        }
    }
} 