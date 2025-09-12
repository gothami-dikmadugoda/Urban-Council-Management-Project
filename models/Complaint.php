<?php
class Complaint {
    private $conn;
    private $table_name = "complaints";

    public $id;
    public $user_id;
    public $title;
    public $description;
    public $category;
    public $image;
    public $status;
    public $priority;
    public $department_id;
    public $assigned_to;
    public $created_at;
    public $updated_at;
    public $resolved_date;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . "
                (user_id, title, description, category, image, status, priority, department_id, assigned_to, created_at)
                VALUES
                (:user_id, :title, :description, :category, :image, :status, :priority, :department_id, :assigned_to, NOW())";

        $stmt = $this->conn->prepare($query);

        // Sanitize and bind
        $stmt->bindParam(":user_id", $data['user_id']);
        $stmt->bindParam(":title", $data['title']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":category", $data['category']);
        $stmt->bindParam(":image", $data['image'], PDO::PARAM_LOB);
        $stmt->bindParam(":status", $data['status']);
        $stmt->bindParam(":priority", $data['priority']);
        $stmt->bindParam(":department_id", $data['department_id']);
        $stmt->bindParam(":assigned_to", $data['assigned_to']);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getById($id) {
        $query = "SELECT c.*, d.name as department_name,
                 CONCAT(u.first_name, ' ', u.last_name) as staff_name
                 FROM " . $this->table_name . " c
                 LEFT JOIN departments d ON c.department_id = d.id
                 LEFT JOIN users u ON c.department_id = u.department AND u.role = 'staff'
                 WHERE c.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByUserId($userId) {
        $query = "SELECT c.*, d.name as department_name, 
                 CONCAT(u.first_name, ' ', u.last_name) as staff_name
                 FROM " . $this->table_name . " c
                 LEFT JOIN departments d ON c.department_id = d.id
                 LEFT JOIN users u ON c.department_id = u.department AND u.role = 'staff'
                 WHERE c.user_id = :user_id
                 ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssignedTo($staffId) {
        $query = "SELECT c.*, u.first_name, u.last_name
                 FROM complaints c
                 JOIN users u ON c.user_id = u.id
                 WHERE c.assigned_to = ?
                 ORDER BY c.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $staffId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status, 
                    updated_at = NOW(),
                    resolved_date = CASE WHEN :status = 'resolved' THEN NOW() ELSE resolved_date END
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function updatePriority($id, $priority) {
        $query = "UPDATE " . $this->table_name . "
                SET priority = :priority, 
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":priority", $priority);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function addNote($complaintId, $userId, $note) {
        $query = "INSERT INTO complaint_notes (complaint_id, created_by, note, created_at)
                VALUES (:complaint_id, :created_by, :note, NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":complaint_id", $complaintId);
        $stmt->bindParam(":created_by", $userId);
        $stmt->bindParam(":note", $note);

        return $stmt->execute();
    }

    public function getNotes($complaintId) {
        $query = "SELECT cn.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
                 FROM complaint_notes cn
                 JOIN users u ON cn.created_by = u.id
                 WHERE cn.complaint_id = :complaint_id
                 ORDER BY cn.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":complaint_id", $complaintId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $query = "SELECT c.*, d.name as department_name
                  FROM complaints c
                  LEFT JOIN departments d ON c.department_id = d.id
                  ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>