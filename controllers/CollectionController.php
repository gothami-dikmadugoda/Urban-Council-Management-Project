<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/models/Collection.php';
require_once dirname(__DIR__) . '/config/database.php';

class CollectionController {
    private $collection;
    private $db;

    public function __construct() {
        $this->collection = new Collection();
        $this->db = new Database();

        // Debug log
        error_log("CollectionController constructor called");
        error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
        error_log("POST data: " . print_r($_POST, true));

        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            error_log("Processing action: " . $_POST['action']);
            switch ($_POST['action']) {
                case 'schedule_collection':
                    error_log("Handling schedule collection");
                    $this->handleScheduleCollection();
                    break;
                case 'update_collection':
                    $this->handleUpdateCollection();
                    break;
            }
        }
    }

    private function handleScheduleCollection() {
        try {
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('User not authenticated');
            }

            // Add user_id to the POST data
            $_POST['user_id'] = $_SESSION['user_id'];
            
            // Debug log
            error_log("Scheduling collection with data: " . print_r($_POST, true));
            
            // Validate required fields
            $required_fields = ['area_id', 'assigned_to', 'collection_date', 'time_slot', 'waste_type', 'quantity'];
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }
            
            $result = $this->scheduleCollection($_POST);
            error_log("Schedule collection result: " . print_r($result, true));
            
            if ($result['status'] === 'success') {
                $_SESSION['message'] = 'Collection scheduled successfully! Collection ID: ' . $result['collection_id'];
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = isset($result['message']) ? $result['message'] : 'Failed to schedule collection';
                $_SESSION['message_type'] = 'danger';
                if (isset($result['errors'])) {
                    $_SESSION['form_errors'] = $result['errors'];
                }
            }
        } catch (Exception $e) {
            error_log("Exception in handleScheduleCollection: " . $e->getMessage());
            $_SESSION['message'] = 'An error occurred: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        } finally {
            // Always redirect back to the form
            header('Location: /A-11/views/citizen/CitizenDashboard.php');
            exit();
        }
    }

    private function handleUpdateCollection() {
        if (!isset($_POST['collection_id']) || !isset($_POST['status'])) {
            $_SESSION['message'] = 'Invalid update request';
            $_SESSION['message_type'] = 'danger';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }

        $result = $this->updateCollectionStatus($_POST['collection_id'], $_POST['status']);
        
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = $result['status'] === 'success' ? 'success' : 'danger';
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    public function getCollectionAreas() {
        return $this->collection->getAreas();
    }

    public function getAvailableStaff() {
        try {
            $query = "SELECT id, first_name, last_name, department, job_role 
                     FROM users 
                     WHERE department = 'isHealth' 
                     AND job_role = 'garbage_manager' 
                     AND status = 'active'";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting available staff: " . $e->getMessage());
            return [];
        }
    }

    public function getHealthDepartmentStaff() {
        try {
            $query = "SELECT id, first_name, last_name, department, job_role 
                     FROM users 
                     WHERE department = 'isHealth' 
                     AND job_role = 'garbage_manager' 
                     AND status = 'active'";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting health department staff: " . $e->getMessage());
            return [];
        }
    }

    public function scheduleCollection($data) {
        try {
            // Validate input data
            $errors = $this->validateCollectionData($data);
            if (!empty($errors)) {
                return [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $errors
                ];
            }

            // Schedule the collection
            $result = $this->collection->scheduleCollection($data);
            
            // Log the result for debugging
            error_log("Collection scheduling result: " . print_r($result, true));
            
            if ($result['status'] === 'success') {
                return [
                    'status' => 'success',
                    'message' => 'Collection scheduled successfully',
                    'collection_id' => $result['collection_id']
                ];
            }

            return [
                'status' => 'error',
                'message' => isset($result['message']) ? $result['message'] : 'Failed to schedule collection'
            ];
        } catch (Exception $e) {
            error_log("Exception in scheduleCollection: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'An error occurred while scheduling the collection'
            ];
        }
    }

    public function getUpcomingCollections() {
        if (!isset($_SESSION['user_id'])) {
            return [
                'status' => 'error',
                'message' => 'User not authenticated'
            ];
        }

        $collections = $this->collection->getUpcomingCollections($_SESSION['user_id']);
        return [
            'status' => 'success',
            'data' => $collections
        ];
    }

    public function getStaffAssignments() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
            return [
                'status' => 'error',
                'message' => 'Unauthorized access'
            ];
        }

        $assignments = $this->collection->getStaffAssignments($_SESSION['user_id']);
        return [
            'status' => 'success',
            'data' => $assignments
        ];
    }

    private function validateCollectionData($data) {
        $errors = [];
        $required_fields = ['user_id', 'area', 'collection_date', 'collection_time', 'waste_type', 'waste_volume'];

        // Check required fields
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }

        // Validate date
        if (isset($data['collection_date'])) {
            $collection_date = strtotime($data['collection_date']);
            $today = strtotime('today');
            if ($collection_date < $today) {
                $errors['collection_date'] = 'Collection date cannot be in the past';
            }
        }

        // Validate time slot
        if (isset($data['collection_time'])) {
            $valid_time_slots = ['morning', 'afternoon', 'evening'];
            if (!in_array($data['collection_time'], $valid_time_slots)) {
                $errors['collection_time'] = 'Invalid time slot. Please select morning, afternoon, or evening.';
            }
        }

        // Validate waste type
        $valid_waste_types = ['household', 'garden', 'construction', 'hazardous', 'recyclable'];
        if (isset($data['waste_type']) && !in_array($data['waste_type'], $valid_waste_types)) {
            $errors['waste_type'] = 'Invalid waste type';
        }

        // Validate waste volume
        $valid_volumes = ['small', 'medium', 'large'];
        if (isset($data['waste_volume']) && !in_array($data['waste_volume'], $valid_volumes)) {
            $errors['waste_volume'] = 'Invalid waste volume';
        }

        return $errors;
    }

    public function createCollectionRequest($data) {
        try {
            // Validate required fields
            $requiredFields = ['user_id', 'area', 'collection_date', 'collection_time', 'waste_type', 'waste_volume'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => "Missing required field: $field"];
                }
            }

            // Validate date format
            if (!strtotime($data['collection_date'])) {
                return ['success' => false, 'message' => 'Invalid date format'];
            }

            // Check if date is not in the past
            if (strtotime($data['collection_date']) < strtotime('today')) {
                return ['success' => false, 'message' => 'Collection date cannot be in the past'];
            }

            // Format collection time to proper MySQL time format
            $collection_time = match($data['collection_time']) {
                'morning' => '08:00:00',
                'afternoon' => '12:00:00',
                'evening' => '16:00:00',
                default => null
            };

            if (!$collection_time) {
                return ['success' => false, 'message' => 'Invalid collection time'];
            }

            // Start transaction
            $this->db->getConnection()->beginTransaction();

            // Insert collection request
            $stmt = $this->db->getConnection()->prepare("
                INSERT INTO collection_requests (
                    user_id, area, collection_date, collection_time, 
                    waste_type, waste_volume, special_instructions, status,
                    created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, 'pending',
                    CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                )
            ");

            $stmt->execute([
                $data['user_id'],
                $data['area'],
                $data['collection_date'],
                $collection_time,
                $data['waste_type'],
                $data['waste_volume'],
                $data['special_instructions'] ?? null
            ]);

            $requestId = $this->db->getConnection()->lastInsertId();

            // Get all garbage managers
            $stmt = $this->db->getConnection()->prepare("
                SELECT id, first_name, last_name 
                FROM users 
                WHERE job_role = 'garbage_manager' 
                AND status = 'active'
            ");
            $stmt->execute();
            $garbageManagers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Create notifications for garbage managers
            require_once __DIR__ . '/NotificationController.php';
            $notificationController = new NotificationController();

            foreach ($garbageManagers as $manager) {
                $notificationData = [
                    'user_id' => $manager['id'],
                    'title' => 'New Collection Request',
                    'message' => "A new garbage collection request has been submitted for {$data['area']} area on " . date('Y-m-d', strtotime($data['collection_date'])),
                    'type' => 'collection_request',
                    'reference_id' => $requestId
                ];
                $notificationController->createNotification($notificationData);
            }

            // Commit transaction
            $this->db->getConnection()->commit();

            return ['success' => true, 'message' => 'Collection request created successfully'];
        } catch (PDOException $e) {
            // Rollback transaction on error
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->getConnection()->rollBack();
            }
            error_log("Error creating collection request: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while creating the collection request'];
        }
    }

    public function getCollectionRequests($userId = null, $status = null) {
        try {
            $query = "
                SELECT cr.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as user_name,
                       u.phone as user_phone
                FROM collection_requests cr
                JOIN users u ON cr.user_id = u.id
                WHERE 1=1
            ";
            $params = [];

            if ($userId) {
                $query .= " AND cr.user_id = ?";
                $params[] = $userId;
            }

            if ($status) {
                $query .= " AND cr.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY cr.collection_date DESC, cr.collection_time ASC";

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching collection requests: " . $e->getMessage());
            return [];
        }
    }

    public function updateCollectionStatus($requestId, $status, $staffId = null) {
        try {
            $stmt = $this->db->getConnection()->prepare("
                UPDATE collection_requests 
                SET status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $stmt->execute([$status, $requestId]);

            if ($staffId) {
                $this->addCollectionNote($requestId, $staffId, "Status updated to: $status");
            }

            return ['success' => true, 'message' => 'Collection status updated successfully'];
        } catch (PDOException $e) {
            error_log("Error updating collection status: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating the collection status'];
        }
    }

    public function addCollectionNote($requestId, $staffId, $note) {
        try {
            // First verify the connection's timezone setting
            $this->db->getConnection()->exec("SET time_zone = '+05:30'");
            
            $currentTimestamp = date('Y-m-d H:i:s');
            $stmt = $this->db->getConnection()->prepare("
                INSERT INTO collection_notes 
                (request_id, staff_id, note, created_at) 
                VALUES 
                (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $requestId,
                $staffId,
                $note,
                $currentTimestamp
            ]);
            
            return ['success' => true, 'message' => 'Note added successfully'];
        } catch (PDOException $e) {
            error_log("Error adding collection note: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while adding the note'];
        }
    }

    public function getCollectionNotes($requestId) {
        try {
            $stmt = $this->db->getConnection()->prepare("
                SELECT cn.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as staff_name,
                       u.role as staff_role
                FROM collection_notes cn
                JOIN users u ON cn.staff_id = u.id
                WHERE cn.request_id = ?
                ORDER BY cn.created_at DESC
            ");
            
            $stmt->execute([$requestId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching collection notes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get collection requests for a specific user
     * 
     * @param int $userId The ID of the user
     * @return array Collection requests for the user
     */
    public function getUserCollectionRequests($userId) {
        try {
            $query = "
                SELECT cr.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as user_name,
                       u.phone as user_phone
                FROM collection_requests cr
                JOIN users u ON cr.user_id = u.id
                WHERE cr.user_id = ?
                ORDER BY cr.collection_date DESC, cr.collection_time ASC
            ";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user collection requests: " . $e->getMessage());
            return [];
        }
    }

    public function getCollectionRequestById($requestId) {
        try {
            $query = "
                SELECT cr.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as user_name,
                       u.phone as user_phone
                FROM collection_requests cr
                JOIN users u ON cr.user_id = u.id
                WHERE cr.id = ?
                LIMIT 1
            ";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$requestId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching collection request by ID: " . $e->getMessage());
            return null;
        }
    }
}

// Initialize controller if this file is accessed directly
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new CollectionController();
}
?> 