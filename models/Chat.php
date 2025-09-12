<?php
require_once __DIR__ . '/../config/database.php';

class Chat {
    private $conn;
    private $chat_messages_table = "chat_messages";
    private $chat_groups_table = "chat_groups";
    private $chat_group_members_table = "chat_group_members";
    private $users_table = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get individual chat messages between two users
    public function getIndividualMessages($sender_id, $receiver_id, $limit = 50) {
        $query = "SELECT m.*, 
                  CONCAT(u.first_name, ' ', u.last_name) as sender_name 
                  FROM " . $this->chat_messages_table . " m
                  JOIN " . $this->users_table . " u ON m.sender_id = u.id
                  WHERE (m.sender_id = :sender_id AND m.receiver_id = :receiver_id)
                  OR (m.sender_id = :receiver_id AND m.receiver_id = :sender_id)
                  ORDER BY m.created_at DESC LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":sender_id", $sender_id);
        $stmt->bindParam(":receiver_id", $receiver_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get group chat messages
    public function getGroupMessages($group_id, $limit = 50) {
        $query = "SELECT m.*, 
                  CONCAT(u.first_name, ' ', u.last_name) as sender_name 
                  FROM " . $this->chat_messages_table . " m
                  JOIN " . $this->users_table . " u ON m.sender_id = u.id
                  WHERE m.group_id = :group_id
                  ORDER BY m.created_at DESC LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":group_id", $group_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Send individual message
    public function sendIndividualMessage($sender_id, $receiver_id, $message) {
        $query = "INSERT INTO " . $this->chat_messages_table . " 
                  (sender_id, receiver_id, message, message_type) 
                  VALUES (:sender_id, :receiver_id, :message, 'individual')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":sender_id", $sender_id);
        $stmt->bindParam(":receiver_id", $receiver_id);
        $stmt->bindParam(":message", $message);

        return $stmt->execute();
    }

    // Send group message
    public function sendGroupMessage($sender_id, $group_id, $message) {
        $query = "INSERT INTO " . $this->chat_messages_table . " 
                  (sender_id, group_id, message, message_type) 
                  VALUES (:sender_id, :group_id, :message, 'group')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":sender_id", $sender_id);
        $stmt->bindParam(":group_id", $group_id);
        $stmt->bindParam(":message", $message);

        return $stmt->execute();
    }

    // Get user's groups
    public function getUserGroups($user_id) {
        $query = "SELECT DISTINCT g.* FROM " . $this->chat_groups_table . " g
                  JOIN " . $this->chat_group_members_table . " gm ON g.id = gm.group_id
                  WHERE gm.user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['success' => true, 'data' => $groups];
    }

    // Get group members
    public function getGroupMembers($group_id) {
        $query = "SELECT u.id, u.first_name, u.last_name, u.department 
                  FROM " . $this->users_table . " u
                  JOIN " . $this->chat_group_members_table . " gm ON u.id = gm.user_id
                  WHERE gm.group_id = :group_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":group_id", $group_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mark messages as read
    public function markMessagesAsRead($receiver_id, $sender_id) {
        $query = "UPDATE " . $this->chat_messages_table . "
                  SET is_read = 1
                  WHERE receiver_id = :receiver_id AND sender_id = :sender_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":receiver_id", $receiver_id);
        $stmt->bindParam(":sender_id", $sender_id);

        return $stmt->execute();
    }

    // Get unread message count
    public function getUnreadCount($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->chat_messages_table . "
                  WHERE receiver_id = :user_id AND is_read = 0";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    // Create department group
    public function createDepartmentGroup($name, $department) {
        $query = "INSERT INTO " . $this->chat_groups_table . " 
                  (name, department) VALUES (:name, :department)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":department", $department);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Add member to group
    public function addGroupMember($group_id, $user_id) {
        // Check if member already exists
        $check_query = "SELECT COUNT(*) as count FROM " . $this->chat_group_members_table . "
                       WHERE group_id = :group_id AND user_id = :user_id";
        
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(":group_id", $group_id);
        $check_stmt->bindParam(":user_id", $user_id);
        $check_stmt->execute();
        
        $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] > 0) {
            return true; // Member already exists
        }

