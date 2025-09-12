<?php
class Settings {
    private $conn;
    private $table_name = "settings";

    public $id;
    public $site_name;
    public $site_description;
    public $contact_email;
    public $contact_phone;
    public $address;
    public $maintenance_mode;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getSettings() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting settings: " . $e->getMessage());
            return false;
        }
    }

    public function updateSettings($data) {
        try {
            $query = "UPDATE " . $this->table_name . " SET 
                     site_name = :site_name,
                     site_description = :site_description,
                     contact_email = :contact_email,
                     contact_phone = :contact_phone,
                     address = :address,
                     maintenance_mode = :maintenance_mode
                     WHERE id = 1";
            
            $stmt = $this->conn->prepare($query);
            
            // Sanitize and bind values
            $this->site_name = htmlspecialchars(strip_tags($data['site_name']));
            $this->site_description = htmlspecialchars(strip_tags($data['site_description']));
            $this->contact_email = htmlspecialchars(strip_tags($data['contact_email']));
            $this->contact_phone = htmlspecialchars(strip_tags($data['contact_phone']));
            $this->address = htmlspecialchars(strip_tags($data['address']));
            $this->maintenance_mode = isset($data['maintenance_mode']) ? 1 : 0;

            // Bind values
            $stmt->bindParam(':site_name', $this->site_name);
            $stmt->bindParam(':site_description', $this->site_description);
            $stmt->bindParam(':contact_email', $this->contact_email);
            $stmt->bindParam(':contact_phone', $this->contact_phone);
            $stmt->bindParam(':address', $this->address);
            $stmt->bindParam(':maintenance_mode', $this->maintenance_mode);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating settings: " . $e->getMessage());
            return false;
        }
    }

    public function isMaintenanceMode() {
        try {
            $query = "SELECT maintenance_mode FROM " . $this->table_name . " WHERE id = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['maintenance_mode'] == 1;
        } catch (PDOException $e) {
            error_log("Error checking maintenance mode: " . $e->getMessage());
            return false;
        }
    }
} 