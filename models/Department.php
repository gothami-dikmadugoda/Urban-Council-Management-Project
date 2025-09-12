<?php
class Department {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllDepartments() {
        $query = "SELECT * FROM departments ORDER BY name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDepartmentById($id) {
        $query = "SELECT * FROM departments WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDepartmentStaff($departmentId) {
        $query = "SELECT id, first_name, last_name, email 
                 FROM users 
                 WHERE department_id = ? AND role = 'staff' 
                 ORDER BY first_name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $departmentId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDepartmentComplaintCount($departmentId) {
        $query = "SELECT COUNT(*) as count FROM complaints WHERE department_id = :department_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":department_id", $departmentId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    public function getDepartmentResolutionRate($departmentId) {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
                 FROM complaints 
                 WHERE department_id = :department_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":department_id", $departmentId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row['total'] > 0) {
            return round(($row['resolved'] / $row['total']) * 100);
        }
        return 0;
    }

    public function getDepartmentAverageResolutionTime($departmentId) {
        $query = "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_date)) as avg_time
                 FROM complaints 
                 WHERE department_id = :department_id 
                 AND status = 'resolved' 
                 AND resolved_date IS NOT NULL";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":department_id", $departmentId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row['avg_time']) {
            if ($row['avg_time'] < 24) {
                return round($row['avg_time']) . ' hours';
            } else {
                return round($row['avg_time'] / 24) . ' days';
            }
        }
        return 'N/A';
    }

    public function createDepartment($name, $description) {
        $query = "INSERT INTO departments (name, description) VALUES (:name, :description)";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        
        return $stmt->execute();
    }

    public function updateDepartment($id, $name, $description) {
        $query = "UPDATE departments 
                 SET name = :name, description = :description 
                 WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        
        return $stmt->execute();
    }

    public function deleteDepartment($id) {
        $query = "DELETE FROM departments WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
} 