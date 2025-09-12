<?php
class Admin {
    private $conn;
    private $table_name = "users";

    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $phone;
    public $address;
    public $role;
    public $department;
    public $job_role;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function create() {
        // Check if email exists first
        if ($this->emailExists()) {
            return "email_exists";
        }

        $query = "INSERT INTO " . $this->table_name . "
                SET
                    first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    password = :password,
                    phone = :phone,
                    address = :address,
                    role = :role,
                    department = :department,
                    job_role = :job_role,
                    status = :status";

        $stmt = $this->conn->prepare($query);

        // Sanitize and hash
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->department = htmlspecialchars(strip_tags($this->department));
        $this->job_role = htmlspecialchars(strip_tags($this->job_role));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind values
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":department", $this->department);
        $stmt->bindParam(":job_role", $this->job_role);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    phone = :phone,
                    address = :address,
                    department = :department,
                    job_role = :job_role,
                    status = :status";

        // Add password to update if it's set
        if (isset($this->password)) {
            $query .= ", password = :password";
        }

        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->department = htmlspecialchars(strip_tags($this->department));
        $this->job_role = htmlspecialchars(strip_tags($this->job_role));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":department", $this->department);
        $stmt->bindParam(":job_role", $this->job_role);
        $stmt->bindParam(":status", $this->status);

        // Bind password if it's set
        if (isset($this->password)) {
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
            $stmt->bindParam(":password", $this->password);
        }

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getDashboardStats() {
        $stats = array();

        // Get total staff count
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE role = 'staff'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_staff'] = $row['total'];

        // Get total citizens count
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE role = 'citizen'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_citizens'] = $row['total'];

        // Get total private companies count
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE role = 'private_company'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_companies'] = $row['total'];

        // Get staff by department
        $query = "SELECT department, COUNT(*) as total FROM " . $this->table_name . 
                 " WHERE role = 'staff' GROUP BY department";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['staff_by_department'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }

    public function getStaffList() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE role = 'staff' ORDER BY department, job_role";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStaffById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? AND role = 'staff' LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStaffStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = ? WHERE id = ? AND role = 'staff'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $status);
        $stmt->bindParam(2, $id);
        return $stmt->execute();
    }

    public function getRecentActivities() {
        // This will be implemented when we add activity logging
        return array();
    }
}
?> 