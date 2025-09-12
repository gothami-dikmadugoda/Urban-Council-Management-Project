<?php
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../config/database.php';

class NotificationController {
    private $db;
    private $notification;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->notification = new Notification($this->db);
    }

    public function createNotification($data) {
        return $this->notification->create($data);
    }

    public function getUnreadNotifications($userId) {
        return $this->notification->getUnreadByUserId($userId);
    }

    public function markAsRead($notificationId) {
        return $this->notification->markAsRead($notificationId);
    }

    public function getUnreadCount($userId) {
        return $this->notification->getUnreadCount($userId);
    }

    public function getNotificationsByUserId($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting notifications by user ID: " . $e->getMessage());
            return [];
        }
    }

    public function getNotificationsByReference($referenceId, $type) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notifications 
                WHERE reference_id = ? AND type = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$referenceId, $type]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting notifications by reference: " . $e->getMessage());
            return [];
        }
    }

    public function createComplaintNotification($complaintId, $staffId, $title) {
        $data = [
            'user_id' => $staffId,
            'title' => 'New Complaint Assigned',
            'message' => "You have been assigned a new complaint: $title",
            'type' => 'complaint',
            'reference_id' => $complaintId
        ];
        
        // Add debug logging
        error_log("Creating notification with data: " . print_r($data, true));
        
        $result = $this->createNotification($data);
        
        // Add debug logging for result
        error_log("Notification creation result: " . ($result ? "success" : "failed"));
        
        return $result;
    }

    public function createStatusUpdateNotification($complaintId, $staffId, $title, $status) {
        $data = [
            'user_id' => $staffId,
            'title' => 'Complaint Status Updated',
            'message' => "Complaint '$title' status has been updated to: $status",
            'type' => 'status_update',
            'reference_id' => $complaintId
        ];
        return $this->createNotification($data);
    }

    public function createPriorityNotification($complaintId, $title, $priority) {
        // Get admin user ID from database
        $query = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin) {
            error_log("No admin user found for priority notification");
            return false;
        }
        
        $data = [
            'user_id' => $admin['id'],
            'title' => 'Priority Complaint Alert',
            'message' => "A new $priority priority complaint has been submitted: $title",
            'type' => 'priority_alert',
            'reference_id' => $complaintId
        ];
        
        error_log("Creating priority notification with data: " . print_r($data, true));
        return $this->createNotification($data);
    }

    public function createNoteNotification($complaintId, $userId, $title, $message) {
        $data = [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => 'complaint_note',
            'reference_id' => $complaintId
        ];
        return $this->createNotification($data);
    }

    public function getNotificationsByType($userId, $types) {
        try {
            $placeholders = str_repeat('?,', count($types) - 1) . '?';
            $query = "SELECT * FROM notifications 
                     WHERE user_id = ? 
                     AND type IN ($placeholders)
                     AND is_read = 0
                     ORDER BY created_at DESC";
            
            $params = array_merge([$userId], $types);
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting notifications by type: " . $e->getMessage());
            return [];
        }
    }
}
?> 