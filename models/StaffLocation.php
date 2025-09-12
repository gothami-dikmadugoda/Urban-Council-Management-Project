<?php
require_once __DIR__ . '/../config/database.php';

class StaffLocation {
    private $conn;
    private $table = 'staff_locations';

    public $id;
    public $user_id;
    public $latitude;
    public $longitude;
    public $timestamp;
    public $status;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Update or create staff location
    public function updateLocation($user_id, $latitude, $longitude) {
        // First check if user is health department staff
        $query = "SELECT department, job_role FROM users 
                 WHERE id = ? 
                 AND department = 'health' 
                 AND job_role IN ('garbage_collector', 'field_visitor')
                 AND status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        
        if ($stmt->rowCount() == 0) {
            return false; // User is not authorized for location tracking
        }

        // Use REPLACE INTO to handle both insert and update
        $query = "REPLACE INTO " . $this->table . " 
                 (user_id, latitude, longitude, timestamp) 
                 VALUES (:user_id, :latitude, :longitude, NOW())";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":latitude", $latitude);
        $stmt->bindParam(":longitude", $longitude);

        return $stmt->execute();
    }

    // Get latest locations of all active health department staff
    public function getActiveStaffLocations() {
        $query = "SELECT sl.*, 
                  u.first_name, u.last_name, u.job_role,
                  u.phone, u.profile_image
                  FROM " . $this->table . " sl
                  INNER JOIN users u ON sl.user_id = u.id
                  WHERE u.department = 'health' 
                  AND u.status = 'active'
                  AND sl.id IN (
                      SELECT MAX(id) 
                      FROM " . $this->table . " 
                      GROUP BY user_id
                  )
                  ORDER BY sl.timestamp DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get location history for a specific staff member
    public function getStaffLocationHistory($user_id, $start_date = null, $end_date = null) {
        $query = "SELECT * FROM " . $this->table . " 
                 WHERE user_id = :user_id";
        
        if ($start_date && $end_date) {
            $query .= " AND timestamp BETWEEN :start_date AND :end_date";
        }
        
        $query .= " ORDER BY timestamp DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        
        if ($start_date && $end_date) {
            $stmt->bindParam(":start_date", $start_date);
            $stmt->bindParam(":end_date", $end_date);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 