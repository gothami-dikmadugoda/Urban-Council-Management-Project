<?php
require_once __DIR__ . '/../models/Chat.php';
require_once __DIR__ . '/../config/database.php';

class ChatController {
    private $chat;

    public function __construct() {
        $this->chat = new Chat();
    }

    // Get chat messages between users
    public function getMessages($sender_id, $receiver_id) {
        try {
            $messages = $this->chat->getIndividualMessages($sender_id, $receiver_id);
            $this->chat->markMessagesAsRead($receiver_id, $sender_id);
            return $messages;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // Get group chat messages
    public function getGroupMessages($group_id) {
        try {
            return $this->chat->getGroupMessages($group_id);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // Send message
    public function sendMessage($data) {
        try {
            if ($data['type'] === 'individual') {
                return $this->chat->sendIndividualMessage(
                    $data['sender_id'],
                    $data['receiver_id'],
                    $data['message']
                );
            } else {
                return $this->chat->sendGroupMessage(
                    $data['sender_id'],
                    $data['group_id'],
                    $data['message']
                );
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // Get user's chat groups
    public function getUserGroups($user_id) {
        try {
            // First, ensure department groups exist
            $this->initializeDepartmentGroups();
            
            // Then get user's groups
            return $this->chat->getUserGroups($user_id);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    // Initialize department groups
    public function initializeDepartmentGroups() {
        try {
            // Create Health Department group if it doesn't exist
            $healthGroup = $this->chat->createDepartmentGroup('Health Department Chat', 'health');
            if ($healthGroup) {
                // Add all health department staff to the group
                $this->addDepartmentStaffToGroup($healthGroup, 'health');
            }

            // Create Engineering Department group if it doesn't exist
            $engineeringGroup = $this->chat->createDepartmentGroup('Engineering Department Chat', 'engineering');
            if ($engineeringGroup) {
                // Add all engineering department staff to the group
                $this->addDepartmentStaffToGroup($engineeringGroup, 'engineering');
            }

            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    private function addDepartmentStaffToGroup($group_id, $department) {
        try {
            // Get all staff from the department
            $query = "SELECT id FROM users WHERE department = :department AND role = 'staff'";
            $database = new Database();
            $conn = $database->getConnection();
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":department", $department);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->chat->addGroupMember($group_id, $row['id']);
            }
            
            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // Get unread message count
    public function getUnreadCount($user_id) {
        try {
            return $this->chat->getUnreadCount($user_id);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    // Update user's online status
    public function updateUserStatus($user_id, $status) {
        try {
            return $this->chat->updateUserStatus($user_id, $status);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // Clear chat messages
    public function clearChat($user_id, $chat_type, $chat_id) {
        try {
            return $this->chat->clearChat($user_id, $chat_type, $chat_id);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getUnreadMessageCount($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM messages 
                WHERE receiver_id = ? AND is_read = 0
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Error in getUnreadMessageCount: " . $e->getMessage());
            throw new Exception("Error fetching unread message count");
        }
    }

    public function markMessageAsRead($messageId, $userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE messages 
                SET is_read = 1 
                WHERE id = ? AND receiver_id = ? AND is_read = 0
            ");
            return $stmt->execute([$messageId, $userId]);
        } catch (PDOException $e) {
            error_log("Error in markMessageAsRead: " . $e->getMessage());
            throw new Exception("Error marking message as read");
        }
    }

    // Search messages
    public function searchMessages($user_id, $search_term, $type = 'all', $group_id = null) {
        try {
            return $this->chat->searchMessages($user_id, $search_term, $type, $group_id);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // Update typing status
    public function updateTypingStatus($user_id, $chat_id, $is_typing) {
        try {
            return $this->chat->updateTypingStatus($user_id, $chat_id, $is_typing);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // Delete message
    public function deleteMessage($user_id, $message_id) {
        try {
            return $this->chat->deleteMessage($user_id, $message_id);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
} 