        // Add new member
        $query = "INSERT INTO " . $this->chat_group_members_table . " 
                  (group_id, user_id) VALUES (:group_id, :user_id)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":group_id", $group_id);
        $stmt->bindParam(":user_id", $user_id);

        return $stmt->execute();
    }

    // Update user's online status
    public function updateUserStatus($user_id, $status) {
        $query = "UPDATE " . $this->users_table . " 
                  SET online_status = :status, last_activity = NOW() 
                  WHERE id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":user_id", $user_id);

        return $stmt->execute();
    }

    // Search messages
    public function searchMessages($user_id, $search_term, $type = 'all', $group_id = null) {
        $query = "SELECT m.*, 
                  CONCAT(u.first_name, ' ', u.last_name) as sender_name,
                  CASE 
                    WHEN m.message_type = 'individual' THEN 
                        (SELECT CONCAT(u2.first_name, ' ', u2.last_name) 
                         FROM " . $this->users_table . " u2 
                         WHERE u2.id = m.receiver_id)
                    ELSE g.name
                  END as chat_name
                  FROM " . $this->chat_messages_table . " m
                  JOIN " . $this->users_table . " u ON m.sender_id = u.id
                  LEFT JOIN " . $this->chat_groups_table . " g ON m.group_id = g.id
                  WHERE (m.message LIKE :search_term)";

        if ($type === 'individual') {
            $query .= " AND m.message_type = 'individual' 
                       AND (m.sender_id = :user_id OR m.receiver_id = :user_id)";
        } elseif ($type === 'group') {
            $query .= " AND m.message_type = 'group'";
            if ($group_id) {
                $query .= " AND m.group_id = :group_id";
            }
        } else {
            $query .= " AND (m.message_type = 'individual' AND (m.sender_id = :user_id OR m.receiver_id = :user_id)
                        OR m.message_type = 'group' AND m.group_id IN 
                        (SELECT group_id FROM " . $this->chat_group_members_table . " WHERE user_id = :user_id))";
        }

        $query .= " ORDER BY m.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $search_param = "%" . $search_term . "%";
        $stmt->bindParam(":search_term", $search_param);
        $stmt->bindParam(":user_id", $user_id);
        
        if ($type === 'group' && $group_id) {
            $stmt->bindParam(":group_id", $group_id);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Clear chat messages
    public function clearChat($user_id, $chat_type, $chat_id) {
        if ($chat_type === 'individual') {
            $query = "DELETE FROM " . $this->chat_messages_table . "
                      WHERE ((sender_id = :user_id AND receiver_id = :chat_id)
                      OR (sender_id = :chat_id AND receiver_id = :user_id))
                      AND message_type = 'individual'";
        } else {
            $query = "DELETE FROM " . $this->chat_messages_table . "
                      WHERE group_id = :chat_id AND message_type = 'group'";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        if ($chat_type === 'individual') {
            $stmt->bindParam(":chat_id", $chat_id);
        } else {
            $stmt->bindParam(":chat_id", $chat_id);
        }

        return $stmt->execute();
    }

    // Update typing status
    public function updateTypingStatus($user_id, $chat_id, $is_typing) {
        $query = "INSERT INTO typing_status (user_id, chat_id, is_typing, updated_at)
                  VALUES (:user_id, :chat_id, :is_typing, NOW())
                  ON DUPLICATE KEY UPDATE is_typing = :is_typing, updated_at = NOW()";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":chat_id", $chat_id);
        $stmt->bindParam(":is_typing", $is_typing, PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    // Delete message
    public function deleteMessage($user_id, $message_id) {
        $query = "DELETE FROM " . $this->chat_messages_table . "
                  WHERE id = :message_id AND sender_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":message_id", $message_id);
        $stmt->bindParam(":user_id", $user_id);

        return $stmt->execute();
    }
} 