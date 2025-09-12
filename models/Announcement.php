<?php
require_once __DIR__ . '/../config/database.php';

class Announcement {
    private $conn;
    private $table_name = "announcements";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new announcement
    public function create($title, $content, $posted_by, $expiry_datetime = null) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                     (title, content, posted_by, expiry_datetime) 
                     VALUES (:title, :content, :posted_by, :expiry_datetime)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":content", $content);
            $stmt->bindParam(":posted_by", $posted_by);
            $stmt->bindParam(":expiry_datetime", $expiry_datetime);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error in create announcement: " . $e->getMessage());
            return false;
        }
    }

    // Get all active announcements
    public function getActiveAnnouncements() {
        try {
            $query = "SELECT a.*, u.first_name, u.last_name 
                     FROM " . $this->table_name . " a
                     JOIN users u ON a.posted_by = u.id
                     WHERE (a.expiry_datetime IS NULL OR a.expiry_datetime > NOW())
                     ORDER BY a.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getActiveAnnouncements: " . $e->getMessage());
            return [];
        }
    }

    // Get announcement by ID
    public function getById($id) {
        try {
            $query = "SELECT a.*, u.first_name, u.last_name 
                     FROM " . $this->table_name . " a
                     JOIN users u ON a.posted_by = u.id
                     WHERE a.announcement_id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getById: " . $e->getMessage());
            return false;
        }
    }

    // Update announcement
    public function update($id, $title, $content, $expiry_datetime = null) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                     SET title = :title, 
                         content = :content, 
                         expiry_datetime = :expiry_datetime
                     WHERE announcement_id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":content", $content);
            $stmt->bindParam(":expiry_datetime", $expiry_datetime);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in update announcement: " . $e->getMessage());
            return false;
        }
    }

    // Delete announcement
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " 
                     WHERE announcement_id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in delete announcement: " . $e->getMessage());
            return false;
        }
    }

    // Get announcements by user
    public function getByUser($user_id) {
        try {
            $query = "SELECT a.*, u.first_name, u.last_name 
                     FROM " . $this->table_name . " a
                     JOIN users u ON a.posted_by = u.id
                     WHERE a.posted_by = :user_id
                     ORDER BY a.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getByUser: " . $e->getMessage());
            return [];
        }
    }
} 