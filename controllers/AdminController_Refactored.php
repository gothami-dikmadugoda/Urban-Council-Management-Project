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
        return $this->admin->updateStaffStatus($id, $status);
    }

    public function getStaffDetails($id) {
        return $this->admin->getStaffById($id);
    }

    public function validateAdminAccess() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /urban2/login.php');
            exit;
        }
    }

    public function register($data) {
        return $this->admin->create($data);
    }

    public function updateStaff($id, $data) {
        return $this->admin->update($id, $data);
    }

    public function deleteStaff($id) {
        return $this->admin->delete($id);
    }

    public function getRecentNotifications() {
        return $this->admin->getRecentNotifications();
    }

    public function getUserFeedback() {
        return $this->admin->getUserFeedback();
    }

    public function updateProfilePicture($id, $profilePicture) {
        return $this->admin->updateProfilePicture($id, $profilePicture);
    }

    public function getStaffByDepartment($department) {
        return $this->admin->getStaffByDepartment($department);
    }
}
?>
