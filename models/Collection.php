<?php
require_once dirname(__DIR__) . '/config/database.php';

class Collection {
    private $db;
    private $table = 'collections';

    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
        } catch(Exception $e) {
            error_log("Connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    public function getAreas() {
        try {
            $stmt = $this->db->query("SELECT id, name, description FROM collection_areas WHERE status = 'active'");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error fetching areas: " . $e->getMessage());
            return [];
        }
    }

    public function getAvailableStaff() {
        try {
            $stmt = $this->db->prepare("
                SELECT id, first_name, last_name 
                FROM users 
                WHERE role = 'staff' 
                AND job_role = 'garbage_manager'
                AND status = 'active'
                ORDER BY first_name, last_name
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error fetching available staff: " . $e->getMessage());
            return [];
        }
    }

    public function scheduleCollection($data) {
        try {
            // Debug log
            error_log("Starting collection scheduling with data: " . print_r($data, true));
            
            // Generate a unique collection ID (format: COL-YYYYMMDD-XXXX)
            $collection_id = 'COL-' . date('Ymd') . '-' . substr(uniqid(), -4);
            
            $stmt = $this->db->prepare("
                INSERT INTO collections (
                    collection_id, user_id, area_id, collection_date,
                    time_slot, waste_type, quantity, description,
                    status, assigned_to, created_at
                ) VALUES (
                    :collection_id, :user_id, :area_id, :collection_date,
                    :time_slot, :waste_type, :quantity, :description,
                    'pending', :assigned_to, NOW()
                )
            ");

            $params = [
                'collection_id' => $collection_id,
                'user_id' => $data['user_id'],
                'area_id' => $data['area_id'],
                'collection_date' => $data['collection_date'],
                'time_slot' => $data['time_slot'],
                'waste_type' => $data['waste_type'],
                'quantity' => $data['quantity'],
                'description' => isset($data['description']) ? $data['description'] : '',
                'assigned_to' => $data['assigned_to']
            ];

            error_log("Executing SQL with params: " . print_r($params, true));

            $result = $stmt->execute($params);

            if ($result) {
                error_log("Collection inserted successfully with ID: " . $collection_id);
                // Create notification for assigned staff
                $this->createStaffNotification($collection_id, $data['assigned_to']);
                return [
                    'status' => 'success',
                    'collection_id' => $collection_id
                ];
            }
            error_log("Failed to insert collection");
            return [
                'status' => 'error',
                'message' => 'Failed to insert collection'
            ];
        } catch(PDOException $e) {
            error_log("Database error in scheduleCollection: " . $e->getMessage());
            error_log("SQL State: " . $e->errorInfo[0]);
            error_log("Error Code: " . $e->errorInfo[1]);
            error_log("Error Message: " . $e->errorInfo[2]);
            return [
                'status' => 'error',
                'message' => 'Database error occurred'
            ];
        } catch(Exception $e) {
            error_log("General error in scheduleCollection: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'An error occurred while scheduling collection'
            ];
        }
    }

    private function createStaffNotification($collection_id, $staff_id) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (
                    user_id, title, message, type,
                    category, status, action_url
                ) VALUES (
                    :user_id,
                    'New Collection Assignment',
                    :message,
                    'info',
                    'collection',
                    'unread',
                    :action_url
                )
            ");

            $stmt->execute([
                'user_id' => $staff_id,
                'message' => "You have been assigned to collection request {$collection_id}.",
                'action_url' => '/views/staff/view-collection.php?id=' . $collection_id
            ]);
        } catch(PDOException $e) {
            error_log("Error creating staff notification: " . $e->getMessage());
        }
    }

    private function createReceptionistNotification($collection_id) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (
                    user_id, title, message, type,
                    category, status, action_url
                ) VALUES (
                    :user_id,
                    'New Collection Request',
                    :message,
                    'info',
                    'collection',
                    'unread',
                    :action_url
                )
            ");

            $receptionist_id = $this->getReceptionist();
            if ($receptionist_id) {
                $stmt->execute([
                    'user_id' => $receptionist_id,
                    'message' => "New collection request {$collection_id} has been submitted and assigned to staff.",
                    'action_url' => '/views/staff/view-collection.php?id=' . $collection_id
                ]);
            }
        } catch(PDOException $e) {
            error_log("Error creating receptionist notification: " . $e->getMessage());
        }
    }

    private function getReceptionist() {
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM users 
                WHERE role = 'staff' 
                AND job_role = 'receptionist' 
                AND status = 'active' 
                LIMIT 1
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['id'] : null;
        } catch(PDOException $e) {
            error_log("Error getting receptionist: " . $e->getMessage());
            return null;
        }
    }

    public function getUpcomingCollections($user_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, ca.name as area_name, 
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_staff_name
                FROM collections c
                JOIN collection_areas ca ON c.area_id = ca.id
                LEFT JOIN users u ON c.assigned_to = u.id
                WHERE c.user_id = :user_id 
                AND c.collection_date >= CURDATE()
                AND c.status != 'cancelled'
                ORDER BY c.collection_date ASC, 
                FIELD(c.time_slot, 'morning', 'afternoon', 'evening')
                LIMIT 5
            ");
            $stmt->execute(['user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error fetching upcoming collections: " . $e->getMessage());
            return [];
        }
    }

    public function getStaffAssignments($staff_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, ca.name as area_name,
                       CONCAT(u.first_name, ' ', u.last_name) as citizen_name
                FROM collections c
                JOIN collection_areas ca ON c.area_id = ca.id
                JOIN users u ON c.user_id = u.id
                WHERE c.assigned_to = :staff_id
                AND c.status IN ('pending', 'approved')
                ORDER BY c.collection_date ASC,
                FIELD(c.time_slot, 'morning', 'afternoon', 'evening')
            ");
            $stmt->execute(['staff_id' => $staff_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error fetching staff assignments: " . $e->getMessage());
            return [];
        }
    }

    public function getHealthDepartmentStaff() {
        try {
            $stmt = $this->db->prepare("
                SELECT id, first_name, last_name 
                FROM users 
                WHERE role = 'staff' 
                AND department = 'health'
                AND status = 'active'
                ORDER BY first_name, last_name
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error fetching health department staff: " . $e->getMessage());
            return [];
        }
    }

    public function getCollectionById($collection_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM collections WHERE collection_id = :collection_id");
            $stmt->execute(['collection_id' => $collection_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error fetching collection by ID: " . $e->getMessage());
            return null;
        }
    }
}
?> 