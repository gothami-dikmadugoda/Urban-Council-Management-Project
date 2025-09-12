<?php
require_once __DIR__ . '/../config/database.php';

class ProfileController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function updateProfile($userId, $formData) {
        try {
            $query = "UPDATE users SET 
                first_name = :first_name,
                last_name = :last_name,
                email = :email,
                phone = :phone,
                address = :address
                WHERE id = :user_id";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':first_name', $formData['first_name']);
            $stmt->bindParam(':last_name', $formData['last_name']);
            $stmt->bindParam(':email', $formData['email']);
            $stmt->bindParam(':phone', $formData['phone']);
            $stmt->bindParam(':address', $formData['address']);
            $stmt->bindParam(':user_id', $userId);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to update profile. Please try again.'
            ];

        } catch (PDOException $e) {
            error_log("Profile update error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update profile. Please try again.'
            ];
        }
    }

    public function updateProfilePicture($userId, $fileArray) {
        if ($fileArray['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($fileArray['name'], PATHINFO_EXTENSION);
            $targetDir = '/urban2/uploads/profiles/';
            if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $targetDir)) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . $targetDir, 0777, true);
            }
            $fileName = 'profile_' . $userId . '_' . time() . '.' . $ext;
            $targetPath = $targetDir . $fileName;
            $absolutePath = $_SERVER['DOCUMENT_ROOT'] . $targetPath;
            if (move_uploaded_file($fileArray['tmp_name'], $absolutePath)) {
            $query = "UPDATE users SET profile_image = :profile_image WHERE id = :user_id";
            $stmt = $this->db->prepare($query);
                $stmt->bindParam(':profile_image', $targetPath);
            $stmt->bindParam(':user_id', $userId);
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Profile picture updated successfully'
                ];
            }
            }
            return [
                'success' => false,
                'message' => 'Failed to upload profile picture'
            ];
        }
        return [
            'success' => false,
            'message' => 'No file uploaded or upload error'
        ];
    }

    public function updatePassword($userId, $newPassword) {
        try {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $query = "UPDATE users SET password = :password WHERE id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':password', $hashed);
            $stmt->bindParam(':user_id', $userId);
            if ($stmt->execute()) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Failed to update password.'];
            }
        } catch (PDOException $e) {
            error_log("Password update error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update password.'];
        }
    }
}
?> 