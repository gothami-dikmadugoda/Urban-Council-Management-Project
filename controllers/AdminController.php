<?php
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../config/database.php';

class AdminController {
    private $conn;
    private $admin;
    private $db;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->admin = new Admin($this->conn);
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

        // Handle password update if provided
        if (!empty($data['password'])) {
            $this->admin->password = $data['password'];
        }

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
        $userId = $_SESSION['user_id'];
        $query = "SELECT n.*, c.id as complaint_id, c.title as complaint_title 
                 FROM notifications n 
                 LEFT JOIN complaints c ON n.reference_id = c.id 
                 WHERE n.user_id = ? AND n.is_read = 0
                 ORDER BY n.created_at DESC 
                 LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $userId);
        $stmt->execute();
        
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format notifications
        foreach ($notifications as &$notification) {
            if ($notification['type'] === 'complaint' && $notification['complaint_id']) {
                $notification['title'] = 'New Complaint: ' . $notification['complaint_title'];
                $notification['message'] = 'You have been assigned a new complaint to review';
            }
        }
        
        return $notifications;
    }

    public function createComplaintNotification($staffId, $complaintId, $complaintTitle) {
        $query = "INSERT INTO notifications (user_id, type, title, message, complaint_id, created_at, is_read) 
                 VALUES (?, 'complaint', ?, ?, ?, NOW(), 0)";
        
        $title = "New Complaint Assignment";
        $message = "You have been assigned to handle complaint: " . $complaintTitle;
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $staffId);
        $stmt->bindParam(2, $title);
        $stmt->bindParam(3, $message);
        $stmt->bindParam(4, $complaintId);
        
        return $stmt->execute();
    }

    public function getUserFeedback() {
        $query = "SELECT f.*, u.first_name, u.last_name 
                 FROM feedback f 
                 JOIN users u ON f.user_id = u.id 
                 ORDER BY f.created_at DESC 
                 LIMIT 5";
        
        $stmt = $this->conn->prepare($query);
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
        $stmt = $this->conn->prepare($query);
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
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $department);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllAdmins() {
        try {
            $query = "SELECT id, first_name, last_name, email, department 
                     FROM users 
                     WHERE role = 'admin' AND status = 'active'
                     ORDER BY first_name, last_name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting admin list: " . $e->getMessage());
            return [];
        }
    }

    public function getSettings() {
        try {
            require_once __DIR__ . '/../models/Settings.php';
            $settings = new Settings($this->conn);
            return $settings->getSettings();
        } catch (Exception $e) {
            error_log("Error getting settings: " . $e->getMessage());
            return false;
        }
    }

    public function getProfile() {
        try {
            $query = "SELECT * FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting profile: " . $e->getMessage());
            return false;
        }
    }

    public function updateProfile($data) {
        try {
            // First get the current profile data to preserve department and job_role
            $currentProfile = $this->getProfile();
            
            // Only update allowed fields
            $query = "UPDATE users SET 
                     first_name = :first_name,
                     last_name = :last_name,
                     email = :email,
                     phone = :phone,
                     address = :address
                     WHERE id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);

            if($stmt->execute()) {
                return array(
                    "success" => true,
                    "message" => "පැතිකඩ සාර්ථකව යාවත්කාලීන කරන ලදී / Profile updated successfully"
                );
            }

            return array(
                "success" => false,
                "message" => "පැතිකඩ යාවත්කාලීන කිරීමේදී දෝෂයක් ඇති විය / Error updating profile"
            );
        } catch (PDOException $e) {
            error_log("Error updating profile: " . $e->getMessage());
            return array(
                "success" => false,
                "message" => "පැතිකඩ යාවත්කාලීන කිරීමේදී දෝෂයක් ඇති විය / Error updating profile"
            );
        }
    }

    public function updatePassword($data) {
        try {
            // Verify current password
            $query = "SELECT password FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($data['current_password'], $user['password'])) {
                $_SESSION['message'] = "Current password is incorrect.";
                $_SESSION['message_type'] = "danger";
                return;
            }

            // Verify new password matches confirmation
            if ($data['new_password'] !== $data['confirm_password']) {
                $_SESSION['message'] = "New passwords do not match.";
                $_SESSION['message_type'] = "danger";
                return;
            }

            // Update password
            $query = "UPDATE users SET password = :password WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $hashed_password = password_hash($data['new_password'], PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Password updated successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Failed to update password.";
                $_SESSION['message_type'] = "danger";
            }
        } catch (PDOException $e) {
            error_log("Error updating password: " . $e->getMessage());
            $_SESSION['message'] = "An error occurred while updating password.";
            $_SESSION['message_type'] = "danger";
        }
    }

    public function updateSystemSettings($data) {
        try {
            require_once __DIR__ . '/../models/Settings.php';
            $settings = new Settings($this->conn);
            
            if ($settings->updateSettings($data)) {
                $_SESSION['message'] = "System settings updated successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Failed to update system settings.";
                $_SESSION['message_type'] = "danger";
            }
        } catch (Exception $e) {
            error_log("Error updating system settings: " . $e->getMessage());
            $_SESSION['message'] = "An error occurred while updating system settings.";
            $_SESSION['message_type'] = "danger";
        }
    }

    public function getGarbageSchedules() {
        try {
            $query = "SELECT * FROM garbage_schedules ORDER BY schedule_date ASC, schedule_time ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug log
            error_log("Fetched schedules: " . print_r($schedules, true));
            
            return array(
                "success" => true,
                "data" => $schedules
            );
        } catch (Exception $e) {
            error_log("Error getting garbage schedules: " . $e->getMessage());
            return array(
                "success" => false,
                "message" => "Error getting garbage schedules"
            );
        }
    }

    public function addGarbageSchedule($data) {
        try {
            // Insert into garbage_schedules table using prepared statement
            $query = "INSERT INTO garbage_schedules (area, schedule_date, schedule_time, waste_type, created_by, created_at) 
                      VALUES (:area, :schedule_date, :schedule_time, :waste_type, :created_by, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':area', $data['area']);
            $stmt->bindParam(':schedule_date', $data['schedule_date']);
            $stmt->bindParam(':schedule_time', $data['schedule_time']);
            $stmt->bindParam(':waste_type', $data['waste_type']);
            $stmt->bindParam(':created_by', $data['created_by']);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Schedule added successfully / කාලසටහන සාර්ථකව එකතු කරන ලදී'
                ];
            } else {
                throw new Exception("Failed to execute query");
            }
        } catch (Exception $e) {
            error_log("Error in addGarbageSchedule: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error adding schedule / කාලසටහන එකතු කිරීමේදී දෝෂයක් ඇති විය'
            ];
        }
    }

    public function updateGarbageSchedule($id, $data) {
        try {
            // Update garbage_schedules table using prepared statement
            $query = "UPDATE garbage_schedules 
                      SET area = :area, 
                          schedule_date = :schedule_date, 
                          schedule_time = :schedule_time, 
                          waste_type = :waste_type 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':area', $data['area']);
            $stmt->bindParam(':schedule_date', $data['schedule_date']);
            $stmt->bindParam(':schedule_time', $data['schedule_time']);
            $stmt->bindParam(':waste_type', $data['waste_type']);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Schedule updated successfully / කාලසටහන සාර්ථකව යාවත්කාලීන කරන ලදී'
                ];
            } else {
                throw new Exception("Failed to execute query");
            }
        } catch (Exception $e) {
            error_log("Error in updateGarbageSchedule: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error updating schedule / කාලසටහන යාවත්කාලීන කිරීමේදී දෝෂයක් ඇති විය'
            ];
        }
    }

    public function deleteGarbageSchedule($id) {
        try {
            require_once __DIR__ . '/../models/GarbageSchedule.php';
            $schedule = new GarbageSchedule($this->conn);
            $schedule->id = $id;

            if($schedule->delete()) {
                return array(
                    "success" => true,
                    "message" => "සාර්ථකව මකා දමන ලදී / Schedule deleted successfully"
                );
            }

            return array(
                "success" => false,
                "message" => "මකා දැමීමේදී දෝෂයක් ඇති විය / Error deleting schedule"
            );
        } catch (Exception $e) {
            error_log("Error deleting garbage schedule: " . $e->getMessage());
            return array(
                "success" => false,
                "message" => "මකා දැමීමේදී දෝෂයක් ඇති විය / Error deleting schedule"
            );
        }
    }

    public function getAllStaff() {
        $query = "SELECT id, first_name, last_name, email, department 
                 FROM users 
                 WHERE role = 'staff' 
                 ORDER BY department, first_name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAnnouncements() {
        try {
            $query = "SELECT a.*, u.first_name, u.last_name 
                      FROM announcements a 
                      LEFT JOIN users u ON a.created_by = u.id 
                      ORDER BY a.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching announcements: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentAnnouncements($limit = 5) {
        try {
            $sql = "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as author_name 
                    FROM announcements a 
                    JOIN users u ON a.created_by = u.id 
                    ORDER BY a.created_at DESC 
                    LIMIT :limit";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting recent announcements: " . $e->getMessage());
            return [];
        }
    }

    public function getStaffAnalytics($staffId) {
        try {
            $db = $this->conn;
            
            // Get assigned complaints analytics
            $assignedComplaintsQuery = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                DATE_FORMAT(created_at, '%Y-%m') as month
                FROM complaints 
                WHERE assigned_to = :staff_id
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month DESC
                LIMIT 12";
            
            $stmt = $db->prepare($assignedComplaintsQuery);
            $stmt->bindParam(':staff_id', $staffId);
            $stmt->execute();
            $complaintsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get bookings analytics
            $bookingsQuery = "SELECT 
                COUNT(*) as total,
                DATE_FORMAT(booking_date, '%Y-%m') as month
                FROM bookings 
                WHERE staff_id = :staff_id
                GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
                ORDER BY month DESC
                LIMIT 12";
            
            $stmt = $db->prepare($bookingsQuery);
            $stmt->bindParam(':staff_id', $staffId);
            $stmt->execute();
            $bookingsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get notifications analytics
            $notificationsQuery = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
                DATE_FORMAT(created_at, '%Y-%m') as month
                FROM notifications 
                WHERE user_id = :staff_id
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month DESC
                LIMIT 12";
            
            $stmt = $db->prepare($notificationsQuery);
            $stmt->bindParam(':staff_id', $staffId);
            $stmt->execute();
            $notificationsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate trends and percentages
            $analytics = [
                'assigned_complaints' => $this->calculateTrends($complaintsData),
                'resolved_complaints' => $this->calculateResolutionRate($complaintsData),
                'bookings' => $this->calculateTrends($bookingsData),
                'notifications' => $this->calculateTrends($notificationsData)
            ];
            
            return ['status' => 'success', 'data' => $analytics];
        } catch (PDOException $e) {
            error_log("Error in getStaffAnalytics: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Failed to fetch analytics data'];
        }
    }

    private function calculateTrends($data) {
        if (empty($data)) {
            return [
                'total' => 0,
                'trend' => [],
                'percentage_change' => 0
            ];
        }
        
        $trend = array_reverse(array_map(function($item) {
            return ['month' => $item['month'], 'count' => (int)$item['total']];
        }, $data));
        
        $currentMonth = isset($data[0]['total']) ? (int)$data[0]['total'] : 0;
        $previousMonth = isset($data[1]['total']) ? (int)$data[1]['total'] : 0;
        
        $percentageChange = $previousMonth > 0 
            ? round((($currentMonth - $previousMonth) / $previousMonth) * 100, 1)
            : 0;
        
        return [
            'total' => $currentMonth,
            'trend' => $trend,
            'percentage_change' => $percentageChange
        ];
    }

    private function calculateResolutionRate($data) {
        if (empty($data)) {
            return [
                'total' => 0,
                'trend' => [],
                'resolution_rate' => 0
            ];
        }
        
        $trend = array_reverse(array_map(function($item) {
            $total = (int)$item['total'];
            $resolved = (int)$item['resolved'];
            $rate = $total > 0 ? round(($resolved / $total) * 100, 1) : 0;
            return ['month' => $item['month'], 'count' => $resolved, 'rate' => $rate];
        }, $data));
        
        $currentTotal = isset($data[0]['total']) ? (int)$data[0]['total'] : 0;
        $currentResolved = isset($data[0]['resolved']) ? (int)$data[0]['resolved'] : 0;
        
        $resolutionRate = $currentTotal > 0 
            ? round(($currentResolved / $currentTotal) * 100, 1)
            : 0;
        
        return [
            'total' => $currentResolved,
            'trend' => $trend,
            'resolution_rate' => $resolutionRate
        ];
    }

    public function getRecentActivities() {
        try {
            $query = "SELECT 
                        a.id,
                        a.activity_type,
                        a.description,
                        a.user_id,
                        a.created_at,
                        u.first_name,
                        u.last_name,
                        u.role
                    FROM activities a
                    LEFT JOIN users u ON a.user_id = u.id
                    ORDER BY a.created_at DESC
                    LIMIT 10";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format activities with appropriate icons and timestamps
            foreach ($activities as &$activity) {
                $activity['icon'] = $this->getActivityIcon($activity['activity_type']);
                $activity['timestamp'] = $this->formatTimestamp($activity['created_at']);
                $activity['user_name'] = $activity['first_name'] . ' ' . $activity['last_name'];
            }

            return $activities;
        } catch (PDOException $e) {
            error_log("Error fetching recent activities: " . $e->getMessage());
            return [];
        }
    }

    private function getActivityIcon($activityType) {
        $icons = [
            'login' => 'bx-log-in',
            'logout' => 'bx-log-out',
            'complaint_created' => 'bx-message-square-add',
            'complaint_updated' => 'bx-message-square-edit',
            'complaint_resolved' => 'bx-check-circle',
            'staff_added' => 'bx-user-plus',
            'staff_updated' => 'bx-user-check',
            'staff_deleted' => 'bx-user-x',
            'report_generated' => 'bx-file',
            'settings_updated' => 'bx-cog',
            'default' => 'bx-bell'
        ];

        return isset($icons[$activityType]) ? $icons[$activityType] : $icons['default'];
    }

    private function formatTimestamp($timestamp) {
        $datetime = new DateTime($timestamp);
        $now = new DateTime();
        $interval = $now->diff($datetime);

        if ($interval->y > 0) {
            return $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
        }
        if ($interval->m > 0) {
            return $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
        }
        if ($interval->d > 0) {
            return $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
        }
        if ($interval->h > 0) {
            return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        }
        if ($interval->i > 0) {
            return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        }
        return 'Just now';
    }

    public function logActivity($userId, $activityType, $description) {
        try {
            $query = "INSERT INTO activities (user_id, activity_type, description, created_at) 
                     VALUES (:user_id, :activity_type, :description, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':user_id' => $userId,
                ':activity_type' => $activityType,
                ':description' => $description
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error logging activity: " . $e->getMessage());
            return false;
        }
    }

    public function getUserInfo($userId) {
        try {
            $query = "SELECT first_name, last_name, email, role FROM users WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user info: " . $e->getMessage());
            return null;
        }
    }
}
?> 