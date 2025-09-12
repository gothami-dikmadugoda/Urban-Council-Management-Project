<?php
require_once __DIR__ . '/../models/Complaint.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/NotificationController.php';

class ComplaintController {
    private $db;
    private $complaint;
    private $notificationController;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->complaint = new Complaint($this->db);
        $this->notificationController = new NotificationController();
    }

    public function validateUserAccess() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /urban2/login.php');
            exit;
        }
        // Log user access validation
        error_log("User access validated for user ID: " . $_SESSION['user_id']);
    }

    public function getComplaintsByUserId($userId) {
        return $this->complaint->getByUserId($userId);
    }

    public function getComplaintById($id) {
        return $this->complaint->getById($id);
    }

    public function updateComplaintStatus($id, $status) {
        $result = $this->complaint->updateStatus($id, $status);
        return [
            'success' => $result,
            'message' => $result ? 'Status updated successfully' : 'Failed to update status'
        ];
    }

    public function updateComplaintPriority($id, $priority) {
        $result = $this->complaint->updatePriority($id, $priority);
        return [
            'success' => $result,
            'message' => $result ? 'Priority updated successfully' : 'Failed to update priority'
        ];
    }

    public function addComplaintNote($id, $note) {
        $result = $this->complaint->addNote($id, $_SESSION['user_id'], $note);
        return [
            'success' => $result,
            'message' => $result ? 'Note added successfully' : 'Failed to add note'
        ];
    }

    public function getComplaintNotes($id) {
        return $this->complaint->getNotes($id);
    }

    public function createComplaint($data) {
        // Set default values if not provided
        $data['status'] = $data['status'] ?? 'pending';
        $data['priority'] = $data['priority'] ?? 'medium';
        
        // Handle image upload if present
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $data['image'] = file_get_contents($_FILES['image']['tmp_name']);
        } else {
            $data['image'] = null;
        }

        $result = $this->complaint->create($data);
        
        if ($result !== false) {
            if (isset($data['assigned_to']) && !empty($data['assigned_to'])) {
                // Create notification for assigned staff member
                $this->notificationController->createComplaintNotification(
                    $result,
                    $data['assigned_to'],
                    $data['title']
                );
            }
            
            // Create notification for priority complaints
            if ($data['priority'] === 'urgent' || $data['priority'] === 'high') {
                $this->notificationController->createPriorityNotification(
                    $result,
                    $data['title'],
                    $data['priority']
                );
            }
        }
        
        return [
            'success' => $result !== false,
            'message' => $result !== false ? 'Complaint submitted successfully' : 'Failed to submit complaint'
        ];
    }

    // New method to get historical complaint data
    public function getHistoricalComplaintData() {
        $query = "SELECT created_at, category, status, priority FROM complaints ORDER BY created_at ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // New method to get predictions from the API
    public function getPredictions() {
        $historicalData = $this->getHistoricalComplaintData();
        // Assuming the API endpoint is /api/predict
        $url = 'http://localhost:5000/api/predict'; // Update with actual API URL

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($historicalData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function getComplaintStatistics($userId, $timeframe = 'monthly') {
        try {
            // Debug log
            error_log("Getting complaint statistics for user: $userId, timeframe: $timeframe");
            
            $query = "";
            switch ($timeframe) {
                case 'weekly':
                    $query = "
                        SELECT 
                            DATE(created_at) as date,
                            COUNT(*) as count,
                            WEEK(created_at) as period
                        FROM complaints 
                        WHERE user_id = :user_id
                        AND created_at >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
                        GROUP BY WEEK(created_at), DATE(created_at)
                        ORDER BY date ASC
                    ";
                    break;
                case 'yearly':
                    $query = "
                        SELECT 
                            DATE_FORMAT(created_at, '%Y-%m') as date,
                            COUNT(*) as count,
                            YEAR(created_at) as period
                        FROM complaints 
                        WHERE user_id = :user_id
                        AND created_at >= DATE_SUB(NOW(), INTERVAL 2 YEAR)
                        GROUP BY YEAR(created_at), MONTH(created_at)
                        ORDER BY date ASC
                    ";
                    break;
                default: // monthly
                    $query = "
                        SELECT 
                            DATE_FORMAT(created_at, '%Y-%m') as date,
                            COUNT(*) as count,
                            MONTH(created_at) as period
                        FROM complaints 
                        WHERE user_id = :user_id
                        AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                        GROUP BY YEAR(created_at), MONTH(created_at)
                        ORDER BY date ASC
                    ";
            }

            // Debug log the query
            error_log("Executing query: " . $query);

            $stmt = $this->db->prepare($query);
            $stmt->execute(['user_id' => $userId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Debug log the results
            error_log("Query results: " . print_r($results, true));

            // If no results, return empty data with success status
            if (empty($results)) {
                error_log("No complaints found for user $userId");
                return [
                    'status' => 'success',
                    'data' => [
                        [
                            'date' => date('Y-m-d'),
                            'count' => 0,
                            'percentChange' => 0
                        ]
                    ]
                ];
            }

            // Calculate percentage changes
            $data = [];
            $prevCount = null;
            foreach ($results as $key => $row) {
                $percentChange = null;
                if ($prevCount !== null) {
                    $percentChange = $prevCount != 0 ? 
                        round((($row['count'] - $prevCount) / $prevCount) * 100, 1) : 100;
                }
                $data[] = [
                    'date' => $row['date'],
                    'count' => (int)$row['count'],
                    'percentChange' => $percentChange
                ];
                $prevCount = $row['count'];
            }

            error_log("Processed data: " . print_r($data, true));

            return [
                'status' => 'success',
                'data' => $data
            ];
        } catch (PDOException $e) {
            error_log("Database error in getComplaintStatistics: " . $e->getMessage());
            error_log("Error code: " . $e->getCode());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [
                'status' => 'error',
                'message' => 'Database error occurred while fetching complaint statistics'
            ];
        } catch (Exception $e) {
            error_log("General error in getComplaintStatistics: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [
                'status' => 'error',
                'message' => 'Failed to fetch complaint statistics'
            ];
        }
    }

    public function getAllComplaints() {
        return $this->complaint->getAll();
    }
}
?>