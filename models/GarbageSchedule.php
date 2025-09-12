<?php
class GarbageSchedule {
    private $conn;
    private $table_name = "garbage_schedules";

    // Object properties
    public $id;
    public $area;
    public $schedule_date;
    public $schedule_time;
    public $waste_type;
    public $status;
    public $assigned_staff_id;
    public $created_by;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all garbage schedules
    public function getAll() {
        $query = "SELECT gs.*, 
                        u1.first_name as staff_first_name, 
                        u1.last_name as staff_last_name,
                        u2.first_name as creator_first_name,
                        u2.last_name as creator_last_name
                 FROM " . $this->table_name . " gs
                 LEFT JOIN users u1 ON gs.assigned_staff_id = u1.id
                 LEFT JOIN users u2 ON gs.created_by = u2.id
                 ORDER BY gs.schedule_date ASC, gs.schedule_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get schedules by staff member
    public function getByStaff($staffId) {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE assigned_staff_id = :staff_id 
                ORDER BY schedule_date ASC, schedule_time ASC";
        $stmt = $this->conn->prepare($query);
        $staffId = htmlspecialchars(strip_tags($staffId));
        $stmt->bindParam(":staff_id", $staffId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get schedules by waste type
    public function getByWasteType($wasteType) {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE waste_type = :waste_type 
                ORDER BY schedule_date ASC, schedule_time ASC";
        $stmt = $this->conn->prepare($query);
        $wasteType = htmlspecialchars(strip_tags($wasteType));
        $stmt->bindParam(":waste_type", $wasteType);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create new garbage schedule
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (area, schedule_date, schedule_time, waste_type, status, assigned_staff_id, created_by)
                VALUES
                (:area, :schedule_date, :schedule_time, :waste_type, :status, :assigned_staff_id, :created_by)";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->area = htmlspecialchars(strip_tags($this->area));
        $this->schedule_date = htmlspecialchars(strip_tags($this->schedule_date));
        $this->schedule_time = htmlspecialchars(strip_tags($this->schedule_time));
        $this->waste_type = htmlspecialchars(strip_tags($this->waste_type));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->assigned_staff_id = htmlspecialchars(strip_tags($this->assigned_staff_id));
        $this->created_by = htmlspecialchars(strip_tags($this->created_by));

        // Bind values
        $stmt->bindParam(":area", $this->area);
        $stmt->bindParam(":schedule_date", $this->schedule_date);
        $stmt->bindParam(":schedule_time", $this->schedule_time);
        $stmt->bindParam(":waste_type", $this->waste_type);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":assigned_staff_id", $this->assigned_staff_id);
        $stmt->bindParam(":created_by", $this->created_by);

        // Execute query
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Update garbage schedule
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    area = :area,
                    schedule_date = :schedule_date,
                    schedule_time = :schedule_time,
                    waste_type = :waste_type,
                    status = :status,
                    assigned_staff_id = :assigned_staff_id
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->area = htmlspecialchars(strip_tags($this->area));
        $this->schedule_date = htmlspecialchars(strip_tags($this->schedule_date));
        $this->schedule_time = htmlspecialchars(strip_tags($this->schedule_time));
        $this->waste_type = htmlspecialchars(strip_tags($this->waste_type));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->assigned_staff_id = htmlspecialchars(strip_tags($this->assigned_staff_id));

        // Bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":area", $this->area);
        $stmt->bindParam(":schedule_date", $this->schedule_date);
        $stmt->bindParam(":schedule_time", $this->schedule_time);
        $stmt->bindParam(":waste_type", $this->waste_type);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":assigned_staff_id", $this->assigned_staff_id);

        // Execute query
        return $stmt->execute();
    }

    // Delete garbage schedule
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    // Get schedule by ID
    public function getById() {
        $query = "SELECT gs.*, 
                        u1.first_name as staff_first_name, 
                        u1.last_name as staff_last_name,
                        u2.first_name as creator_first_name,
                        u2.last_name as creator_last_name
                 FROM " . $this->table_name . " gs
                 LEFT JOIN users u1 ON gs.assigned_staff_id = u1.id
                 LEFT JOIN users u2 ON gs.created_by = u2.id
                 WHERE gs.id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get schedules by area
    public function getByArea($area) {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE area = :area 
                ORDER BY schedule_date ASC, schedule_time ASC";
        $stmt = $this->conn->prepare($query);
        $area = htmlspecialchars(strip_tags($area));
        $stmt->bindParam(":area", $area);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get upcoming schedules
    public function getUpcoming() {
        $query = "SELECT gs.*, 
                        u1.first_name as staff_first_name, 
                        u1.last_name as staff_last_name
                 FROM " . $this->table_name . " gs
                 LEFT JOIN users u1 ON gs.assigned_staff_id = u1.id
                 WHERE gs.schedule_date >= CURDATE() 
                 AND gs.status = 'pending' 
                 ORDER BY gs.schedule_date ASC, gs.schedule_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create notification for schedule update
    public function createNotification($scheduleId, $type, $message) {
        $query = "INSERT INTO garbage_notifications 
                (schedule_id, notification_type, message) 
                VALUES (:schedule_id, :type, :message)";
        
        $stmt = $this->conn->prepare($query);
        
        $scheduleId = htmlspecialchars(strip_tags($scheduleId));
        $type = htmlspecialchars(strip_tags($type));
        $message = htmlspecialchars(strip_tags($message));
        
        $stmt->bindParam(":schedule_id", $scheduleId);
        $stmt->bindParam(":type", $type);
        $stmt->bindParam(":message", $message);
        
        return $stmt->execute();
    }

    // Get notifications for a user
    public function getUserNotifications($userId) {
        $query = "SELECT n.*, gs.area, gs.schedule_date, gs.schedule_time, gs.waste_type
                 FROM garbage_notifications n
                 JOIN garbage_schedules gs ON n.schedule_id = gs.id
                 JOIN user_notifications un ON n.id = un.notification_id
                 WHERE un.user_id = :user_id
                 ORDER BY n.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $userId = htmlspecialchars(strip_tags($userId));
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 