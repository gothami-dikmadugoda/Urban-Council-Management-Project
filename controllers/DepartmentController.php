<?php
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../config/database.php';

class DepartmentController {
    private $db;
    private $department;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->department = new Department($this->db);
    }

    public function getAllDepartments() {
        return $this->department->getAllDepartments();
    }

    public function getDepartmentById($id) {
        return $this->department->getDepartmentById($id);
    }

    public function getDepartmentStaff($departmentId) {
        return $this->department->getDepartmentStaff($departmentId);
    }

    public function getDepartmentComplaintCount($departmentId) {
        return $this->department->getDepartmentComplaintCount($departmentId);
    }

    public function getDepartmentResolutionRate($departmentId) {
        return $this->department->getDepartmentResolutionRate($departmentId);
    }
}
?> 