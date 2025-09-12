<?php
class User {
    private $conn;
    private $table = 'users';

    // User properties
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
    public $profile_picture;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new user
    public function create() {
        $query = "INSERT INTO " . $this->table . "
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

        // Sanitize
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        // Password is already hashed in UserController
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->department = $this->department ? htmlspecialchars(strip_tags($this->department)) : null;
        $this->job_role = $this->job_role ? htmlspecialchars(strip_tags($this->job_role)) : null;
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

        try {
            if($stmt->execute()) {
                return true;
            }
            error_log("Failed to create user: " . implode(", ", $stmt->errorInfo()));
            return false;
        } catch (PDOException $e) {
            error_log("Database error creating user: " . $e->getMessage());
            return false;
        }
    }

    // Read all users
    public function read() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single user
    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->role = $row['role'];
            $this->department = $row['department'];
            $this->job_role = $row['job_role'];
            $this->profile_picture = $row['profile_picture'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    // Get user by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? AND status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update user
    public function update() {
        $query = "UPDATE " . $this->table . " SET 
                  first_name = :first_name,
                  last_name = :last_name,
                  email = :email,
                  phone = :phone,
                  address = :address
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Clean and bind data
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));

        // Bind parameters
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':id', $this->id);

        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }

    // Update password
    public function updatePassword($newPassword) {
        $query = "UPDATE " . $this->table . " 
                  SET password = :password 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $this->id);

        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }

    // Update profile picture
    public function updateProfilePicture($picturePath) {
        $query = "UPDATE " . $this->table . " 
                  SET profile_picture = :profile_picture 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':profile_picture', $picturePath);
        $stmt->bindParam(':id', $this->id);

        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error updating profile picture: " . $e->getMessage());
            return false;
        }
    }

    // Delete user
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Login user
    public function login($email, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row && password_verify($password, $row['password'])) {
            $this->id = $row['id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            $this->department = $row['department']; // Use the department column directly
            $this->job_role = $row['job_role'];
            $this->status = $row['status'];

            // Debug logging
            error_log("User login successful. Department: " . $this->department . ", Job Role: " . $this->job_role);
            
            return true;
        }
        return false;
    }
}
?> 