<?php
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../config/database.php';

class AdminController {
    private $db;
    private $admin;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->admin = new Admin($this->db);
    }

    public function getDashboardData() {
        return array(
            'stats' => $this->admin->getDashboardStats(),
            'staff_list' => $this->admin->getStaffList(),
            'recent_activities' => $this->admin->getRecentActivities()
        );
    }

    public function updateStaffStatus($id, $status) {
        if ($this->admin->updateStaffStatus($id, $status)) {
            return array(
                'success' => true,
                'message' => 'සාර්ථකව යාවත්කාලීන කරන ලදී / Status updated successfully'
            );
        }
        return array(
            'success' => false,
            'message' => 'යාවත්කාලීන කිරීමේදී දෝෂයක් ඇති විය / Error updating status'
        );
    }

    public function getStaffDetails($id) {
        $staff = $this->admin->getStaffById($id);
        if ($staff) {
            return array(
                'success' => true,
                'data' => $staff
            );
        }
        return array(
            'success' => false,
            'message' => 'කාර්ය මණ්ඩල සාමාජිකයෙකු සොයාගත නොහැකි විය / Staff member not found'
        );
    }

    public function validateAdminAccess() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /urban2/login.php');
            exit;
        }
        // Log access validation
        error_log("Admin access validated for user ID: " . $_SESSION['user_id']);
    }

    public function register($data) {
        // Validate input
        if(empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || 
           empty($data['password']) || empty($data['phone']) || empty($data['address'])) {
            return array(
                "success" => false,
                "message" => "සියලු ක්ෂේත්ර පුරවන්න / Please fill all fields"
            );
        }

        // Set user properties
        $this->admin->first_name = $data['first_name'];
        $this->admin->last_name = $data['last_name'];
        $this->admin->email = $data['email'];
        $this->admin->password = $data['password'];
        $this->admin->phone = $data['phone'];
        $this->admin->address = $data['address'];
        $this->admin->role = 'staff';
        $this->admin->department = $data['department'];
        $this->admin->job_role = $data['job_role'];
        $this->admin->status = 'active';

        // Create staff member
        if($this->admin->create()) {
            return array(
                "success" => true,
                "message" => "කාර්ය මණ්ඩල සාමාජිකයෙකු සාර්ථකව එකතු කරන ලදී / Staff member added successfully"
            );
        }

        return array(
            "success" => false,
            "message" => "කාර්ය මණ්ඩල සාමාජිකයෙකු එකතු කිරීමේදී දෝෂයක් ඇති විය / Error adding staff member"
        );
    }

    public function updateStaff($id, $data) {
        $this->admin->id = $id;
        $this->admin->first_name = $data['first_name'];
        $this->admin->last_name = $data['last_name'];
        $this->admin->email = $data['email'];
        $this->admin->phone = $data['phone'];
        $this->admin->address = $data['address'];
        $this->admin->department = $data['department'];
        $this->admin->job_role = $data['job_role'];
        $this->admin->status = $data['status'];

        if($this->admin->update()) {
            return array(
                "success" => true,
                "message" => "කාර්ය මණ්ඩල සාමාජිකයෙකු සාර්ථකව යාවත්කාලීන කරන ලදී / Staff member updated successfully"
            );
        }

        return array(
            "success" => false,
            "message" => "යාවත්කාලීන කිරීමේදී දෝෂයක් ඇති විය / Error updating staff member"
        );
    }

    public function deleteStaff($id) {
        $this->admin->id = $id;
        if($this->admin->delete()) {
            return array(
                "success" => true,
                "message" => "කාර්ය මණ්ඩල සාමාජිකයෙකු සාර්ථකව මකා දමන ලදී / Staff member deleted successfully"
            );
        }

        return array(
            "success" => false,
            "message" => "මකා දැමීමේදී දෝෂයක් ඇති විය / Error deleting staff member"
        );
    }

    public function getRecentNotifications() {
        // This is a placeholder for notifications
        // In a real application, you would fetch notifications from a notifications table
        return array(
            array(
                'id' => 1,
                'title' => 'New Complaint',
                'message' => 'A new complaint has been submitted',
                'created_at' => date('Y-m-d H:i:s'),
                'type' => 'complaint'
            ),
            array(
                'id' => 2,
                'title' => 'Schedule Update',
                'message' => 'Garbage collection schedule has been updated',
                'created_at' => date('Y-m-d H:i:s'),
                'type' => 'schedule'
            ),
            array(
                'id' => 3,
                'title' => 'System Maintenance',
                'message' => 'System will be under maintenance tomorrow',
                'created_at' => date('Y-m-d H:i:s'),
                'type' => 'system'
            )
        );
    }

    public function getUserFeedback() {
        $query = "SELECT f.*, u.first_name, u.last_name 
                 FROM feedback f 
                 JOIN users u ON f.user_id = u.id 
                 ORDER BY f.created_at DESC 
                 LIMIT 5";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the data
        foreach ($feedback as &$item) {
            $item['name'] = $item['first_name'] . ' ' . $item['last_name'];
            $item['message'] = $item['feedback'];
            unset($item['first_name'], $item['last_name'], $item['feedback']);
        }
        
        return $feedback;
    }

    public function updateProfilePicture($id, $profilePicture) {
        $query = "UPDATE users SET profile_picture = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $profilePicture);
        $stmt->bindParam(2, $id);

        if($stmt->execute()) {
            return array(
                "success" => true,
                "message" => "පැතිකඩ පින්තූරය සාර්ථකව යාවත්කාලීන කරන ලදී / Profile picture updated successfully"
            );
        }

        return array(
            "success" => false,
            "message" => "පැතිකඩ පින්තූරය යාවත්කාලීන කිරීමේදී දෝෂයක් ඇති විය / Error updating profile picture"
        );
    }

    public function getStaffByDepartment($department) {
        $query = "SELECT id, first_name, last_name, email, phone, department, job_role 
                  FROM users 
                  WHERE department = ? AND role = 'staff' 
                  ORDER BY first_name, last_name";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $department);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 