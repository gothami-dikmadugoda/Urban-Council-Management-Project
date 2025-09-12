<?php
require_once __DIR__ . '/../models/SystemMessage.php';
require_once __DIR__ . '/../config/database.php';

class SystemMessageController {
    private $systemMessageModel;

    public function __construct() {
        $this->systemMessageModel = new SystemMessage();
    }

    public function sendSystemMessage($senderId, $message, $file = null) {
        try {
            $fileUrl = null;
            $fileType = null;
            $fileName = null;

            if ($file && $file['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/system_messages/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileName = basename($file['name']);
                $fileType = $file['type'];
                $uniqueFileName = uniqid() . '_' . $fileName;
                $fileUrl = 'uploads/system_messages/' . $uniqueFileName;
                $fullPath = $uploadDir . $uniqueFileName;

                if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
                    throw new Exception('Failed to upload file');
                }
            }

            $success = $this->systemMessageModel->createSystemMessage(
                $senderId,
                $message,
                $fileUrl,
                $fileType,
                $fileName
            );

            if (!$success) {
                throw new Exception('Failed to create system message');
            }

            return [
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [
                    'file_url' => $fileUrl,
                    'file_name' => $fileName
                ]
            ];
        } catch (Exception $e) {
            error_log("Error in SystemMessageController::sendSystemMessage: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ];
        }
    }

    public function getSystemMessages($limit = 50) {
        try {
            $messages = $this->systemMessageModel->getSystemMessages($limit);
            return [
                'success' => true,
                'data' => $messages
            ];
        } catch (Exception $e) {
            error_log("Error in SystemMessageController::getSystemMessages: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve messages'
            ];
        }
    }

    public function getUnreadCount($userId) {
        try {
            $count = $this->systemMessageModel->getUnreadSystemMessageCount($userId);
            return [
                'success' => true,
                'count' => $count
            ];
        } catch (Exception $e) {
            error_log("Error in SystemMessageController::getUnreadCount: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get unread count'
            ];
        }
    }

    public function markAsRead($messageId, $userId) {
        try {
            $success = $this->systemMessageModel->markAsRead($messageId, $userId);
            return [
                'success' => $success,
                'message' => $success ? 'Message marked as read' : 'Failed to mark message as read'
            ];
        } catch (Exception $e) {
            error_log("Error in SystemMessageController::markAsRead: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to mark message as read'
            ];
        }
    }
} 